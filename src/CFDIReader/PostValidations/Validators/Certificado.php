<?php
namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;
use CfdiUtils\CadenaOrigen\CfdiDefaultLocations;
use CfdiUtils\CadenaOrigen\XsltBuilderInterface;
use CfdiUtils\Certificado\Certificado as UtilCertificado;
use CfdiUtils\Certificado\NodeCertificado;
use CfdiUtils\Nodes\XmlNodeUtils;
use XmlResourceRetriever\XsltRetriever;

/**
 * This class validate that:
 * - The certificate exists and is valid
 * - It matches with the emisor RFC and name
 * - The seal match with the cfdi
 */
class Certificado extends AbstractValidator
{
    /** @var XsltBuilderInterface|null */
    private $cadenaOrigen;

    /** @var XsltRetriever|null */
    private $xsltRetriever;

    public function setXsltRetriever(XsltRetriever $xsltRetriever = null)
    {
        $this->xsltRetriever = $xsltRetriever;
    }

    public function getXsltRetriever(): XsltRetriever
    {
        if (! $this->xsltRetriever instanceof XsltRetriever) {
            throw new \RuntimeException('The xsltRetriever object has not been set');
        }
        return $this->xsltRetriever;
    }

    public function hasXsltRetriever(): bool
    {
        return ($this->xsltRetriever instanceof XsltRetriever);
    }

    public function setCadenaOrigen(XsltBuilderInterface $cadenaOrigen = null)
    {
        $this->cadenaOrigen = $cadenaOrigen;
    }

    public function getCadenaOrigen(): XsltBuilderInterface
    {
        // use this comparison instead of hasCadenaOrigen to satisfy phpstan
        if (! $this->cadenaOrigen instanceof XsltBuilderInterface) {
            throw new \RuntimeException('The CadenaOrigen object has not been set');
        }
        return $this->cadenaOrigen;
    }

    public function hasCadenaOrigen(): bool
    {
        return ($this->cadenaOrigen instanceof XsltBuilderInterface);
    }

    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);

        // create the certificate
        $extractor = new NodeCertificado(XmlNodeUtils::nodeFromXmlElement($cfdi->document()->documentElement));
        try {
            $certificado = $extractor->obtain();
        } catch (\Exception $ex) {
            $this->errors->add('No se pudo obtener el certificado del comprobante');
            return;
        }

        $this->validateNoCertificado($certificado, $cfdi->attribute('noCertificado'));
        $this->validateRfc($certificado, $cfdi->attribute('emisor', 'rfc'));
        $this->validateNombre($certificado, $cfdi->attribute('emisor', 'nombre'));
        $this->validateFecha($certificado, $cfdi->attribute('fecha'));

        // validate certificate seal
        if ($this->hasCadenaOrigen()) {
            $cadenaOrigen = $this->getCadenaOrigen()->build(
                $cfdi->source(),
                $this->getXsltLocation($cfdi->getVersion())
            );
            $this->validateSello(
                $certificado,
                $cfdi->getVersion(),
                $cadenaOrigen,
                $cfdi->attribute('sello')
            );
        }
    }

    private function getXsltLocation(string $version): string
    {
        $remoteLocation = CfdiDefaultLocations::location($version);
        if (! $this->hasXsltRetriever()) {
            return $remoteLocation;
        }
        $retriever = $this->getXsltRetriever();
        $localPath = $retriever->buildPath($remoteLocation);
        if (! file_exists($localPath)) {
            $retriever->retrieve($remoteLocation);
        }
        return $localPath;
    }

    private function validateNoCertificado(UtilCertificado $certificado, string $noCertificado)
    {
        if ($certificado->getSerial() !== $noCertificado) {
            $this->errors->add(sprintf(
                'El número del certificado extraido (%s) no coincide con el reportado en el comprobante (%s)',
                $certificado->getSerial(),
                $noCertificado
            ));
        }
    }

    private function validateRfc(UtilCertificado $certificado, string $emisorRfc)
    {
        if ($certificado->getRfc() !== $emisorRfc) {
            $this->errors->add(sprintf(
                'El certificado extraido contiene el RFC (%s) que no coincide con el RFC reportado en el emisor (%s)',
                $certificado->getRfc(),
                $emisorRfc
            ));
        }
    }

    private function validateNombre(UtilCertificado $certificado, string $emisorNombre)
    {
        if ('' === $emisorNombre) {
            return;
        }
        if (! $this->compareNames($certificado->getName(), $emisorNombre)) {
            $this->warnings->add(sprintf(
                'El certificado extraido contiene la razón social "%s"'
                . ' que no coincide con el la razón social reportado en el emisor "%s"',
                $certificado->getName(),
                $emisorNombre
            ));
        }
    }

    private function validateFecha(UtilCertificado $certificado, string $fechaSource)
    {
        $fecha = ('' === $fechaSource) ? 0 : strtotime($fechaSource);
        if (0 === $fecha) {
            $this->errors->add('La fecha del documento no fue encontrada');
            return;
        }
        if ($fecha < $certificado->getValidFrom()) {
            $this->errors->add(sprintf(
                'La fecha del documento %s es menor a la fecha de vigencia del certificado %s',
                date('Y-m-d H:i:s', $fecha),
                date('Y-m-d H:i:s', $certificado->getValidFrom())
            ));
        }
        if ($fecha > $certificado->getValidTo()) {
            $this->errors->add(sprintf(
                'La fecha del documento %s es mayor a la fecha de vigencia del certificado %s',
                date('Y-m-d H:i:s', $fecha),
                date('Y-m-d H:i:s', $certificado->getValidTo())
            ));
        }
    }

    private function validateSello(UtilCertificado $certificado, string $version, string $cadena, string $selloBase64)
    {
        $algorithms = [OPENSSL_ALGO_SHA256];
        if ('3.2' === $version) {
            $algorithms[] = OPENSSL_ALGO_SHA1;
        }
        $sello = $this->obtainSello($selloBase64);
        if ('' !== $sello) {
            $selloIsValid = false;
            foreach ($algorithms as $algorithm) {
                if ($certificado->verify($cadena, $sello, $algorithm)) {
                    $selloIsValid = true;
                    break;
                }
            }
            if (! $selloIsValid) {
                $this->errors->add(
                    'La verificación del sello del CFDI no coincide, probablemente el CFDI fue alterado o mal generado'
                );
            }
        }
    }

    private function obtainSello(string $selloBase64): string
    {
        // this silence error operator is intentional, if $selloBase64 is malformed
        // then it will return false and I will recognize the error
        if (false === $sello = @base64_decode($selloBase64, true)) {
            $this->errors->add('El sello del comprobante fiscal digital no está en base 64');
            return '';
        }
        return $sello;
    }

    private function compareNames(string $first, string $second): bool
    {
        return (0 === strcasecmp($this->castNombre($first), $this->castNombre($second)));
    }

    private function castNombre(string $nombre): string
    {
        return str_replace([' ', '.'], '', $nombre);
    }
}

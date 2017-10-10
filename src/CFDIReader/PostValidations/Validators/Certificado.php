<?php
namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;
use CfdiUtils\CadenaOrigen;
use CfdiUtils\Certificado as UtilCertificado;
use CfdiUtils\CfdiCertificado;

/**
 * This class validate that:
 * - The certificate exists and is valid
 * - It matches with the emisor RFC and name
 * - The seal match with the cfdi
 */
class Certificado extends AbstractValidator
{
    /** @var CadenaOrigen|null */
    private $cadenaOrigen;

    public function setCadenaOrigen(CadenaOrigen $cadenaOrigen = null)
    {
        $this->cadenaOrigen = $cadenaOrigen;
    }

    public function getCadenaOrigen(): CadenaOrigen
    {
        // use this comparison instead of hasCadenaOrigen to satisfy phpstan
        if (! ($this->cadenaOrigen instanceof CadenaOrigen)) {
            throw new \RuntimeException('The CadenaOrigen object has not been set');
        }
        return $this->cadenaOrigen;
    }

    public function hasCadenaOrigen(): bool
    {
        return ($this->cadenaOrigen instanceof CadenaOrigen);
    }

    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);

        // create the certificate
        $extractor = new CfdiCertificado($cfdi->document());
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
            $this->validateSello(
                $certificado,
                $cfdi->getVersion(),
                $this->getCadenaOrigen()->build($cfdi->source()),
                $cfdi->attribute('sello')
            );
        }
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

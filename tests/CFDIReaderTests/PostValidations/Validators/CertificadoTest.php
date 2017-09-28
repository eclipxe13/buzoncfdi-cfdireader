<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\CFDIFactory;
use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Validators\Certificado;

class CertificadoTest extends ValidatorsTestCase
{
    private function createCertificado($clearCadenaOrigen = true): Certificado
    {
        $factory = new CFDIFactory();
        $validator = $factory->newCertificadoValidator();
        if ($clearCadenaOrigen) {
            $validator->setCadenaOrigen(null);
        }
        return $validator;
    }

    public function testValidateValid()
    {
        $this->setupWithFile('v32/real.xml', true);

        $validator = $this->createCertificado(false);
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testValidateWithInvalidCertificate()
    {
        $this->setupWithFile('v32/validator-certificado/without-certificado.xml');
        $expectedError = 'No se pudo obtener el certificado del comprobante';

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithCertificateNumberMissmatch()
    {
        $this->setupWithFile('v32/validator-certificado/number-missmatch.xml');
        $expectedError = 'El número del certificado extraido (20001000000100005867)'
            . ' no coincide con el reportado en el comprobante (1234567890123456790)';

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithEmisorRfcMissmatch()
    {
        $this->setupWithFile('v32/validator-certificado/emisor-rfc.xml');
        $expectedError = 'El certificado extraido contiene el RFC (AAA010101AAA)'
            . ' que no coincide con el RFC reportado en el emisor (ZZZZ1111119AB)';

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithEmisorNombreMissmatch()
    {
        $this->setupWithFile('v32/validator-certificado/emisor-nombre.xml');
        $expectedError = 'El certificado extraido contiene la razón social "ACCEM SERVICIOS EMPRESARIALES SC"'
            . ' que no coincide con el la razón social reportado en el emisor "Foo Bar SA de CV"';

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::WARNING => [$expectedError]], $issues);
    }

    public function testValidateWithFechaLowerThanValid()
    {
        $this->setupWithFile('v32/validator-certificado/fecha-lower-than-valid.xml');
        $expectedError = 'La fecha del documento 2010-01-01 16:52:45'
            . ' es menor a la fecha de vigencia del certificado 2012-07-27 17:02:00';

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithFechaGreaterThanValid()
    {
        $this->setupWithFile('v32/validator-certificado/fecha-greater-than-valid.xml');
        $expectedError = 'La fecha del documento 2018-01-01 16:52:45'
            . ' es mayor a la fecha de vigencia del certificado 2016-07-27 17:02:00';

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithSelloMissmatch()
    {
        $this->setupWithFile('v32/validator-certificado/sello-missmatch.xml');
        $expectedError = 'La verificación del sello del CFDI no coincide,'
            . ' probablemente el CFDI fue alterado o mal generado';

        $validator = $this->createCertificado(false);
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithSelloNotBase64()
    {
        $this->setupWithFile('v32/validator-certificado/sello-not-base64.xml');
        $expectedError = 'El sello del comprobante fiscal digital no está en base 64';

        $validator = $this->createCertificado(false);
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => [$expectedError]], $issues);
    }

    public function testValidateWithMissingValues()
    {
        $this->setupWithFile('v32/validator-certificado/cfdi-missing-values.xml');
        $expectedErrors = [
            'El número del certificado extraido (20001000000100005867)'
                . ' no coincide con el reportado en el comprobante ()',
            'El certificado extraido contiene el RFC (AAA010101AAA)'
                . ' que no coincide con el RFC reportado en el emisor ()',
            'La fecha del documento no fue encontrada',
        ];

        $validator = $this->createCertificado();
        $validator->validate($this->cfdi, $this->issues);

        $issues = $this->issues->all();
        $this->assertCount(1, $issues);
        $this->assertEquals([IssuesTypes::ERROR => $expectedErrors], $issues);
    }

    public function testGetCadenaOrigenWithoutObjectThrowsException()
    {
        $validator = new Certificado();
        $validator->setCadenaOrigen(null);

        $this->assertFalse($validator->hasCadenaOrigen());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The CadenaOrigen object has not been set');

        $validator->getCadenaOrigen();
    }
}

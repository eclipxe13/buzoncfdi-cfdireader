<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Validators\Totales;

class TotalesTest extends ValidatorsTestCase
{
    public function testValidateValid()
    {
        $this->setupWithFile('v32/valid.xml');

        $validator = new Totales();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testValidateWarningSubtotal()
    {
        $this->setupWithFile('v32/totales-subtotal.xml');
        $validator = new Totales();
        $validator->validate($this->cfdi, $this->issues);

        $expectedError = 'El subtotal no coincide con la suma de los importes';
        $this->assertEquals([$expectedError], $this->issues->messages(IssuesTypes::WARNING)->all());
    }

    public function testValidateWarningTotal()
    {
        $this->setupWithFile('v32/totales-total.xml');
        $validator = new Totales();
        $validator->validate($this->cfdi, $this->issues);

        $expectedError = 'El total no coincide con la suma del subtotal'
            . ' menos el descuento más los traslados menos las retenciones';
        $this->assertEquals([$expectedError], $this->issues->messages(IssuesTypes::WARNING)->all());
    }
}

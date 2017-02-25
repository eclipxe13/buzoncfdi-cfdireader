<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Validators\Conceptos;

class ConceptosTest extends ValidatorsTestCase
{
    public function testValidateValid()
    {
        $this->setupWithFile('cfdi-valid.xml');

        $validator = new Conceptos();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testInvalidFechaIsInTheFuture()
    {
        $this->setupWithFile('cfdi-conceptos.xml');

        $validator = new Conceptos();
        $validator->validate($this->cfdi, $this->issues);

        $expectedError = 'El importe del concepto'
            . ' Módulo de embarque serie A-6743-Ñ no coincide con el producto del valor unitario y el total';
        $this->assertEquals([$expectedError], $this->issues->messages(IssuesTypes::WARNING)->all());
    }
}

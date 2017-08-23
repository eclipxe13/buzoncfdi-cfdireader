<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Validators\Impuestos;

class ImpuestosTest extends ValidatorsTestCase
{
    public function testValidate()
    {
        $this->setupWithFile('cfdi-impuestos-valid.xml');

        $validator = new Impuestos();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testValidateRetenidos()
    {
        $this->setupWithFile('cfdi-impuestos-retenidos.xml');

        $validator = new Impuestos();
        $validator->validate($this->cfdi, $this->issues);

        $expectedMessage = 'El total de impuestos retenidos difiere de la suma de los nodos de las retenciones';

        $this->assertContains($expectedMessage, $this->issues->messages(IssuesTypes::WARNING)->all());
        $this->assertCount(1, $this->issues->all());
    }

    public function testValidateTrasladados()
    {
        $this->setupWithFile('cfdi-impuestos-trasladados.xml');

        $validator = new Impuestos();
        $validator->validate($this->cfdi, $this->issues);

        $expectedMessage = 'El total de impuestos trasladados difiere de la suma de los nodos de los traslados';

        $this->assertContains($expectedMessage, $this->issues->messages(IssuesTypes::WARNING)->all());
        $this->assertCount(1, $this->issues->all());
    }

    public function testValidateLocalesRetenidos()
    {
        $this->setupWithFile('cfdi-impuestos-locales-retenidos.xml');

        $validator = new Impuestos();
        $validator->validate($this->cfdi, $this->issues);

        $expectedMessage = 'El total de impuestos locales retenidos difiere de la suma de los nodos de las retenciones';

        $this->assertContains($expectedMessage, $this->issues->messages(IssuesTypes::WARNING)->all());
        $this->assertCount(1, $this->issues->all());
    }

    public function testValidateLocalesTrasladados()
    {
        $this->setupWithFile('cfdi-impuestos-locales-trasladados.xml');

        $validator = new Impuestos();
        $validator->validate($this->cfdi, $this->issues);

        $expectedMessage = 'El total de impuestos locales trasladados difiere de la suma de los nodos de los traslados';

        $this->assertContains($expectedMessage, $this->issues->messages(IssuesTypes::WARNING)->all());
        $this->assertCount(1, $this->issues->all());
    }
}

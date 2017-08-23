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
}

<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Validators\TFDVersions;

class TFDVersionsTest extends ValidatorsTestCase
{
    public function testValidateWithoutTimbre()
    {
        $this->setupWithFile('validator-tfd-version/no-timbre.xml');

        $validator = new TFDVersions();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testValidateValid33()
    {
        $this->setupWithFile('validator-tfd-version/valid-v33.xml', true);

        $validator = new TFDVersions();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testValidateValid32()
    {
        $this->setupWithFile('validator-tfd-version/valid-v32.xml', true);

        $validator = new TFDVersions();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(0, $this->issues->all());
    }

    public function testValidateUnmatch33()
    {
        $this->setupWithFile('validator-tfd-version/unmatch-v33.xml', true);

        $validator = new TFDVersions();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(1, $this->issues->messages(IssuesTypes::ERROR));
        $this->assertCount(1, $this->issues->all());
    }

    public function testValidateUnmatch32()
    {
        $this->setupWithFile('validator-tfd-version/unmatch-v32.xml', true);

        $validator = new TFDVersions();
        $validator->validate($this->cfdi, $this->issues);

        $this->assertCount(1, $this->issues->messages(IssuesTypes::ERROR));
        $this->assertCount(1, $this->issues->all());
    }
}

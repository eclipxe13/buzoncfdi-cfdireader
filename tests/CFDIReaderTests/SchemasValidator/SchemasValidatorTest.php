<?php
namespace CFDIReaderTests\SchemasValidator;

use CFDIReader\SchemasValidator\SchemasValidator;
use PHPUnit\Framework\TestCase;
use XmlResourceRetriever\XsdRetriever;

class SchemasValidatorTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $validator = new SchemasValidator();
        $this->assertFalse($validator->hasRetriever());
        $this->assertFalse($validator->isForcedDownloads());
    }

    public function testDefaultConstructorWithArguments()
    {
        $retriever = new XsdRetriever(__DIR__);
        $validator = new SchemasValidator($retriever, true);
        $this->assertSame($retriever, $validator->getRetriever());
        $this->assertTrue($validator->hasRetriever());
        $this->assertTrue($validator->isForcedDownloads());
    }

    public function testForcedDownloadsProperty()
    {
        $validator = new SchemasValidator();
        $validator->setForcedDownloads(true);
        $this->assertTrue($validator->isForcedDownloads());
        $validator->setForcedDownloads(false);
        $this->assertFalse($validator->isForcedDownloads());
    }

    public function testGetRetrieverThrowALogicExceptionWhenNoRetrieverHasBeenSet()
    {
        $validator = new SchemasValidator();
        $this->expectException(\LogicException::class);
        $validator->getRetriever();
    }

    public function testValidateWithRetrieverUnset()
    {
        $validator = new SchemasValidator();
        $validator->validate('<root/>');
        $this->assertTrue(true, 'This method should not raise any exception');
    }
}

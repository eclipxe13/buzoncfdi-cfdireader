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
    }

    public function testDefaultConstructorWithArguments()
    {
        $retriever = new XsdRetriever(__DIR__);
        $validator = new SchemasValidator($retriever);
        $this->assertSame($retriever, $validator->getRetriever());
        $this->assertTrue($validator->hasRetriever());
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

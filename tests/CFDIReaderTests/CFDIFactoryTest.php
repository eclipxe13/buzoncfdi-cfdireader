<?php
namespace CFDIReaderTests;

use CFDIReader\CFDIFactory;
use PHPUnit\Framework\TestCase;
use XmlResourceRetriever\XsdRetriever;

class CFDIFactoryTest extends TestCase
{
    public function testDefaultConstructor()
    {
        $factory = new CFDIFactory();
        $this->assertEquals($factory->getDefaultLocalResourcesPath(), $factory->getLocalResourcesPath());
    }

    public function testLocalResourcesPathProperty()
    {
        $factory = new CFDIFactory();
        $factory->setLocalResourcesPath(__DIR__);
        $this->assertEquals(__DIR__, $factory->getLocalResourcesPath());
        $factory->setLocalResourcesPath('');
        $this->assertEquals('', $factory->getLocalResourcesPath());
        $factory->setLocalResourcesPath(null);
        $this->assertNotNull($factory->getLocalResourcesPath());
        $this->assertEquals($factory->getDefaultLocalResourcesPath(), $factory->getLocalResourcesPath());
    }

    public function testNewRetrieverReturnNullIfLocalResourcesPathIsEmpty()
    {
        $factory = new CFDIFactory();
        $factory->setLocalResourcesPath('');
        $this->assertNull($factory->newRetriever());
    }

    public function testNewRetrieverReturnRetrieverIfLocalResourcesPathIsNotEmpty()
    {
        $factory = new CFDIFactory();
        $this->assertInstanceOf(XsdRetriever::class, $factory->newRetriever());
    }

    public function testNewCFDIReaderWithOutTimbreAndNotRequired()
    {
        $factory = new CFDIFactory();
        $content = file_get_contents(test_file_location('v33/valid-without-timbre.xml'));
        $errors = [];
        $warnings = [];
        $cfdi = $factory->newCFDIReader($content, $errors, $warnings, false);

        $this->assertFalse($cfdi->hasTimbreFiscalDigital());
    }

    public function testNewCFDIReaderWithOutTimbreAndRequired()
    {
        $factory = new CFDIFactory();
        $content = file_get_contents(test_file_location('v33/valid-without-timbre.xml'));
        $errors = [];
        $warnings = [];

        $this->expectException(\Exception::class);

        $factory->newCFDIReader($content, $errors, $warnings, true);
    }
}

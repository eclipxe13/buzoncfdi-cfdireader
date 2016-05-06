<?php

namespace CFDIReaderTests;

use CFDIReader\CFDIReader;

class CFDIReaderTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructorWithValidCFDI()
    {
        $filename = test_file_location('cfdi-valid.xml');
        $cfdi = new CFDIReader(file_get_contents($filename));
        $this->assertInstanceOf('\CFDIReader\CFDIReader', $cfdi, 'Object created');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The content provided to build the CFDIReader is not a valid XML
     */
    public function testConstructorWithInValidXML()
    {
        new CFDIReader('This is not an XML content');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The XML root node must be Comprobante
     */
    public function testConstructorWithInvalidRoot()
    {
        new CFDIReader('<root/>');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Comprobante version attribute must be 3.2
     */
    public function testConstructorWithInvalidVersionMissing()
    {
        new CFDIReader('<Comprobante/>');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Comprobante version attribute must be 3.2
     */
    public function testConstructorWithInvalidVersionWrong()
    {
        new CFDIReader('<Comprobante version="3.1"/>');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The content does not use the namespace http://www.sat.gob.mx/cfd/3
     */
    public function testConstructorWithInvalidNamespaceCFD3()
    {
        new CFDIReader('<Comprobante version="3.2"/>');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The content does not use the namespace http://www.sat.gob.mx/TimbreFiscalDigital
     */
    public function testConstructorWithInvalidNamespaceTimbre()
    {
        $content = '<a'.':Comprobante xmlns:a="http://www.sat.gob.mx/cfd/3" version="3.2" />';
        new CFDIReader($content);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Seal not found on Comprobante/Complemento/TimbreFiscalDigital
     */
    public function testConstructorWithoutSeal()
    {
        $filename = test_file_location('cfdi-noseal.xml');
        new CFDIReader(file_get_contents($filename));
    }

    public function testGetTwoDifferentInstances()
    {
        $filename = test_file_location('cfdi-valid.xml');
        $cfdi = new CFDIReader(file_get_contents($filename));
        $a = $cfdi->comprobante();
        $b = $cfdi->comprobante();
        $this->assertInstanceOf('\SimpleXMLElement', $a, 'cfdi->comprobante() do not retrieve a SimpleXMLElement');
        $this->assertInstanceOf('\SimpleXMLElement', $b, 'cfdi->comprobante() do not retrieve a SimpleXMLElement');
        $this->assertEquals($a, $b, 'Two instances retrieved by cfdi->comprobante() must be equals');
        $this->assertNotSame($a, $b, 'Two instances retrieved by cfdi->comprobante() must be equals but not the same');
    }

    public function testGetUUID()
    {
        $filename = test_file_location('cfdi-valid.xml');
        $cfdi = new CFDIReader(file_get_contents($filename));
        $this->assertSame('e403f396-6a57-4625-adb4-bb436b00789f', $cfdi->getUUID(), 'Unable to retrieve the UUID by using the getUUID() method');
    }

}

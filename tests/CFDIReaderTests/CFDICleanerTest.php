<?php
namespace CFDIReaderTests;

use CFDIReader\CFDICleaner;
use CFDIReader\CFDICleanerException;
use PHPUnit\Framework\TestCase;

class CFDICleanerTest extends TestCase
{
    public function testConstructorWithEmptyText()
    {
        $cleaner = new CFDICleaner('');
        $this->expectException(CFDICleanerException::class);
        // use the @ to not throw the warning
        @$cleaner->loadContent('');
    }

    public function testConstructorWithNonCFDI()
    {
        $cleaner = new CFDICleaner('');
        $this->expectException(CFDICleanerException::class);
        // use the @ to not throw the warning
        @$cleaner->loadContent('<node></node>');
    }

    public function testConstructorWithBadVersion()
    {
        $this->expectException(CFDICleanerException::class);
        new CFDICleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" version="3.15" />
        ');
    }

    public function testConstructorWithoutInvalidXml()
    {
        $this->expectException(CFDICleanerException::class);
        $this->expectExceptionMessage('XML Error');

        new CFDICleaner('<' . 'node>');
    }

    public function testConstructorWithoutVersion()
    {
        $this->expectException(CFDICleanerException::class);
        $this->expectExceptionMessage('The XML document does not contains a version');

        new CFDICleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" />
        ');
    }

    public function testConstructorWithMinimalCompatibilityVersion32()
    {
        $cleaner = new CFDICleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" version="3.2" />
        ');
        $this->assertInstanceOf(CFDICleaner::class, $cleaner, 'Cleaner created with minimum compatibility');
    }

    public function testConstructorWithMinimalCompatibilityVersion33()
    {
        $cleaner = new CFDICleaner('<?xml version="1.0" encoding="UTF-8"?>
            <' . 'cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/3" Version="3.3" />
        ');
        $this->assertInstanceOf(CFDICleaner::class, $cleaner, 'Cleaner created with minimum compatibility');
    }

    public function testCleanOnDetail()
    {
        $basefile = test_file_location('v32/cleaner-dirty.xml');
        $step1 = test_file_location('v32/cleaner-no-addenda.xml');
        $step2 = test_file_location('v32/cleaner-no-nonsat-nodes.xml');
        $step3 = test_file_location('v32/cleaner-no-nonsat-schemalocations.xml');
        $step4 = test_file_location('v32/cleaner-no-nonsat-xmlns.xml');
        foreach ([$basefile, $step1, $step2, $step3, $step4] as $filename) {
            $this->assertFileExists($basefile, "The file $filename for testing does not exists");
        }
        $cleaner = new CFDICleaner(file_get_contents($basefile));
        $this->assertXmlStringEqualsXmlFile(
            $basefile,
            $cleaner->retrieveXml(),
            'Compare that the document was loaded without modifications'
        );
        $cleaner->removeAddenda();
        $this->assertXmlStringEqualsXmlFile($step1, $cleaner->retrieveXml(), 'Compare that addenda was removed');
        $cleaner->removeNonSatNSNodes();
        $this->assertXmlStringEqualsXmlFile($step2, $cleaner->retrieveXml(), 'Compare that non SAT nodes were removed');
        $cleaner->removeNonSatNSschemaLocations();
        $this->assertXmlStringEqualsXmlFile(
            $step3,
            $cleaner->retrieveXml(),
            'Compare that non SAT schemaLocations were removed'
        );
        $cleaner->removeUnusedNamespaces();
        $this->assertXmlStringEqualsXmlFile(
            $step4,
            $cleaner->retrieveXml(),
            'Compare that xmlns definitions were removed'
        );
        $this->assertXmlStringEqualsXmlFile(
            $step4,
            CFDICleaner::staticClean(file_get_contents($basefile)),
            'Check static method for cleaning is giving the same results as detailed execution'
        );
    }
}

<?php

namespace CFDIReaderTests\SchemaValidator;

use CFDIReader\SchemaValidator\Schemas;
use CFDIReader\SchemaValidator\Schema;
use CFDIReader\SchemaValidator\Locator;

class SchemasTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyObject()
    {
        $schemas = new Schemas();
        $this->assertInstanceOf('\Countable', $schemas, "The class must implements Countable");
        $this->assertInstanceOf('\IteratorAggregate', $schemas, "The class must implements IteratorAggregate");
        $this->assertCount(0, $schemas, 'Assert that the count is zero');
        $this->assertSame([], $schemas->all(), 'Assert that the returned array is empty');
    }

    public function testCreateAndGetItem()
    {
        $ns = 'http://example.com';
        $location = 'http://example.com/xsd';
        $schemas = new Schemas();
        $schema = $schemas->create($ns, $location);
        $this->assertCount(1, $schemas);
        $this->assertInstanceOf('\CFDIReader\SchemaValidator\Schema', $schema, "The create method must return a Schema object");
        $this->assertSame($ns, $schema->getNamespace(), 'The object contains the right namespace');
        $this->assertSame($location, $schema->getLocation(), 'The object contains the right location');
        $this->assertSame($schema, $schemas->item($ns), 'The object created is the SAME as the object retrieved');
    }

    public function testItemNonExistent()
    {
        $ns = 'http://example.com';
        $schemas = new Schemas();
        $this->setExpectedException('\InvalidArgumentException', "Namespace $ns does not exists in the schemas");
        $schemas->item($ns);
    }

    public function testInsert()
    {
        $ns = 'http://example.com';
        $location = 'http://example.com/xsd';
        $schemas = new Schemas();
        $schema = $schemas->insert(new Schema($ns, $location));
        $this->assertInstanceOf('\CFDIReader\SchemaValidator\Schema', $schema, "The insert method must return a Schema object");
        $this->assertCount(1, $schemas);
    }

    /**
     * @return Schemas
     */
    public function createSchemaWithCount($count, $ns, $location)
    {
        $schemas = new Schemas();
        for($i = 0 ; $i < $count ; $i++) {
            $schemas->create($ns.$i, $location.$i);
        }
        return $schemas;
    }

    public function testInsertSeveral()
    {
        $ns = 'http://example.com/';
        $location = 'http://example.com/xsd/';
        $schemas = $this->createSchemaWithCount(5, $ns, $location);
        $this->assertCount(5, $schemas, '5 namespaces where included');
        $schemas->create("{$ns}1", "{$location}X");
        $this->assertCount(5, $schemas, '5 repeated schema do not increment schemas count');
        $this->assertSame("{$location}X", $schemas->item("{$ns}1")->getLocation(), 'The old schema was overriten');
    }

    public function testRemove()
    {
        $ns = 'http://example.com/';
        $location = 'http://example.com/xsd/';
        $schemas = $this->createSchemaWithCount(7, $ns, $location);
        $schemas->remove("{$ns}2");
        $this->assertFalse($schemas->exists("{$ns}2"), 'Removed namespace 2 must not exists');
        $schemas->remove("{$ns}3");
        $this->assertFalse($schemas->exists("{$ns}3"), 'Removed namespace 3 must not exists');
        $this->assertCount(5, $schemas, 'After remove 2 items the count is 5');
        $schemas->remove("{$ns}2");
        $this->assertCount(5, $schemas, 'Remove a non existent schema do nothing');
    }

    public function testGetXsdEmpty()
    {
        $basefile = test_file_location("include-template.xsd");
        $this->assertFileExists($basefile, "File $basefile must exists");
        $schemas = new Schemas();
        $filename = tempnam(null, null);
        file_put_contents($filename, $schemas->getXsd(new Locator));
        $this->assertXmlFileEqualsXmlFile($basefile, $filename, 'Empty Xsd must match files/include-template.xsd');
        unlink($filename);
    }

    public function testGetXsdWithContents()
    {
        $basefile = test_file_location("include-commonxsd.xsd");
        $this->assertFileExists($basefile, "File $basefile must exists");

        $commonxsdfolder = test_commonxsd_location('');
        $fcfdv32 = test_commonxsd_location('cfdv32.xsd');
        $this->assertFileExists($fcfdv32, "The file $fcfdv32 for testing must exists");
        $ftimbre = test_commonxsd_location('TimbreFiscalDigital.xsd');
        $this->assertFileExists($fcfdv32, "The file $ftimbre for testing must exists");
        $locator = new Locator();
        $locator->register('http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd', $fcfdv32);
        $locator->register('http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd', $ftimbre);
        $schemas = new Schemas();
        $schemas->create('http://www.sat.gob.mx/cfd/3', 'http://www.sat.gob.mx/sitio_internet/cfd/3/cfdv32.xsd');
        $schemas->create('http://www.sat.gob.mx/TimbreFiscalDigital', 'http://www.sat.gob.mx/TimbreFiscalDigital/TimbreFiscalDigital.xsd');
        $filename = tempnam(null, null);
        // verify that the Xsd contains the location of the commonXsd folder
        $xsdcontents = $schemas->getXsd($locator);
        $this->assertContains(' schemaLocation="' . $commonxsdfolder, $xsdcontents, "The returned Xsd must contain the absolute path to $commonxsdfolder");
        // change the default XSD contents because it contains absolute paths, replace with a constant before compare
        file_put_contents($filename, str_replace($commonxsdfolder, '__COMMONXSDPATH__/', $schemas->getXsd($locator)));
        $this->assertXmlFileEqualsXmlFile($basefile, $filename, 'SAT simple include schema must match files/include-template.xsd');
    }

    public function testIteratorAggregate()
    {
        $data = [
            new Schema('a', 'aaa'),
            new Schema('b', 'bbb'),
            new Schema('c', 'ccc'),
        ];
        $schemas = new Schemas();
        for($i = 0 ; $i < count($data) ; $i++) {
            $schemas->insert($data[$i]);
        }
        $i = 0;
        foreach($schemas as $schema) {
            $this->assertSame($data[$i], $schema, "Iteration of schema index $i");
            $i = $i + 1;
        }
    }


}

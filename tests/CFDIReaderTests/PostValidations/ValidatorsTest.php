<?php

namespace CFDIReaderTests\PostValidations;

use CFDIReader\PostValidations\Validators;

class ValidatorsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Validators */
    private $validators;

    public function setUp()
    {
        parent::setUp();
        $this->validators = new Validators();
    }

    public function testAfterConstruct()
    {
        $this->assertInstanceOf(\Countable::class, $this->validators);
        $this->assertInstanceOf(\IteratorAggregate::class, $this->validators);
        $this->assertCount(0, $this->validators, "No types must exists");
        /* @var $iterator \Iterator */
        $iterator = $this->validators->getIterator();
        $this->assertFalse($iterator->valid(), "Found at least one iteration on an empty container");
    }

    public function testAppend()
    {
        $validator = new MockValidator();
        $this->validators->append($validator);
        $this->assertCount(1, $this->validators, "Validators count must be 1 after append");
        $this->validators->append($validator);
        $this->assertCount(1, $this->validators, "Validators count must be 1 after double append");
        foreach ($this->validators as $vobject) {
            $this->assertSame($validator, $vobject, "The validator included must be the same");
        }
        $this->validators->append(new MockValidator());
        $this->assertCount(2, $this->validators, "Validators count must be 2 after second append of a new object");
    }

    public function testRemove()
    {
        $validator = new MockValidator();
        $this->validators->remove($validator);
        $this->assertCount(0, $this->validators, "Remove on empty validator must count 0");
        $this->validators->append(new MockValidator());
        $this->validators->append($validator);
        $this->validators->remove($validator);
        $this->assertCount(1, $this->validators, "Validator was not removed");
    }

    public function testGetIndexAndGet()
    {
        $validator = new MockValidator();
        $this->validators->append(new MockValidator());
        $this->validators->append($validator);
        $this->assertSame(1, $this->validators->getIndex($validator), "Index of object must be 1");
        $this->assertSame($validator, $this->validators->get(1), "Object returned of index 1 must be the same");
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Validator does not exists
     */
    public function testGetException()
    {
        $this->validators->get(5);
    }
}

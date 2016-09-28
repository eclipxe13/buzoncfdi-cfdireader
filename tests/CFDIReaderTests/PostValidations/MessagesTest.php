<?php

namespace CFDIReaderTests\PostValidations;

use CFDIReader\PostValidations\Messages;

class MessagesTest extends \PHPUnit_Framework_TestCase
{
    /** @var Messages */
    private $messages;

    public function setUp()
    {
        parent::setUp();
        $this->messages = new Messages();
    }

    public function testAfterConstruct()
    {
        $this->assertCount(0, $this->messages, "Messages must be empty");
        $this->assertEquals([], $this->messages->all(), "All method must return an empty array");
    }

    public function testAppendMessages()
    {
        $texts = [
            'Message one',
            'Message two',
            'Message three',
            'Message four',
        ];
        foreach ($texts as $text) {
            $this->messages->add($text);
        }
        $this->assertCount(count($texts), $this->messages, "Must include all messages");
        $i = 0;
        foreach ($this->messages as $message) {
            $this->assertSame($texts[$i], $message, 'Messages must be the same using the iterator');
            $i = $i + 1;
        }
        $this->assertNotSame(0, $i, 'Iterator was not used');
        $this->assertSame($texts, $this->messages->all(), "All method is not returning the messages");
        $this->assertSame($texts[2], $this->messages->get(2), "Get message must return the correct text");
        $this->assertSame($texts[0], $this->messages->getFirst(), "Get first message must return the correct text");
        $this->assertSame(
            $texts[count($texts) - 1],
            $this->messages->getLast(),
            "Get last message must return the correct text"
        );
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Message does not exists
     */
    public function testGetException()
    {
        $this->messages->get(20);
    }


    public function testGetFirstAndLastFalse()
    {
        $this->assertFalse($this->messages->getFirst(), 'With no messages getFirst must return false');
        $this->assertFalse($this->messages->getLast(), 'With no messages getLast must return false');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Message must be a valid string
     */
    public function testAddInvalidString()
    {
        $this->messages->add(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Message must be a non-empty string
     */
    public function testAddEmptyString()
    {
        $this->messages->add('');
    }
}

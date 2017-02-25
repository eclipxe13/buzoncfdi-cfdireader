<?php
namespace CFDIReaderTests\PostValidations;

use CFDIReader\PostValidations\Issues;
use CFDIReader\PostValidations\Messages;
use PHPUnit\Framework\TestCase;

class IssuesTest extends TestCase
{
    /** @var Issues */
    private $issues;

    public function setUp()
    {
        parent::setUp();
        $this->issues = new Issues();
    }

    public function testAfterConstruct()
    {
        $this->assertEmpty($this->issues->types(), 'No types must exists');
        $this->assertEmpty($this->issues->all(), 'No messages must exists');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The type of messages must be a non-empty string
     */
    public function testInvalidTypeNull()
    {
        $this->issues->messages(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The type of messages must be a non-empty string
     */
    public function testInvalidTypeEmpty()
    {
        $this->issues->messages('');
    }

    public function testMessagesReturnAValidInstance()
    {
        $type = 'Notices';
        $this->assertInstanceOf(Messages::class, $this->issues->messages($type));
        $this->assertContains($type, $this->issues->types(), 'The type was not automatically registered');
    }

    private function populateContents()
    {
        $contents = [
            'ERROR' => [
                'Dummy error 1',
                'Dummy error 2',
            ],
            'WARNING' => [
                'Warning',
            ],
            'NOTICE' => [
                'Notice A',
            ],
        ];
        foreach ($contents as $type => $texts) {
            $messages = $this->issues->messages($type);
            foreach ($texts as $text) {
                $messages->add($text);
            }
        }
        return $contents;
    }

    public function testAll()
    {
        $contents = $this->populateContents();
        $this->issues->messages('DUMMY')->count();
        $this->assertCount(4, $this->issues->types(), 'Dummy type was not registered');
        $this->assertEquals($contents, $this->issues->all());
    }

    public function testIterator()
    {
        $contents = $this->populateContents();
        $this->assertEquals(array_keys($contents), $this->issues->types(), 'The types are the equals');
        $types = array_keys($contents);
        $foreachFlag = false;
        foreach ($this->issues as $type => $messages) {
            $foreachFlag = true;
            $this->assertContains($type, $types, 'Types must be registered');
            $this->assertInstanceOf(Messages::class, $messages);
        }
        $this->assertTrue($foreachFlag, 'Iterator over types was not used');
    }

    public function testImport()
    {
        $this->populateContents();
        $dest = new Issues();
        $dest->import($this->issues);
        $this->assertEquals($this->issues->types(), $dest->types(), 'After import both types must be the same');
        /* @var $messages Messages */
        foreach ($this->issues as $type => $messages) {
            $this->assertSame(
                $messages->count(),
                $dest->messages($type)->count(),
                'After import both mesages(types) must have the same count of elements'
            );
        }
    }

    public function testAdd()
    {
        $type = 'DUMMY';
        $message = 'Dummy Error';
        $this->issues->add($type, $message);
        $this->assertSame([$type], $this->issues->types(), 'The types array must contain only the DUMMY type');
        $this->assertSame($message, $this->issues->messages($type)->getFirst(), 'The message was stored');
    }
}

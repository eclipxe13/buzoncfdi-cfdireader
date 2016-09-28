<?php

namespace CFDIReaderTests\PostValidations;

use CFDIReader\PostValidations\PostValidator;
use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\CFDIReader;

class PostValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var Issues */
    private $postvalidator;

    /** @var CFDIReader */
    private static $cfdi;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$cfdi = new CFDIReader(file_get_contents(test_file_location('cfdi-valid.xml')));
    }

    public function setUp()
    {
        parent::setUp();
        $this->postvalidator = new PostValidator();
    }

    public function testAfterConstruct()
    {
        $this->assertInstanceOf('\CFDIReader\PostValidations\Issues', $this->postvalidator->issues);
        $this->assertInstanceOf('\CFDIReader\PostValidations\Validators', $this->postvalidator->validators);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid property name 'dummy'
     */
    public function testInvalidProperty()
    {
        $x = $this->postvalidator->dummy;
    }

    public function testValidateWithEmptyValidators()
    {
        $this->postvalidator->issues->add('Dummy', 'This is a sample message');
        $return = $this->postvalidator->validate(static::$cfdi);
        $this->assertCount(0, $this->postvalidator->issues->all(), 'Messages was not reset');
        $this->assertTrue($return, 'Validate must return true with no validators');
    }

    public function testValidateWithWarning()
    {
        $message = 'This is a sample warning';
        $validator = new MockValidator();
        $validator->setWarningToReturn($message);
        $this->postvalidator->validators->append($validator);
        $return = $this->postvalidator->validate(static::$cfdi);
        $this->assertTrue($return, 'Validate must return true with a warning validation');
        $messages = [
            IssuesTypes::WARNING => [$message]
        ];
        $this->assertEquals($messages, $this->postvalidator->issues->all(), 'Expected one warning message');
    }

    public function testValidateWithError()
    {
        $message = 'This is a sample error';
        $validator = new MockValidator();
        $validator->setErrorToReturn($message);
        $this->postvalidator->validators->append($validator);
        $return = $this->postvalidator->validate(static::$cfdi);
        $this->assertFalse($return, 'Validate must return false with a error validation');
        $messages = [
            IssuesTypes::ERROR => [$message]
        ];
        $this->assertEquals($messages, $this->postvalidator->issues->all(), 'Expected one warning message');
    }

    public function testValidateWithWarningPlusError()
    {
        $validator = new MockValidator();
        $validator->setWarningToReturn('Warning message');
        $validator->setErrorToReturn('Error message');
        $messages = [
            IssuesTypes::WARNING => ['Warning message'],
            IssuesTypes::ERROR => ['Error message'],
        ];
        $this->postvalidator->validators->append($validator);
        $return = $this->postvalidator->validate(static::$cfdi);
        $this->assertFalse($return, 'Validate must return false with warning and error validation');
        $this->assertEquals($messages, $this->postvalidator->issues->all(), 'Messages received are not the same');
    }
}

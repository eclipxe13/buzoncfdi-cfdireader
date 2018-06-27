<?php
namespace CFDIReaderTests\Scripts;

use CFDIReader\Scripts\Validate;
use PHPUnit\Framework\TestCase;

class ValidateTest extends TestCase
{
    public function testConstructor()
    {
        $script = '';
        $filenames = [];
        $stdOut = 'php://stdout';
        $stdErr = 'php://stderr';

        $validate = new Validate($script, $filenames);

        $this->assertEquals($script, $validate->getScript());
        $this->assertEquals($filenames, $validate->getFilenames());
        $this->assertEquals($stdOut, $validate->getStdOut());
        $this->assertEquals($stdErr, $validate->getStdErr());
        $this->assertNull($validate->getLocalPath());
    }

    public function testConstructorWithFullArguments()
    {
        $script = 'validate.php';
        $filenames = ['foo', 'bar'];
        $stdOut = 'my-stdout';
        $stdErr = 'my-stderr';
        $localPath = '/assets/';

        $validate = new Validate($script, $filenames, $stdOut, $stdErr, $localPath);

        $this->assertEquals($script, $validate->getScript());
        $this->assertEquals($filenames, $validate->getFilenames());
        $this->assertEquals($stdOut, $validate->getStdOut());
        $this->assertEquals($stdErr, $validate->getStdErr());
        $this->assertSame($localPath, $validate->getLocalPath());
    }

    public function testMake()
    {
        $script = 'command';
        $filenames = ['first', 'second', 'third'];
        $localPath = '/resources';

        $validate = Validate::make(['command', 'first', 'second', '-l', '/resources', 'third']);

        $this->assertEquals($script, $validate->getScript());
        $this->assertEquals($filenames, $validate->getFilenames());
        $this->assertSame($localPath, $validate->getLocalPath());
    }

    public function testWithInvalidArgument()
    {
        $this->expectException(\Exception::class);
        Validate::make(['', '--argument']);
    }

    public function testMakeWithoutFlags()
    {
        $script = 'command';
        $filenames = ['first', 'second', 'third'];

        $validate = Validate::make(['command', 'first', 'second', 'third']);

        $this->assertEquals($script, $validate->getScript());
        $this->assertEquals($filenames, $validate->getFilenames());
        $this->assertNull($validate->getLocalPath());
    }

    public function testMakeWithLocalPathDisabled()
    {
        $validate = Validate::make(['command', '--local-path', 'disable']);
        $this->assertSame('', $validate->getLocalPath());
    }

    public function testMakeThrowExceptionOnEmptyArgumentsArray()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot construct without arguments');

        Validate::make([]);
    }

    public function testConstructorFilenamesThrowException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('parameter 1 is not a string');

        /** @var string $fakeString Override type to test the exception and avoid phpstan warning */
        $fakeString = null;
        new Validate('', ['', $fakeString]);
    }

    public function testRunExpectUUID32()
    {
        $validate = $this->makeValidateObject([
            test_file_location('v32/real.xml'),
        ]);
        $validate->run();

        $this->assertCount(1, $validate->messages);
        $this->assertCount(1, $validate->writes);
        $this->assertContains('UUID: 80824F3B-323E-407B-8F8E-40D83FE2E69F', $validate->writes[0]);
    }

    public function testRunExpectUUID33()
    {
        $validate = $this->makeValidateObject([
            test_file_location('v33/valid.xml'),
        ]);
        $validate->run();

        $this->assertCount(1, $validate->messages);
        $this->assertCount(1, $validate->writes);
        $this->assertContains('UUID: 9FB6ED1A-5F37-4FEF-980A-7F8C83B51894', $validate->writes[0]);
    }

    public function testRunExpectErrorEmptyFilename()
    {
        $validate = $this->makeValidateObject([
            '',
        ]);
        $validate->run();

        $this->assertCount(1, $validate->messages);
        $this->assertCount(1, $validate->errors);
        $this->assertContains('FATAL: Empty filename', $validate->errors[0]);
    }

    public function testRunExpectErrorFileNotFound()
    {
        $validate = $this->makeValidateObject([
            test_file_location('non-existent'),
        ]);
        $validate->run();

        $this->assertCount(1, $validate->messages);
        $this->assertCount(1, $validate->errors);
        $this->assertContains('FATAL: not found or is not readable', $validate->errors[0]);
    }

    public function testRunExpectErrorsAndWarnings()
    {
        $validate = $this->makeValidateObject([
            test_file_location('v32/totales-subtotal.xml'),
        ]);
        $validate->run();

        $expectedMessages = [
            'totales-subtotal.xml ERROR: ',
            'totales-subtotal.xml WARNING: ',
            'UUID: e403f396-6a57-4625-adb4-bb436b00789f',
        ];

        $this->assertGreaterThanOrEqual(1, $validate->messages);
        $this->assertGreaterThanOrEqual(1, $validate->errors);
        $this->assertGreaterThanOrEqual(1, $validate->writes);

        $allMessagesText = implode("\n", $validate->messages);
        foreach ($expectedMessages as $expectedMessage) {
            $this->assertContains($expectedMessage, $allMessagesText);
        }
    }

    public function testRunExpectFatalError()
    {
        $validate = $this->makeValidateObject([
            test_file_location('v32/noseal.xml'),
        ]);
        $validate->run();

        $expectedMessages = [
            'noseal.xml FATAL: ',
        ];

        $this->assertCount(1, $validate->messages);
        $this->assertCount(1, $validate->errors);
        foreach ($expectedMessages as $index => $expectedMessage) {
            $this->assertContains($expectedMessage, $validate->messages[$index]);
        }
    }

    public function testRunExpectMultipleFiles()
    {
        $validate = $this->makeValidateObject([
            test_file_location('v32/valid.xml'),
            test_file_location('v32/totales-subtotal.xml'),
        ]);
        $validate->run();

        $expectedMessages = [
            'valid.xml UUID: e403f396-6a57-4625-adb4-bb436b00789f',
            'totales-subtotal.xml UUID: e403f396-6a57-4625-adb4-bb436b00789f',
        ];

        $this->assertCount(2, $validate->writes);
        foreach ($expectedMessages as $index => $expectedMessage) {
            $this->assertContains($expectedMessage, $validate->writes[$index]);
        }
    }

    private function makeValidateObject(array $filenames)
    {
        return new ValidateArrayOutput('', $filenames);
    }
}

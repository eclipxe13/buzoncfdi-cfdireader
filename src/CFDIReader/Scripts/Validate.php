<?php
namespace CFDIReader\Scripts;

use CFDIReader\CFDIFactory;

class Validate
{
    /** @var string */
    private $script;

    /** @var string[] */
    private $filenames;

    /** @var string defaults to php://stdout */
    private $stdOut;

    /** @var string defaults to php://stderr */
    private $stdErr;

    /**
     * Validate constructor.
     * @param string $script
     * @param string[] $filenames
     * @param string $stdOut
     * @param string $stdErr
     * @throws \TypeError if an item in $filenames is not a string
     */
    public function __construct(
        string $script,
        array $filenames,
        string $stdOut = 'php://stdout',
        string $stdErr = 'php://stderr'
    ) {
        $filenames = array_values($filenames);
        foreach ($filenames as $index => $value) {
            if (! is_string($value)) {
                throw new \InvalidArgumentException("filename parameter $index is not a string");
            }
        }
        $this->script = $script;
        $this->filenames = $filenames;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
    }

    /**
     * Use this function to build the validate object from arguments values
     * @param string[] $argv
     * @return Validate
     */
    public static function make(array $argv): Validate
    {
        if (! count($argv)) {
            throw new \InvalidArgumentException('Cannot construct without arguments');
        }
        $script = array_shift($argv);
        return new self($script, $argv);
    }

    /**
     * write a text to stdout
     * @param string $message
     * @return void
     */
    protected function write(string $message)
    {
        file_put_contents($this->stdOut, $message . "\n", FILE_APPEND);
    }

    /**
     * write a text to stderr
     * @param string $message
     * @return void
     */
    protected function error(string $message)
    {
        file_put_contents($this->stdErr, $message . "\n", FILE_APPEND);
    }

    /**
     * Run this script
     * @return void
     */
    public function run()
    {
        $factory = new CFDIFactory();
        foreach ($this->filenames as $filename) {
            $this->runFilename($factory, $filename);
        }
    }

    /**
     * Run only a filename, used in the run loop
     * @param CFDIFactory $factory
     * @param string $argument
     * @return void
     */
    protected function runFilename(CFDIFactory $factory, string $argument)
    {
        if ('' === $argument) {
            $this->error('FATAL: Empty filename');
            return;
        }
        $filename = realpath($argument);
        if ('' === $filename || ! is_file($filename) || ! is_readable($filename)) {
            $this->error("File $argument FATAL: not found or is not readable");
            return;
        }
        // do the object creation
        try {
            $errors = [];
            $warnings = [];
            $reader = $factory->newCFDIReader(file_get_contents($filename), $errors, $warnings);
            foreach ($errors as $message) {
                $this->error("File $argument ERROR: $message");
            }
            foreach ($warnings as $message) {
                $this->error("File $argument WARNING: $message");
            }
            $this->write("File $argument UUID: " . $reader->getUUID());
        } catch (\Exception $ex) {
            $this->error("File $argument FATAL: " . $ex->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getScript(): string
    {
        return $this->script;
    }

    /**
     * @return string[]
     */
    public function getFilenames(): array
    {
        return $this->filenames;
    }

    /**
     * @return string
     */
    public function getStdOut(): string
    {
        return $this->stdOut;
    }

    /**
     * @return string
     */
    public function getStdErr(): string
    {
        return $this->stdErr;
    }
}

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
     * @param string   $script
     * @param string[] $filenames
     * @param string   $stdOut
     * @param string   $stdErr
     */
    public function __construct($script, array $filenames, $stdOut = 'php://stdout', $stdErr = 'php://stderr')
    {
        if (! is_string($script)) {
            throw new \InvalidArgumentException('script argument is not a string');
        }
        $filenames = array_values($filenames);
        foreach ($filenames as $index => $value) {
            if (! is_string($value)) {
                throw new \InvalidArgumentException("filename parameter $index is not a string");
            }
        }
        if (! is_string($stdOut)) {
            throw new \InvalidArgumentException('argument stdout is not a string');
        }
        if (! is_string($stdErr)) {
            throw new \InvalidArgumentException('argument stderr is not a string');
        }
        $this->script = $script;
        $this->filenames = $filenames;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
    }

    /**
     * Use this function to build the validate object from arguments values
     * @param  string[] $argv
     * @return Validate
     */
    public static function make(array $argv)
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
     */
    protected function write($message)
    {
        file_put_contents($this->stdOut, $message . "\n", FILE_APPEND);
    }

    /**
     * write a text to stderr
     * @param string $message
     */
    protected function error($message)
    {
        file_put_contents($this->stdErr, $message . "\n", FILE_APPEND);
    }

    /**
     * Run this script
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
     * @param string      $argument
     */
    protected function runFilename(CFDIFactory $factory, $argument)
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
    public function getScript()
    {
        return $this->script;
    }

    /**
     * @return string[]
     */
    public function getFilenames()
    {
        return $this->filenames;
    }

    /**
     * @return string
     */
    public function getStdOut()
    {
        return $this->stdOut;
    }

    /**
     * @return string
     */
    public function getStdErr()
    {
        return $this->stdErr;
    }
}

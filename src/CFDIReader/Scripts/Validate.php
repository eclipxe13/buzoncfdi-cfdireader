<?php
namespace CFDIReader\Scripts;

use CFDIReader\CFDIFactory;

class Validate
{
    /** @var string */
    private $script;
    /** @var string[] */
    private $filenames;

    /** @var string */
    private $stdOut;

    /** @var string */
    private $stdErr;

    public function __construct($script, array $filenames, $stdOut = 'php://stdout', $stdErr = 'php://stderr')
    {
        if (! is_string($script)) {
            throw new \InvalidArgumentException('script argument is not a string');
        }
        $filenames = array_values($filenames);
        foreach ($filenames as $index => $value) {
            if (! is_string($value)) {
                throw new \InvalidArgumentException("parameter $index is not a string");
            }
        }
        $this->script = $script;
        $this->filenames = $filenames;
        $this->stdOut = $stdOut;
        $this->stdErr = $stdErr;
    }

    public static function make(array $argv)
    {
        if (! count($argv)) {
            throw new \InvalidArgumentException('Cannot construct without filenames');
        }
        $script = array_shift($argv);
        return new self($script, $argv);
    }

    protected function write($message)
    {
        file_put_contents($this->stdOut, $message . "\n", FILE_APPEND);
    }

    protected function error($message)
    {
        file_put_contents($this->stdErr, $message . "\n", FILE_APPEND);
    }

    public function run()
    {
        $factory = new CFDIFactory();

        foreach ($this->filenames as $current) {
            if ('' === $current) {
                $this->error('FATAL: Empty filename');
                continue;
            }
            $filename = realpath($current);
            if ('' === $filename || ! is_file($filename) || ! is_readable($filename)) {
                $this->error("File $current FATAL: not found or is not readable");
                continue;
            }
            // do the object creation
            $errors = [];
            $warnings = [];
            try {
                $reader = $factory->newCFDIReader(file_get_contents($filename), $errors, $warnings);
                foreach ($errors as $message) {
                    $this->error("File $current ERROR: $message");
                }
                foreach ($warnings as $message) {
                    $this->error("File $current WARNING: $message");
                }
                $this->write("File $current UUID: " . $reader->getUUID());
            } catch (\Exception $ex) {
                $this->error("File $current FATAL: " . $ex->getMessage());
            }
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

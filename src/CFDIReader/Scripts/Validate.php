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

    /** @var string|null config variable to create the factory */
    private $localPath;

    /**
     * Validate constructor.
     * @param string $script
     * @param string[] $filenames
     * @param string $stdOut
     * @param string $stdErr
     * @param string|null $localPath Default is null to use library path to store resources
     * @throws \InvalidArgumentException if an item in $filenames is not a string
     */
    public function __construct(
        string $script,
        array $filenames,
        string $stdOut = 'php://stdout',
        string $stdErr = 'php://stderr',
        string $localPath = null
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
        $this->localPath = $localPath;
    }

    /**
     * Use this function to build the validate object from arguments values
     * @param string[] $arguments
     * @return Validate
     */
    public static function make(array $arguments): Validate
    {
        if (! count($arguments)) {
            throw new \InvalidArgumentException('Cannot construct without arguments');
        }
        $script = array_shift($arguments);

        $filenames = [];
        $localPath = null;
        while (null !== $argument = array_shift($arguments)) {
            if (in_array($argument, ['--local-path', '-l'])) {
                $localPath = (count($arguments)) ? array_shift($arguments) : '';
                continue;
            }
            if (false === strpos($argument, '-')) {
                $filenames[] = $argument;
                continue;
            }
            throw new \InvalidArgumentException("Invalid argument '$argument'");
        }

        $command = new self($script, $filenames);
        $command->localPath = (null !== $localPath && 'disable' === $localPath) ? '' : $localPath;
        return $command;
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
        $factory = new CFDIFactory($this->localPath);
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

    public function getStdOut(): string
    {
        return $this->stdOut;
    }

    public function getStdErr(): string
    {
        return $this->stdErr;
    }

    /**
     * If it is null then the factory will use library path
     * If it is empty then local path is disabled
     * Otherwise the specified path will be used
     * @return null|string
     */
    public function getLocalPath()
    {
        return $this->localPath;
    }
}

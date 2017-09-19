<?php
namespace CFDIReaderTests\Scripts;

use CFDIReader\Scripts\Validate;

class ValidateArrayOutput extends Validate
{
    public $writes = [];
    public $errors = [];
    public $messages = [];

    protected function write(string $message)
    {
        $this->writes[] = $message;
        $this->messages[] = $message;
    }

    protected function error(string $message)
    {
        $this->errors[] = $message;
        $this->messages[] = $message;
    }
}

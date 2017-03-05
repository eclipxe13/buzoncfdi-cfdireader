<?php
namespace CFDIReaderTests\Scripts;

use CFDIReader\Scripts\Validate;

class ValidateArrayOutout extends Validate
{
    public $writes = [];
    public $errors = [];
    public $messages = [];

    protected function write($message)
    {
        $this->writes[] = $message;
        $this->messages[] = $message;
    }

    protected function error($message)
    {
        $this->errors[] = $message;
        $this->messages[] = $message;
    }
}

<?php

namespace CFDIReaderTests\PostValidations;

use CFDIReader\PostValidations\ValidatorInterface;
use CFDIReader\PostValidations\IssuesTypes;

/**
 * Implements the interface ValidatorInterface with a testing object
 */
class MockValidator implements ValidatorInterface
{
    private $warning;
    private $error;

    public function validate(\CFDIReader\CFDIReader $cfdi, \CFDIReader\PostValidations\Issues $issues)
    {
        if ($this->warning) {
            $issues->add(IssuesTypes::WARNING, $this->warning);
        }
        if ($this->error) {
            $issues->add(IssuesTypes::ERROR, $this->error);
        }
    }

    public function setWarningToReturn($message)
    {
        $this->warning = $message;
    }

    public function setErrorToReturn($message)
    {
        $this->error = $message;
    }
}

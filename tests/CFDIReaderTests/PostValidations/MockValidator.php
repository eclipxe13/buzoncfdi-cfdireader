<?php

namespace CFDIReaderTests\PostValidations;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;
use CFDIReader\PostValidations\ValidatorInterface;
use CFDIReader\PostValidations\IssuesTypes;

/**
 * Implements the interface ValidatorInterface with a testing object
 */
class MockValidator implements ValidatorInterface
{
    private $warning;
    private $error;

    public function validate(CFDIReader $cfdi, Issues $issues)
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

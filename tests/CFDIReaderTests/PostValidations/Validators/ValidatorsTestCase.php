<?php
namespace CFDIReaderTests\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;
use PHPUnit\Framework\TestCase;

class ValidatorsTestCase extends TestCase
{
    /** @var CFDIReader */
    protected $cfdi;
    /** @var Issues */
    protected $issues;

    protected function setupWithFile($filename, $requireTimbre = false)
    {
        $this->cfdi = new CFDIReader(file_get_contents(test_file_location($filename)), $requireTimbre);
        $this->issues = new Issues();
    }
}

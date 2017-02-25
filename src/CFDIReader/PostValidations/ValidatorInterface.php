<?php
namespace CFDIReader\PostValidations;

use CFDIReader\CFDIReader;

interface ValidatorInterface
{
    public function validate(CFDIReader $cfdi, Issues $issues);
}

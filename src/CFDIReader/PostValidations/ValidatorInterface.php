<?php
namespace CFDIReader\PostValidations;

use CFDIReader\CFDIReader;

interface ValidatorInterface
{
    /**
     * Method to perform the validation on the CFDIReader.
     * If the validation process found some issue then it must append it to the issues object as:
     * - Error: When the CFDI should be revoked.
     * - Warning: When the CFDI contains somethind you notice but is no reason to revoke the document.
     *
     * @param CFDIReader $cfdi
     * @param Issues $issues
     * @return void
     */
    public function validate(CFDIReader $cfdi, Issues $issues);
}

<?php
namespace CFDIReader\PostValidations;

class IssuesTypes
{
    /**
     * Use this type of issue when is so important that the CFDI should be revoked.
     */
    const ERROR = 'ERROR';

    /**
     * Use this type of issue to report information to be noticed but not as high to be revoked.
     */
    const WARNING = 'WARNING';
}

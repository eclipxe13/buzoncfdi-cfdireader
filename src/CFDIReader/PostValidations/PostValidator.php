<?php
namespace CFDIReader\PostValidations;

use CFDIReader\CFDIReader;

/**
 * Class to run validators over a CFDIReader
 *
 * @property-read Validators|ValidatorInterface[] $validators Collection of validators to run
 * @property-read Issues|Messages[] $issues Collection of issues found
 */
class PostValidator
{
    /** @var Validators **/
    private $validators;

    /** @var Issues **/
    private $issues;

    public function __construct()
    {
        $this->issues = new Issues();
        $this->validators = new Validators();
    }

    public function validate(CFDIReader $cfdi)
    {
        // reset issues
        $this->issues = new Issues();
        foreach ($this->validators as $validator) {
            /* @var $validator \CFDIReader\PostValidations\ValidatorInterface */
            // a new issues to be populated
            $issues = new Issues();
            // ask the validator command to work
            $validator->validate($cfdi, $issues);
            // import all the issues found
            $this->issues->import($issues);
        }
        // return false if there are ERROR messages
        return (! $this->issues->messages(IssuesTypes::ERROR)->count());
    }

    public function __get($name)
    {
        if ('issues' === $name) {
            return $this->issues;
        }
        if ('validators' === $name) {
            return $this->validators;
        }
        throw new \InvalidArgumentException("Invalid property name '$name'");
    }
}

<?php

namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Messages;
use CFDIReader\PostValidations\Issues;
use CFDIReader\PostValidations\IssuesTypes;

/**
 * Decorator class to write Validators using some protected helper methods
 */
abstract class AbstractValidator implements \CFDIReader\PostValidations\ValidatorInterface
{
    /**
     * @var Messages
     */
    protected $errors;

    /**
     * @var Messages
     */
    protected $warnings;

    /**
     * @var \SimpleXMLElement
     */
    protected $comprobante;

    /**
     * Configure this helper class
     * @param CFDIReader $cfdi
     * @param Issues $issues
     */
    protected function setup(CFDIReader $cfdi, Issues $issues)
    {
        $this->errors = $issues->messages(IssuesTypes::ERROR);
        $this->warnings = $issues->messages(IssuesTypes::WARNING);
        $this->comprobante = $cfdi->comprobante();
    }

    /**
     * Get a numeric value from a decimal
     * @param string $input
     * @return float
     */
    protected function value($input)
    {
        return floatval($input);
    }

    /**
     * Compare two numbers using a delta
     * @param float $first
     * @param float $second
     * @return bool
     */
    protected function compare($first, $second)
    {
        return (abs($first - $second) < $this->delta);
    }

    /**
     * Compute the sum of a collection of nodes considering an attribute
     * @param \SimpleXMLElement $collection
     * @param string $attribute
     * @return float
     */
    protected function sumNodes($collection, $attribute) {
        $sum = 0;
        foreach($collection as $node) {
            $sum = $sum + $this->value($node[$attribute]);
        }
        return $sum;
    }

    // do not override the validate function
    abstract public function validate(CFDIReader $cfdi, Issues $issues);


}
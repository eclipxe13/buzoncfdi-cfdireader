<?php

namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Messages;
use CFDIReader\PostValidations\Issues;
use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\ValidatorInterface;
use SimpleXMLElement;

/**
 * Decorator class to write Validators using some protected helper methods
 */
abstract class AbstractValidator implements ValidatorInterface
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
     * @var SimpleXMLElement
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
     * Compare two numbers using a delta abs(n - m) <= d
     * @param float $first
     * @param float $second
     * @param float|null $delta
     * @return bool
     */
    protected function compare($first, $second, $delta = null)
    {
        if (null === $delta) {
            $delta = $this->compareDelta();
        }
        return (abs($first - $second) <= $delta);
    }

    /**
     * @return float
     */
    protected function compareDelta()
    {
        return 0.001;
    }

    /**
     * Compute the sum of a collection of nodes considering an attribute
     * @param SimpleXMLElement $collection
     * @param string $attribute
     * @return float
     */
    protected function sumNodes(SimpleXMLElement $collection = null, $attribute = null)
    {
        if (null === $collection) {
            return 0;
        }
        $sum = 0;
        if (! $attribute) {
            foreach ($collection as $node) {
                $sum = $sum + $this->value($node);
            }
        } else {
            foreach ($collection as $node) {
                $sum = $sum + $this->value($node[$attribute]);
            }
        }
        return $sum;
    }
}

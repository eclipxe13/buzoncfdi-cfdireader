<?php
namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;
use CFDIReader\PostValidations\IssuesTypes;
use CFDIReader\PostValidations\Messages;
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
    protected function sumNodes(SimpleXMLElement $collection = null, $attribute = '')
    {
        if (null === $collection) {
            return 0;
        }
        $sum = 0;
        if ('' === $attribute) {
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

    /**
     * Return the node in the path inside the Comprobante
     * Returns null if the node does not exists
     *
     * @param string[] ...$path
     * @return SimpleXMLElement|null
     */
    protected function xmlNode(string ...$path)
    {
        $node = $this->comprobante;
        foreach ($path as $level) {
            if (! isset($node->{$level})) {
                return null;
            }
            $node = $node->{$level};
        }
        return $node;
    }

    /**
     * Get the attribute content of a comprobante child
     * If the node does not exists or the attribute does not exists return an empty string
     *
     * The last argument is the attribute name
     *
     * @param string[] ...$path
     * @return string
     */
    protected function xmlAttr(string ...$path): string
    {
        $attribute = array_pop($path);
        $node = $this->xmlNode(...$path);
        return (null !== $node) ? (string) $node[$attribute] : '';
    }
}

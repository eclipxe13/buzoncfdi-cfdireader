<?php

namespace CFDIReader\SchemaValidator;

use DOMDocument;
use DOMXPath;

/**
 * This class is a SchemaValidator
 * It is needed because some XML can contain more than one external schema
 * and DOM library fails to load it.
 *
 * It uses Locator class to store locally the xsd files and build a generic
 * import schema that uses that files and DOM library can perform the validations
 */
class SchemaValidator
{

    /** @var Locator */
    private $locator;
    private $error = '';

    /**
     * Create the SchemaValidator
     * @param Locator $locator NULL or an instanced Locator, if null a locator with default parameters is created
     */
    public function __construct(Locator $locator = null)
    {
        if (null === $locator) {
            $locator = new Locator();
        }
        $this->locator = $locator;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getLocator()
    {
        return $this->locator;
    }

    /**
     * validate the content using the current locator
     * @param string $content The XML content on UTF-8
     * @return boolean
     */
    public function validate($content)
    {
        // encapsulate the function inside libxml_use_internal_errors
        if (true !== $libxml_use_internal_errors = libxml_use_internal_errors(true)) {
            $r = $this->validate($content);
            libxml_use_internal_errors(false);
            return $r;
        }

        // input validation
        if (!is_string($content) or $content === "") {
            throw new \InvalidArgumentException('The content to validate must be a non-empty string');
        }

        // create the DOMDocument object
        $dom = new DOMDocument();
        $dom->loadXML($content, LIBXML_ERR_ERROR);
        // check for errors on load XML
        foreach(libxml_get_errors() as $xmlerror) {
            libxml_clear_errors();
            return $this->registerError('Malformed XML Document: ' . $xmlerror->message);
        }
        // create the schemas collection and validate only if needed
        $schemas = $this->buildSchemas($dom);
        if ($schemas->count()) {
            // build the unique importing schema using the locator
            $xsd = $schemas->getXsd($this->locator);
            // ask the DOM to validate using the xsd
            $dom->schemaValidateSource($xsd);
            // check for errors on load XML
            foreach(libxml_get_errors() as $xmlerror) {
                libxml_clear_errors();
                return $this->registerError('Invalid XML Document: ' . $xmlerror->message);
            }
        }
        // return true
        return !$this->registerError('');
    }

    /**
     * Utility function to setup the error property;
     * @param string $error
     * @return boolean Always FALSE
     */
    private function registerError($error)
    {
        $this->error = trim($error);
        return false;
    }

    /**
     * Utility function to retrieve a list of namespaces with the schema location
     * @param DOMDocument $dom
     * @return Schemas
     */
    private function buildSchemas(DOMDocument $dom)
    {
        $schemas = new Schemas();
        $xpath = new DOMXPath($dom);
        if (false !== $schemasList = $xpath->query('//@xsi:schemaLocation', null, true)) {
            for($s = 0 ; $s < $schemasList->length ; $s++) {
                if (false != $content = $schemasList->item($s)->nodeValue) {
                    $parts = array_values(array_filter(explode(' ', $content)));
                    if (0 !== count($parts) % 2) {
                        throw new \RuntimeException("The schemaLocation does not contain pairs");
                    }
                    for($k = 0 ; $k < count($parts) ; $k = $k + 2) {
                        $schemas->create($parts[$k], $parts[$k + 1]);
                    }
                }
            }
        }
        return $schemas;
    }
}

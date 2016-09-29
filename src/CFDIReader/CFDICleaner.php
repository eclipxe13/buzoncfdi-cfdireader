<?php

namespace CFDIReader;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

/**
 * Class to clean CFDI and avoid bad practices
 * Strictly speaking, CFDI must acomplish all XML rules, including that any other
 * xml element must be isolated in its own schema and follow their own rules
 * The common practice (allowed by SAT) is that the CFDI is created, signed and after
 * some nodes are attatched, some of them does not accomply the XML Schemas
 * This is why it's better to clear Comprobante/Addenda and remove unused namespaces
 * @package CFDIReader
 */
class CFDICleaner
{

    /** @var DOMDocument */
    protected $dom;

    /**
     * CFDICleaner constructor.
     * @param string $content
     */
    public function __construct($content)
    {
        if (!empty($content)) {
            $this->loadContent($content);
        }
    }

    /**
     * Method to clean content and return the result
     * If an error occurs, an exception is thrown
     * @param string $content
     * @return string
     */
    public static function staticClean($content)
    {
        $cleaner = new self($content);
        $cleaner->clean();
        return $cleaner->retrieveXml();
    }

    /**
     * Check if the CFDI version is complatible to this class
     * @param string $version
     * @return bool
     */
    public static function isVersionAllowed($version)
    {
        return in_array($version, ["3.2"]);
    }

    /**
     * Check if a given namespace is allowed (must not be removed from CFDI)
     * @param string $namespace
     * @return bool
     */
    public static function isNameSpaceAllowed($namespace)
    {
        $fixedNS = [
            "http://www.w3.org/2001/XMLSchema-instance",
            "http://www.w3.org/XML/1998/namespace",
        ];
        foreach ($fixedNS as $ns) {
            if (0 === strcasecmp($ns, $namespace)) {
                return true;
            }
        }
        $willcardNS = [
            "http://www.sat.gob.mx/",
        ];
        foreach ($willcardNS as $ns) {
            if (0 === strpos($namespace, $ns)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Apply all removals (Addenda, Non SAT Nodes and Non SAT namespaces)
     */
    public function clean()
    {
        $this->removeAddenda();
        $this->removeNonSatNSNodes();
        $this->removeNonSatNSschemaLocations();
        $this->removeUnusedNamespaces();
    }

    /**
     * Load the string content as a CFDI
     * This is exposed to reuse the current object instead of create a new instance
     * @param string $content
     */
    public function loadContent($content)
    {
        $dom = new DOMDocument();
        $dom->loadXML($content, LIBXML_ERR_WARNING);
        if (true == $loaderror = libxml_get_last_error()) {
            throw new CFDICleanerException('XML Error: ' . $loaderror);
        }
        $prefix = $dom->lookupPrefix("http://www.sat.gob.mx/cfd/3");
        if (!$prefix) {
            throw new CFDICleanerException('The XML document is not a CFDI');
        }
        $version = $this->xpathQuery('/' . $prefix . ':Comprobante/@version', $dom->documentElement);
        if ($version->length != 1) {
            throw new CFDICleanerException('The XML document does not contains a version');
        }
        if (!$this->isVersionAllowed($version->item(0)->nodeValue)) {
            throw new CFDICleanerException(
                'The XML document version "' . $version->item(0)->nodeValue . '" is not compatible'
            );
        }
        $this->dom = $dom;
    }

    /**
     * Get the XML content of the CFDI
     * @return string
     */
    public function retrieveXml()
    {
        return $this->dom->saveXML();
    }

    /**
     * Helper function to Perform a XPath Query using
     * @param string $query
     * @param DOMNode|null $element
     * @return DOMNodeList
     */
    private function xpathQuery($query, DOMNode $element = null)
    {
        $element = $element ?: $this->dom->documentElement;
        return (new DOMXPath($element->ownerDocument))->query($query, $element);
    }

    /**
     * Procedure to remove the Comprobante/Addenda node
     */
    public function removeAddenda()
    {
        $prefix = $this->dom->lookupPrefix("http://www.sat.gob.mx/cfd/3");
        $query = '/' . $prefix . ':Comprobante/' . $prefix . ':Addenda';
        $addendas = $this->xpathQuery($query);
        if ($addendas->length == 0) {
            return;
        }
        for ($i = 0; $i < $addendas->length; $i++) {
            $addenda = $addendas->item($i);
            $addenda->parentNode->removeChild($addenda);
        }
    }

    /**
     * Procedure to drop schemaLocations that are not allowed
     * If the schemaLocation is empty then remove the attribute
     */
    public function removeNonSatNSschemaLocations()
    {
        $xsi = $this->dom->lookupPrefix("http://www.w3.org/2001/XMLSchema-instance");
        if (!$xsi) {
            return;
        }
        $schemaLocations = $this->xpathQuery("//@$xsi:schemaLocation");
        if (false === $schemaLocations or $schemaLocations->length === 0) {
            return;
        }
        for ($s = 0; $s < $schemaLocations->length; $s++) {
            $this->removeNonSatNSschemaLocation($schemaLocations->item($s));
        }
    }

    /**
     * @param DOMNode $schemaLocation This is the attribute
     */
    private function removeNonSatNSschemaLocation(DOMNode $schemaLocation)
    {
        $source = $schemaLocation->nodeValue;
        $parts = array_values(array_filter(explode(' ', $source)));
        if (0 !== count($parts) % 2) {
            throw new CFDICleanerException("The schemaLocation value '" . $source . "' must have even number of URIs");
        }
        $modified = "";
        for ($k = 0; $k < count($parts); $k = $k + 2) {
            if (!$this->isNameSpaceAllowed($parts[$k])) {
                continue;
            }
            $modified .= $parts[$k] . " " . $parts[$k + 1] . " ";
        }
        $modified = rtrim($modified, " ");
        if ($source == $modified) {
            return;
        }
        if ("" !== $modified) {
            $schemaLocation->nodeValue = $modified;
        } else {
            $schemaLocation->parentNode->attributes->removeNamedItemNS(
                $schemaLocation->namespaceURI,
                $schemaLocation->nodeName
            );
        }
    }

    /**
     * Procedure to remove all nodes that are not allowed
     */
    public function removeNonSatNSNodes()
    {
        $nss = [];
        foreach ($this->xpathQuery('//namespace::*') as $node) {
            $namespace = $node->nodeValue;
            if ($this->isNameSpaceAllowed($namespace)) {
                continue;
            }
            $nss[] = $namespace;
        }
        if (!count($nss)) {
            return;
        }
        foreach ($nss as $namespace) {
            $this->removeNonSatNSNode($namespace);
        }
    }

    /**
     * Procedure to remove all nodes from an specific namespace
     * @param string $namespace
     */
    private function removeNonSatNSNode($namespace)
    {
        foreach ($this->dom->getElementsByTagNameNS($namespace, "*") as $children) {
            $children->parentNode->removeChild($children);
        }
    }

    /**
     * Procedure to remove not allowed xmlns definitions
     */
    public function removeUnusedNamespaces()
    {
        $nss = [];
        foreach ($this->xpathQuery('//namespace::*') as $node) {
            $namespace = $node->nodeValue;
            if (!$namespace or $this->isNameSpaceAllowed($namespace)) {
                continue;
            }
            $prefix = $this->dom->lookupPrefix($namespace);
            $nss[$prefix] = $namespace;
        }
        $nss = array_unique($nss);
        foreach ($nss as $prefix => $namespace) {
            $this->dom->documentElement->removeAttributeNS($namespace, $prefix);
        }
    }
}

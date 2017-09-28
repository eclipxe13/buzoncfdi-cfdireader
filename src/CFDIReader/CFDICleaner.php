<?php
namespace CFDIReader;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

/**
 * Class to clean CFDI and avoid bad common practices.
 *
 * Strictly speaking, CFDI must accomplish all XML rules, including that any other
 * XML element must be isolated in its own namespace and follow their own XSD rules.
 *
 * The common practice (allowed by SAT) is that the CFDI is created, signed and
 * some nodes are attached after sign, some of them does not comply the XML standard.
 *
 * This is why it's better to clear Comprobante/Addenda and remove unused namespaces
 *
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
    public function __construct(string $content)
    {
        if (! empty($content)) {
            $this->loadContent($content);
        }
    }

    /**
     * Method to clean content and return the result
     * If an error occurs, an exception is thrown
     * @param string $content
     * @return string
     */
    public static function staticClean($content): string
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
    public static function isVersionAllowed(string $version): bool
    {
        return in_array($version, ['3.2', '3.3']);
    }

    /**
     * Check if a given namespace is allowed (must not be removed from CFDI)
     * @param string $namespace
     * @return bool
     */
    public static function isNameSpaceAllowed(string $namespace): bool
    {
        $fixedNS = [
            'http://www.w3.org/2001/XMLSchema-instance',
            'http://www.w3.org/XML/1998/namespace',
        ];
        foreach ($fixedNS as $ns) {
            if (0 === strcasecmp($ns, $namespace)) {
                return true;
            }
        }
        $willcardNS = [
            'http://www.sat.gob.mx/',
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
     * @return void
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
     *
     * @param string $content
     *
     * @throws CFDICleanerException when the content is not valid xml
     * @throws CFDICleanerException when the document does not use the namespace http://www.sat.gob.mx/cfd/3
     * @throws CFDICleanerException when cannot find a Comprobante version (or Version) attribute
     * @throws CFDICleanerException when the version is not compatible
     *
     * @return void
     */
    public function loadContent(string $content)
    {
        // run this method with libxml internal errors enabled
        if (true !== libxml_use_internal_errors(true)) {
            try {
                $this->loadContent($content);
            } finally {
                libxml_use_internal_errors(false);
            }
        }

        libxml_clear_errors(); // clear previous libxml errors
        $dom = new DOMDocument();
        $dom->loadXML($content, LIBXML_NOWARNING | LIBXML_NONET);
        if (false !== $loaderror = libxml_get_last_error()) {
            libxml_clear_errors();  // clear recently libxml errors
            throw new CFDICleanerException('XML Error: ' . $loaderror->message);
        }
        $prefix = $dom->lookupPrefix('http://www.sat.gob.mx/cfd/3');
        if (! $prefix) {
            throw new CFDICleanerException('The XML document is not a CFDI');
        }
        $version = $this->xpathQuery('/' . $prefix . ':Comprobante/@version', $dom->documentElement);
        if ($version->length != 1) {
            $version = $this->xpathQuery('/' . $prefix . ':Comprobante/@Version', $dom->documentElement);
        }
        if ($version->length != 1) {
            throw new CFDICleanerException('The XML document does not contains a version');
        }
        if (! $this->isVersionAllowed($version->item(0)->nodeValue)) {
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
    public function retrieveXml(): string
    {
        return $this->dom->saveXML();
    }

    /**
     * Helper function to Perform a XPath Query using
     * @param string $query
     * @param DOMNode|null $element
     * @return DOMNodeList
     */
    private function xpathQuery(string $query, DOMNode $element = null): DOMNodeList
    {
        $element = $element ?: $this->dom->documentElement;
        $nodelist = (new DOMXPath($element->ownerDocument))->query($query, $element);
        if (false === $nodelist) {
            $nodelist = new DOMNodeList();
        }
        return $nodelist;
    }

    /**
     * Procedure to remove the Comprobante/Addenda node
     *
     * @return void
     */
    public function removeAddenda()
    {
        $prefix = $this->dom->lookupPrefix('http://www.sat.gob.mx/cfd/3');
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
     *
     * @return void
     */
    public function removeNonSatNSschemaLocations()
    {
        $xsi = $this->dom->lookupPrefix('http://www.w3.org/2001/XMLSchema-instance');
        if (! $xsi) {
            return;
        }
        $schemaLocations = $this->xpathQuery("//@$xsi:schemaLocation");
        if ($schemaLocations->length === 0) {
            return;
        }
        for ($s = 0; $s < $schemaLocations->length; $s++) {
            $this->removeNonSatNSschemaLocation($schemaLocations->item($s));
        }
    }

    /**
     * @param DOMNode $schemaLocation This is the attribute
     * @return void
     */
    private function removeNonSatNSschemaLocation(DOMNode $schemaLocation)
    {
        $source = $schemaLocation->nodeValue;
        $parts = array_values(array_filter(explode(' ', $source)));
        $partsCount = count($parts);
        if (0 !== $partsCount % 2) {
            throw new CFDICleanerException("The schemaLocation value '" . $source . "' must have even number of URIs");
        }
        $modified = '';
        for ($k = 0; $k < $partsCount; $k = $k + 2) {
            if (! $this->isNameSpaceAllowed($parts[$k])) {
                continue;
            }
            $modified .= $parts[$k] . ' ' . $parts[$k + 1] . ' ';
        }
        $modified = rtrim($modified, ' ');
        if ($source == $modified) {
            return;
        }
        if ('' !== $modified) {
            $schemaLocation->nodeValue = $modified;
        } else {
            $schemaLocation->parentNode->attributes->removeNamedItemNS(
                $schemaLocation->namespaceURI,
                $schemaLocation->nodeName
            );
        }
    }

    /**
     * Procedure to remove all nodes that are not from an allowed namespace
     * @return void
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
        if (! count($nss)) {
            return;
        }
        foreach ($nss as $namespace) {
            $this->removeNonSatNSNode($namespace);
        }
    }

    /**
     * Procedure to remove all nodes from an specific namespace
     * @param string $namespace
     * @return void
     */
    private function removeNonSatNSNode(string $namespace)
    {
        foreach ($this->dom->getElementsByTagNameNS($namespace, '*') as $children) {
            $children->parentNode->removeChild($children);
        }
    }

    /**
     * Procedure to remove not allowed xmlns definitions
     * @return void
     */
    public function removeUnusedNamespaces()
    {
        $nss = [];
        foreach ($this->xpathQuery('//namespace::*') as $node) {
            $namespace = $node->nodeValue;
            if (! $namespace || $this->isNameSpaceAllowed($namespace)) {
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

<?php
namespace CFDIReader;

use SimpleXMLElement;
use DOMDocument;

/**
 * CFDI Reader immutable class to recover contents from a CFDI.
 * This task is a kind of difficult since a CFDI can contain
 * several namespaces and include different rules than the need by SAT.
 *
 * The only mandatory namespaces is:
 * http://www.sat.gob.mx/cfd/3 for CFDI v3.2 and v3.3
 *
 * By default it makes also this namespace as mandatory but can be ommited by constructor:
 * http://www.sat.gob.mx/TimbreFiscalDigital for TimbreFiscalDigital (Seal) versions 1.0 and 1.1
 *
 * The class do not perform validations, only very basic as:
 * - Content must be a XML string
 * - Content must implement mandatory namespaces
 * - Root node must be Comprobante
 * - Root node must contain an attribute version with the value 3.2 or 3.3
 * - The node Comprobante/Complemento/TimbreFiscalDigital must exists if set in the constructor
 *
 * Other validations like XSD can be made using SchemaValidator
 * To validate the logic of the content you can use PostValidations helpers
 *
 * @package CFDIReader
 */
class CFDIReader
{
    /** @var SimpleXMLElement */
    private $comprobante;

    /** @var string */
    private $source;

    /**
     * Return an array of the versions that the reader can process
     *
     * @return string[]
     */
    public static function allowedVersions(): array
    {
        return ['3.2', '3.3'];
    }

    /**
     * @see CFDIReader
     * @param string $content xml contents
     * @param bool $requireTimbre
     * @throws \InvalidArgumentException when the content is not a valid XML
     */
    public function __construct(string $content, bool $requireTimbre = true)
    {
        // create the SimpleXMLElement
        try {
            $xml = new SimpleXMLElement($content);
        } catch (\Exception $ex) {
            throw new \InvalidArgumentException(
                'The content provided to build the CFDIReader is not a valid XML',
                null,
                $ex
            );
        }
        // check the root node name
        if ('Comprobante' !== $xml->getName()) {
            throw new \InvalidArgumentException('The XML root node must be Comprobante');
        }
        $version = '';
        if (isset($xml['version'])) {
            $version = strval($xml['version']);
        } elseif (isset($xml['Version'])) {
            $version = strval($xml['Version']);
        }
        if (! in_array($version, $this->allowedVersions())) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The Comprobante version must be one of the following: %s.',
                    implode(', ', $this->allowedVersions())
                )
            );
        }
        // check it contains both mandatory namespaces
        $nss = array_values($xml->getNamespaces(true));
        $required = ['http://www.sat.gob.mx/cfd/3'];
        if ($requireTimbre) {
            $required[] = 'http://www.sat.gob.mx/TimbreFiscalDigital';
        }
        foreach ($required as $namespace) {
            if (! in_array($namespace, $nss)) {
                throw new \InvalidArgumentException('The content does not use the namespace ' . $namespace);
            }
        }
        // include a null element to also copy the elements without namespace
        array_push($nss, null);
        // populate the root element
        $dummy = new SimpleXMLElement('<dummy/>');
        $this->comprobante = $this->appendChild($xml, $dummy, $nss);
        // check that it contains the node comprobante/complemento/timbreFiscalDigital if required
        if ($requireTimbre && ! $this->hasTimbreFiscalDigital()) {
            throw new \InvalidArgumentException('Seal not found on Comprobante/Complemento/TimbreFiscalDigital');
        }
        $this->source = $content;
    }

    /**
     * Get the xml source that was used to create this object
     *
     * @return string
     */
    public function source(): string
    {
        return $this->source;
    }

    /**
     * Retrieve a new instance of a DOMDocument using source as the content.
     * It always creates and return a new instance
     *
     * @return DOMDocument
     */
    public function document(): DOMDocument
    {
        $document = new DOMDocument();
        $document->loadXML($this->source());
        return $document;
    }

    /**
     * Get a copy of the root element
     *
     * @return SimpleXMLElement
     */
    public function comprobante()
    {
        return clone $this->comprobante;
    }

    public function getVersion(): string
    {
        return (string) $this->comprobante['version'];
    }

    /**
     * Get the UUID from the document. If the node does not exists then return an empty string
     *
     * @return string
     */
    public function getUUID(): string
    {
        if (! $this->hasTimbreFiscalDigital()) {
            return '';
        }
        return (string) $this->comprobante->{'complemento'}->timbreFiscalDigital['UUID'];
    }

    /**
     * Return true if the node Comprobante/Complemento/TimbreFiscalDigital exists
     *
     * @return bool
     */
    public function hasTimbreFiscalDigital(): bool
    {
        return isset($this->comprobante->{'complemento'})
            && isset($this->comprobante->{'complemento'}->timbreFiscalDigital);
    }

    /**
     * Normalize a name to follow accesor rules (all is uppercase or first letter is lowercase)
     * - Version => version
     * - TimbreFiscalDigital => timbreFiscalDigital
     * - UUID => UUID
     *
     * @param string $name
     * @return string
     */
    private function normalizeName(string $name): string
    {
        return (strtoupper($name) === $name) ? $name : lcfirst($name);
    }

    /**
     * Utility function to create a child
     *
     * @param SimpleXMLElement $source
     * @param SimpleXMLElement $parent
     * @param array $nss
     * @return SimpleXMLElement
     */
    private function appendChild(SimpleXMLElement $source, SimpleXMLElement $parent, array $nss): SimpleXMLElement
    {
        $new = $parent->addChild($this->normalizeName($source->getName()), (string) $source);
        $this->populateNode($source, $new, $nss);
        return $new;
    }

    /**
     * Utility function to copy contents from one element to other without namespaces
     *
     * @param SimpleXMLElement $source
     * @param SimpleXMLElement $destination
     * @param array $nss
     * @return void
     */
    private function populateNode(SimpleXMLElement $source, SimpleXMLElement $destination, array $nss)
    {
        // populate attributes
        foreach ($nss as $ns) {
            foreach ($source->attributes($ns) as $attribute) {
                /* @var $attribute SimpleXMLElement */
                $destination->addAttribute($this->normalizeName($attribute->getName()), (string) $attribute);
            }
        }
        // populate children
        foreach ($nss as $ns) {
            foreach ($source->children($ns) as $child) {
                $this->appendChild($child, $destination, $nss);
            }
        }
    }
}

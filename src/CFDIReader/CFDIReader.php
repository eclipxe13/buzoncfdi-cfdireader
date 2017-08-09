<?php
namespace CFDIReader;

use SimpleXMLElement;
use CFDIReader\SchemaRequirement\SchemaRequirement33;
use CFDIReader\SchemaRequirement\SchemaRequirement32;
use CFDIReader\SchemaRequirement\SchemaRequirementInterface;

/**
 * CFDI Reader immutable class to recover contents from a CFDI.
 * This task is a kind of difficult since a CFDI can contain
 * several namespaces and include different rules than the need by SAT.
 *
 * The two mandatory namespaces are:
 * http://www.sat.gob.mx/cfd/3 for CFDI v3.2
 * http://www.sat.gob.mx/TimbreFiscalDigital for TimbreFiscalDigital (Seal)
 *
 * The class do not perform validations, only very basic as:
 * - Content must be a XML string
 * - Content must implement both mandatory namespaces
 * - Root node must be Comprobante
 * - Root node must contain an attribute version with the value 3.2
 * - The node Comprobante/Complemento/TimbreFiscalDigital must exists
 *
 * Other validations like XSD can be made using SchemaValidator
 * To validate the logic of the contect you can use PostValidations helpers
 *
 * @package CFDIReader
 */
class CFDIReader
{
    /** @var SimpleXMLElement */
    private $comprobante;

    /**
     * List of allowed schemas
     *
     * @var array|SchemaRequirement[]
     */
    private $allowedSchemas;

    /**
     * @param string $content xml contents
     * @throws \InvalidArgumentException when the content is not a valid XML
     */
    public function __construct($content, $allowedSchemas = null)
    {
        if (is_null($allowedSchemas)) {
            $this->allowDefaultSchemas();
        }

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
        $version = strval($xml['version']);
        if (! $version) {
            // SAT generated attribute
            $version = strval($xml['Version']);
        }
        $version = $this->validateVersions($version);

        // check it contains both mandatory namespaces
        $nss = array_values($xml->getNamespaces(true));
        $required = $version->getRequiredSchemas();
        foreach ($required as $namespace) {
            if (! in_array($namespace, $nss)) {
                throw new \InvalidArgumentException('The content does not use the namespace ' . $namespace);
            }
        }
        // include a null element to copy the elements without namespace
        array_push($nss, null);
        // populate the root element
        $dummy = new SimpleXMLElement('<dummy/>');
        $this->comprobante = $this->appendChild($xml, $dummy, $nss);
        // check that it contains the node comprobante/complemento/timbreFiscalDigital
        if (! isset($this->comprobante->complemento->timbreFiscalDigital)) {
            throw new \InvalidArgumentException('Seal not found on Comprobante/Complemento/TimbreFiscalDigital');
        }
    }

    /**
     * Get a copy of the root element
     * @return SimpleXMLElement
     */
    public function comprobante()
    {
        return clone $this->comprobante;
    }

    /**
     * Get the UUID from the document
     * @return string
     */
    public function getUUID()
    {
        return (string) $this->comprobante->complemento->timbreFiscalDigital['UUID'];
    }

    /**
     * Normalize a name to be accesible by
     * @param string $name
     * @return string
     */
    private function normalizeName($name)
    {
        return (strtoupper($name) === $name) ? $name : lcfirst($name);
    }

    /**
     * Utility function to create a child
     * @param SimpleXMLElement $source
     * @param SimpleXMLElement $parent
     * @param array $nss
     * @return SimpleXMLElement
     */
    private function appendChild(SimpleXMLElement $source, SimpleXMLElement $parent, array $nss)
    {
        $new = $parent->addChild($this->normalizeName($source->getName()), (string) $source);
        $this->populateNode($source, $new, $nss);
        return $new;
    }

    /**
     * Utility function to copy contents from one element to other without namespaces
     * @param SimpleXMLElement $source
     * @param SimpleXMLElement $destination
     * @param array $nss
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

    public function allowDefaultSchemas()
    {
        $this->allowedSchemas = [
            new SchemaRequirement33,
            new SchemaRequirement32,
        ];
    }

    /**
     * Finds out if the version is supported.
     * @param  strign $version The version to test (eg 3.3 or 3.2)
     * @return SchemaRequirementInterface
     * @throws \InvalidArgumentException If the version is not supported.
     */
    public function validateVersions($version)
    {
        foreach ($this->allowedSchemas as $req) {
            if ($req->getVersion() == $version) {
                return $req;
            }
        }

        throw new \InvalidArgumentException('This Comprobante version is not supported.');
    }
}

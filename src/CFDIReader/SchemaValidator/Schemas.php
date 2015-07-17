<?php

namespace CFDIReader\SchemaValidator;

/**
 * Collection of schemas, used by SchemaValidator
 * @access private
 * @package CFDIReader
 */
class Schemas implements \IteratorAggregate, \Countable
{

    private $schemas = [];

    /** @var \finfo **/
    private $finfo;

    /**
     * Return a the XML of a Xsd that includes all the namespaces
     * @param \CFDIReader\SchemaValidator\Locator $locator
     * @return type
     */
    public function getXsd(Locator $locator)
    {
        $lines = [];
        /* @var $schema Schema */
        foreach($this->schemas as $schema) {
            $file = $locator->get($schema->getLocation());
            if ($this->fileIsXsd($file)) {
                $lines[] = '<xs:import namespace="' . $schema->getNamespace() . '" schemaLocation="' . $file .'" />';
            }
        }
        return '<?xml version="1.0" encoding="utf-8"?>'."\n"
            .'<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified" attributeFormDefault="unqualified">'
            .implode('', $lines)
            .'</xs:schema>';
    }

    protected function fileIsXsd($filename)
    {
        $valids = [
            'text/xml' => null,
            'application/xml' => null,
        ];
        if (null === $this->finfo) {
            $this->finfo = new \finfo(FILEINFO_SYMLINK);
        }
        $detected = $this->finfo->file($filename, FILEINFO_MIME_TYPE);
        return array_key_exists($detected, $valids);
    }

    public function create($namespace, $location)
    {
        return $this->insert(new Schema($namespace, $location));
    }

    public function insert(Schema $schema)
    {
        $this->schemas[$schema->getNamespace()] = $schema;
        return $schema;
    }

    public function remove($namespace)
    {
        unset($this->schemas[$namespace]);
    }

    public function all()
    {
        return $this->schemas;
    }

    public function exists($namespace)
    {
        return array_key_exists($namespace, $this->schemas);
    }

    public function item($namespace)
    {
        if (!$this->exists($namespace)) {
            throw new \InvalidArgumentException("Namespace $namespace does not exists in the schemas");
        }
        return $this->schemas[$namespace];
    }

    public function count()
    {
        return count($this->schemas);
    }

    public function getIterator() {
        return new \ArrayIterator($this->schemas);
    }

}

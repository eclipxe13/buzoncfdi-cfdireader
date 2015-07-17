<?php

namespace CFDIReader\SchemaValidator;

/**
 * Schema item, used by SchemaValidator and Schemas
 * @access private
 * @package CFDIReader
 */
class Schema
{
    private $namespace;
    private $location;

    public function __construct($namespace, $location)
    {
        $this->namespace = (string) $namespace;
        $this->location = (string) $location;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getLocation()
    {
        return $this->location;
    }
}

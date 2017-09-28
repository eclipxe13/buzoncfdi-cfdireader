<?php
namespace CFDIReader\SchemasValidator;

use XmlResourceRetriever\XsdRetriever;
use XmlSchemaValidator\SchemaValidator;

/**
 * Class SchemasValidator.
 * This class validate any xml file with its schemas,
 * is more like a facade of XmlSchemaValidator\SchemaValidator
 *
 * Optionally, it uses XmlResourceRetriever\XsdRetriever to store a copy
 * of the schemas (xsd files) if provided
 *
 * @package CFDIReader\SchemasValidator
 */
class SchemasValidator
{
    /** @var XsdRetriever|null */
    private $retriever;

    public function __construct(XsdRetriever $retriever = null)
    {
        $this->retriever = $retriever;
    }

    public function hasRetriever(): bool
    {
        return ($this->retriever instanceof XsdRetriever);
    }

    /**
     * @return XsdRetriever
     */
    public function getRetriever(): XsdRetriever
    {
        // use this comparison instead of hasRetriever to satisfy phpstan
        if (! ($this->retriever instanceof XsdRetriever)) {
            throw new \LogicException('The retriever property has not been set');
        }
        return $this->retriever;
    }

    /**
     * Validate according to the current retriever.
     * If retriever is set then it will use validateWithRetriever,
     * otherwise it will use validateWithoutRetriever.
     *
     * @see validateWithRetriever
     * @see validateWithoutRetriever
     *
     * @param string $content
     * @throws \RuntimeException on validation errors
     */
    public function validate(string $content)
    {
        if ($this->hasRetriever()) {
            $this->validateWithRetriever($content);
        } else {
            $this->validateWithoutRetriever($content);
        }
    }

    /**
     * Validate changing the namespaces locations to local resources and downloading them
     * if they does not exists
     *
     * @param string $content
     * @throws \RuntimeException on validation errors
     */
    public function validateWithRetriever(string $content)
    {
        // obtain the retriever, throw its own exception if non set
        $retriever = $this->getRetriever();
        // create the schema validator object
        $validator = new SchemaValidator($content);
        // obtain the list of schemas
        $schemas = $validator->buildSchemas();
        // replace with the local path
        foreach ($schemas as $schema) {
            /** @var \XmlSchemaValidator\Schema $schema */
            $location = $schema->getLocation();
            $localPath = $retriever->buildPath($location);
            if (! file_exists($localPath)) {
                $retriever->retrieve($location);
            }
            $schemas->create($schema->getNamespace(), $localPath);
        }
        // validate using the modified schemas
        $validator->validateWithSchemas($schemas);
    }

    /**
     * Validate just using the SchemaValidator, it will not retrieve any resource
     * and perform all validations using internet
     *
     * @param string $content
     * @throws \RuntimeException on validation errors
     */
    public function validateWithoutRetriever($content)
    {
        // create the schema validator object
        $validator = new SchemaValidator($content);
        if (! $validator->validate()) {
            throw new \RuntimeException('XSD error found: ' . $validator->getLastError());
        }
    }
}

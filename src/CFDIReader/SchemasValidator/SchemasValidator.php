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
    /** @var XsdRetriever */
    private $retriever;

    /** @var bool */
    private $forcedDownloads;

    public function __construct(XsdRetriever $retriever = null, bool $forcedDownloads = false)
    {
        $this->retriever = $retriever;
        $this->forcedDownloads = $forcedDownloads;
    }

    public function isForcedDownloads(): bool
    {
        return $this->forcedDownloads;
    }

    /**
     * Set when it should instruct the retriever to download the files
     * even when they already exists.
     * @param bool $forcedDownloads
     */
    public function setForcedDownloads(bool $forcedDownloads)
    {
        $this->forcedDownloads = $forcedDownloads;
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
        if (! $this->hasRetriever()) {
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
            $this->validateWithRetriever($content, $this->isForcedDownloads());
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
    public function validateWithRetriever(string $content, bool $forceDownload = false)
    {
        if (! $this->hasRetriever()) {
            throw new \LogicException('There are no retriever in the object');
        }
        // create the schema validator object
        $validator = new SchemaValidator($content);
        // obtain the list of schemas
        $schemas = $validator->buildSchemas();
        // replace with the local path
        foreach ($schemas as $schema) {
            $location = $schema->getLocation();
            $localPath = $this->retriever->buildPath($location);
            if ($forceDownload || ! file_exists($localPath)) {
                $this->retriever->retrieve($location);
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

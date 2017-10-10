<?php
namespace CFDIReader;

use CFDIReader\PostValidations\PostValidator;
use CFDIReader\PostValidations\Validators;
use CFDIReader\PostValidations\Validators\Certificado as CertificadoValidator;
use CFDIReader\SchemasValidator\SchemasValidator;
use CfdiUtils\CadenaOrigen;
use XmlResourceRetriever\Downloader\DownloaderInterface;
use XmlResourceRetriever\XsdRetriever;
use XmlResourceRetriever\XsltRetriever;

/**
 * Description of CFDIFactory
 *
 * @package CFDIReader
 */
class CFDIFactory
{
    /** @var string */
    private $localResourcesPath;

    /**
     * CFDIFactory constructor.
     *
     * @see setLocalResourcesPath
     * @param string|null $localResourcesPath
     */
    public function __construct(string $localResourcesPath = null)
    {
        $this->setLocalResourcesPath($localResourcesPath);
    }

    /**
     * Return an object with default validators included in the PostValidator object
     *
     * @return PostValidator
     */
    public function newPostValidator(): PostValidator
    {
        $postvalidator = new PostValidator();
        $postvalidator->validators->append(new Validators\TFDVersions());
        $postvalidator->validators->append(new Validators\Impuestos());
        $postvalidator->validators->append(new Validators\Fechas());
        $postvalidator->validators->append(new Validators\Conceptos());
        $postvalidator->validators->append(new Validators\Totales());
        $postvalidator->validators->append($this->newCertificadoValidator());
        return $postvalidator;
    }

    public function getLocalResourcesPath(): string
    {
        return $this->localResourcesPath;
    }

    /**
     * Set the local resources path to be used when created the XsdRetriever
     * If is null then it will take the library installation path + /resources
     * If is an empty string then no local resources will be used
     * If is a non-empty string it will use it like the path to store the resources
     *
     * @param string|null $localResourcesPath
     */
    public function setLocalResourcesPath(string $localResourcesPath = null)
    {
        if (null === $localResourcesPath) {
            $localResourcesPath = $this->getDefaultLocalResourcesPath();
        }
        $this->localResourcesPath = $localResourcesPath;
    }

    public function getDefaultLocalResourcesPath(): string
    {
        return dirname(__DIR__, 2) . '/resources';
    }

    /**
     * Return a new instance of an XsdRetriever depending on the
     * property localResourcesPath.
     *
     * @param DownloaderInterface|null $downloader
     * @return null|XsdRetriever
     */
    public function newRetriever(DownloaderInterface $downloader = null)
    {
        $localResourcesPath = $this->getLocalResourcesPath();
        if ('' === $localResourcesPath) {
            return null;
        }
        return new XsdRetriever($localResourcesPath, $downloader);
    }

    public function newSchemasValidator()
    {
        $retriever = $this->newRetriever();
        return new SchemasValidator($retriever);
    }

    /**
     * Return a new instance of an XsltRetriever depending on the
     * property localResourcesPath.
     *
     * @param DownloaderInterface|null $downloader
     * @return null|XsltRetriever
     */
    public function newXsltRetriever(DownloaderInterface $downloader = null)
    {
        $localResourcesPath = $this->getLocalResourcesPath();
        if ('' === $localResourcesPath) {
            return null;
        }
        return new XsltRetriever($localResourcesPath, $downloader);
    }

    /**
     * Return a new instance of a CadenaOrigen object with the XsltLocations
     * changed according to the retriever created at newXsltRetriever
     *
     * @return CadenaOrigen
     */
    public function newCadenaOrigen(): CadenaOrigen
    {
        $cadenaOrigenBuilder = new CadenaOrigen();
        $retriever = $this->newXsltRetriever();
        if (null === $retriever) {
            return $cadenaOrigenBuilder;
        }
        foreach ($cadenaOrigenBuilder->getXsltLocations() as $version => $remote) {
            $location = $retriever->buildPath($remote);
            if (! file_exists($location)) {
                $retriever->retrieve($remote);
            }
            $cadenaOrigenBuilder->setXsltLocation($version, $location);
        }
        return $cadenaOrigenBuilder;
    }

    /**
     * Return a CertificadoValidator with the CadenaOrigen property set
     * to the value of the newCadenaOrigen method
     *
     * @return CertificadoValidator
     */
    public function newCertificadoValidator(): CertificadoValidator
    {
        $validator = new CertificadoValidator();
        $validator->setCadenaOrigen($this->newCadenaOrigen());
        return $validator;
    }

    /**
     * Create a CFDI Reader, it has to be valid otherwise a exception will be thrown
     * @param string $content
     * @param array $errors
     * @param array $warnings
     * @param bool $requireTimbre
     * @return CFDIReader
     */
    public function newCFDIReader(
        string $content,
        array &$errors = [],
        array &$warnings = [],
        bool $requireTimbre = true
    ): CFDIReader {
        // before creation
        $schemaValidator = $this->newSchemasValidator();
        $schemaValidator->validate($content);

        // creation
        $cfdireader = new CFDIReader($content, $requireTimbre);

        // after creation
        $postValidator = $this->newPostValidator();
        $postValidator->validate($cfdireader);
        $errors = $postValidator->issues->messages(PostValidations\IssuesTypes::ERROR)->all();
        $warnings = $postValidator->issues->messages(PostValidations\IssuesTypes::WARNING)->all();
        return $cfdireader;
    }
}

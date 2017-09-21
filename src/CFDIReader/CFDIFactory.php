<?php
namespace CFDIReader;

use CFDIReader\PostValidations\PostValidator;
use CFDIReader\PostValidations\Validators;
use XmlSchemaValidator\SchemaValidator;

/**
 * Description of CFDIFactory
 *
 * @package CFDIReader
 */
class CFDIFactory
{
    /**
     * Build a new SchemaValidator object with default options for CFDI validations.
     * @param string $content
     * @return SchemaValidator
     */
    public function newSchemaValidator(string $content): SchemaValidator
    {
        return new SchemaValidator($content);
    }

    /**
     * @return PostValidator
     */
    public function newPostValidator(): PostValidator
    {
        $postvalidator = new PostValidator();
        $postvalidator->validators->append(new Validators\Impuestos());
        $postvalidator->validators->append(new Validators\Fechas());
        $postvalidator->validators->append(new Validators\Conceptos());
        $postvalidator->validators->append(new Validators\Totales());
        return $postvalidator;
    }

    /**
     * Create a CFDI Reader, it has to be valid otherwise a exception will be thrown
     * @param string $content
     * @param array $errors
     * @param array $warnings
     * @return CFDIReader
     */
    public function newCFDIReader(string $content, array &$errors = [], array &$warnings = []): CFDIReader
    {
        // before creation SchemaValidator
        $schemaValidator = $this->newSchemaValidator($content);
        if (! $schemaValidator->validate()) {
            throw new \RuntimeException(
                'The content is not a well formed or is not valid: ' . $schemaValidator->getLastError()
            );
        }

        // creation
        $cfdireader = new CFDIReader($content);

        // after creation
        $postValidator = $this->newPostValidator();
        $postValidator->validate($cfdireader);
        $errors = $postValidator->issues->messages(PostValidations\IssuesTypes::ERROR)->all();
        $warnings = $postValidator->issues->messages(PostValidations\IssuesTypes::WARNING)->all();
        return $cfdireader;
    }
}

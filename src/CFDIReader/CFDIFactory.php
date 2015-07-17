<?php

namespace CFDIReader;

/**
 * Description of CFDIFactory
 *
 * @package CFDIReader
 */
class CFDIFactory
{
    /**
     * Build a new SchemaValidator object with default options for CFDI validations.
     * @param \CFDIReader\SchemaValidator\Locator $locator if not provided use factory method to build it with default parameters
     * @return \CFDIReader\SchemaValidator\SchemaValidator
     */
    public function newSchemaValidator(SchemaValidator\Locator $locator = null)
    {
        if (null === $locator) {
            $locator = $this->newLocator();
        }
        $schemavalidator = new SchemaValidator\SchemaValidator($locator);
        return $schemavalidator;
    }

    /**
     * Build a new Locator object with default options for CFDI validations.
     * Sets allowed mimes to Xsd and register cfdv32.xsd and TimbreFiscalDigital.xsd from commonxsd/
     * @param bool $registerCommonXsd try to register files located on commonxsd/
     * @param string $repository location of cached files
     * @param integer $timeout download timeout
     * @param integer $expire expiration
     * @return \CFDIReader\SchemaValidator\Locator
     */
    public function newLocator($registerCommonXsd = true, $repository = '', $timeout = 20, $expire = 0)
    {
        $locator = new SchemaValidator\Locator($repository, $timeout, $expire);
        $locator->mimeAllow('application/xml');
        $locator->mimeAllow('text/xml');
        if ($registerCommonXsd) {
            $xsd = [
                'cfdv32.xsd' => 'http://www.sat.gob.mx/cfd/3',
                'TimbreFiscalDigital.xsd' => 'http://www.sat.gob.mx/TimbreFiscalDigital',
            ];
            if (false != $basepath = realpath(__DIR__ . "/../../commonxsd")) {
                foreach ($xsd as $file => $url) {
                    $locator->register($url, $basepath.'/'.$file);
                }
            }
        }
        return $locator;
    }

    public function newPostValidator()
    {
        $postvalidator = new \CFDIReader\PostValidations\PostValidator();
        $postvalidator->validators->append(new \CFDIReader\PostValidations\Validators\Impuestos());
        $postvalidator->validators->append(new \CFDIReader\PostValidations\Validators\Fechas());
        $postvalidator->validators->append(new \CFDIReader\PostValidations\Validators\Conceptos());
        $postvalidator->validators->append(new \CFDIReader\PostValidations\Validators\Totales());
        return $postvalidator;
    }

    public function newCFDIReader($content)
    {
        // before creation SchemaValidator
        $schemaValidator = $this->newSchemaValidator();
        if (! $schemaValidator->validate($content)) {
            throw new \RuntimeException('The content is not a well formed or is not valid: ' . $schemaValidator->getError());
        }

        // creation
        $cfdireader = new CFDIReader($content);

        // after creation
        $postValidator = $this->newPostValidator();
        if (! $postValidator->validate($cfdireader)) {
            throw new \RuntimeException('The content of the CFDI is not logic: ' . $postValidator->getError());
        }

        return $cfdireader;
    }


}

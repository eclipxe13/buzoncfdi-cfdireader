<?php
namespace CFDIReader\SchemaRequirement;

/**
 * Requirements for schema v. 3.3
 */
class SchemaRequirement33 implements SchemaRequirementInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRequiredSchemas()
    {
        return [
            'http://www.sat.gob.mx/cfd/33',
            'http://www.sat.gob.mx/TimbreFiscalDigital',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '3.3';
    }
}

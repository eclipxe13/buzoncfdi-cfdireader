<?php
namespace CFDIReader\SchemaRequirement;

/**
 * Requirements for schema v. 3.2
 */
class SchemaRequirement32 implements SchemaRequirementInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRequiredSchemas()
    {
        return [
            'http://www.sat.gob.mx/cfd/3',
            'http://www.sat.gob.mx/TimbreFiscalDigital',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return '3.2';
    }
}

<?php
namespace CFDIReader\SchemaRequirement;

/**
 * Schema requirement interface.
 *
 * A SquemaRequirementInterface validates a CFDI version by its headers
 * (schema and Comprobante[version]).
 */
interface SchemaRequirementInterface
{
    /**
     * List the required schemas the XML needs to have.
     * @return array|string[]
     */
    public function getRequiredSchemas();

    /**
     * Retrieves the version.
     * @return string
     */
    public function getVersion();
}

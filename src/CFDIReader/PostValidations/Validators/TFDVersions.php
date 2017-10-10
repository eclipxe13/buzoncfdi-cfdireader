<?php
namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;

class TFDVersions extends AbstractValidator
{
    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        if (! $cfdi->hasTimbreFiscalDigital()) {
            return;
        }
        $this->setup($cfdi, $issues);

        $cfdiVersion = $cfdi->getVersion();
        $tfdVersion = $cfdi->attribute('complemento', 'timbreFiscalDigital', 'version');
        $message = 'El CFDI %s debe contener un timbre fiscal digital versión %s pero se encontró la versión "%s"';
        if ('3.2' === $cfdiVersion && $tfdVersion !== '1.0') {
            $this->errors->add(sprintf($message, $cfdiVersion, '1.0', $tfdVersion));
        }
        if ('3.3' === $cfdiVersion && $tfdVersion !== '1.1') {
            $this->errors->add(sprintf($message, $cfdiVersion, '1.1', $tfdVersion));
        }
    }
}

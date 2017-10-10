<?php
namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;

class Fechas extends AbstractValidator
{
    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);
        // do the validation process
        $document = strtotime($cfdi->attribute('fecha'));
        if ($document > $this->getCurrentDate()) {
            $this->errors->add('La fecha del documento es mayor a la fecha actual');
        }
        if ($cfdi->hasTimbreFiscalDigital()) {
            $timbrado = $cfdi->attribute('complemento', 'timbreFiscalDigital', 'fechaTimbrado');
            if ('' === $timbrado) {
                $this->errors->add('Existe un timbre pero no tiene fecha de timbrado');
            } else {
                $timbrado = strtotime($timbrado);
                if ($document > $timbrado) {
                    $this->errors->add('La fecha del documento es mayor a la fecha del timbrado');
                }
                if ($timbrado - $document > 259200) {
                    $this->errors->add('La fecha fecha del timbrado excedió las 72 horas de la fecha del documento');
                }
            }
        }
    }

    /**
     * @return int
     */
    protected function getCurrentDate()
    {
        return time();
    }
}

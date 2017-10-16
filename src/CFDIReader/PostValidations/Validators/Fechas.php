<?php
namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;

class Fechas extends AbstractValidator
{
    /**
     * Store the tolerance in seconds when comparing dates. Default to 1 minute.
     * This prevents the problem of time sync between the emitter and the PAC.
     *
     * @var int
     */
    private $delta = 60;

    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);
        // do the validation process
        $document = strtotime($cfdi->attribute('fecha'));
        if ($document > $this->getCurrentDate() + $this->getDelta()) {
            $this->errors->add('La fecha del documento es mayor a la fecha actual');
        }
        if ($cfdi->hasTimbreFiscalDigital()) {
            $timbrado = $cfdi->attribute('complemento', 'timbreFiscalDigital', 'fechaTimbrado');
            if ('' === $timbrado) {
                $this->errors->add('Existe un timbre pero no tiene fecha de timbrado');
            } else {
                $timbrado = strtotime($timbrado);
                if ($document > $timbrado + $this->getDelta()) {
                    $this->errors->add('La fecha del documento es mayor a la fecha del timbrado');
                }
                if ($timbrado > $document + 259200 + $this->getDelta()) {
                    $this->errors->add('La fecha fecha del timbrado excedió las 72 horas de la fecha del documento');
                }
            }
        }
    }

    public function getDelta(): int
    {
        return $this->delta;
    }

    public function setDelta(int $delta)
    {
        $this->delta = $delta;
    }

    /**
     * @return int
     */
    protected function getCurrentDate()
    {
        return time();
    }
}

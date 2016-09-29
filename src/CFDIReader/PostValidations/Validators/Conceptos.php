<?php

namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;

class Conceptos extends AbstractValidator
{
    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);
        // do the validation process
        foreach ($this->comprobante->conceptos->concepto as $concepto) {
            $extended = $this->value($concepto["valorUnitario"]) * $this->value($concepto["cantidad"]);
            $importe = $this->value($concepto["importe"]);
            if (! $this->compare($extended, $importe)) {
                $this->warnings->add(
                    'El importe del concepto '
                    . $concepto["descripcion"]
                    . ' no coincide con el producto del valor unitario y el total'
                );
            }
        }
    }
}

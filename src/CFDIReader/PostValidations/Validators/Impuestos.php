<?php

namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;

class Impuestos extends AbstractValidator
{
    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);
        // do the validation process
        $retenidos = $this->value($this->comprobante->impuestos["totalImpuestosRetenidos"]);
        $retenciones = $this->sumNodes($this->comprobante->impuestos->retenciones->retencion, "importe");
        if (! $this->compare($retenciones, $retenidos)) {
            $this->warnings->add('El total de impuestos retenidos difiere de la suma de los nodos de las retenciones');
        }
        $traslados = $this->value($this->comprobante->impuestos["totalImpuestosRetenidos"]);
        $trasladados = $this->sumNodes($this->comprobante->impuestos->traslados->traslado, "importe");
        if (! $this->compare($traslados, $trasladados)) {
            $this->warnings->add('El total de impuestos trasladados difiere de la suma de los nodos de las retenciones');
        }

    }
}

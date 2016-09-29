<?php

namespace CFDIReader\PostValidations\Validators;

use CFDIReader\CFDIReader;
use CFDIReader\PostValidations\Issues;

class Totales extends AbstractValidator
{
    public function validate(CFDIReader $cfdi, Issues $issues)
    {
        // setup the AbstractValidator Helper class
        $this->setup($cfdi, $issues);
        // do the validation process
        $importes = $this->sumNodes($this->comprobante->conceptos->concepto, "importe");
        $subtotal = $this->value($this->comprobante["subTotal"]);
        if (! $this->compare($importes, $subtotal)) {
            $this->warnings->add('El subtotal no coincide con la suma de los importes');
        }
        $retenidos = $this->value($this->comprobante->impuestos["totalImpuestosRetenidos"]);
        $traslados = $this->value($this->comprobante->impuestos["totalImpuestosTrasladados"]);
        $localesRetenidos = $this->value($this->comprobante->complemento->impuestosLocales["totaldeRetenciones"]);
        $localesTraslados = $this->value($this->comprobante->complemento->impuestosLocales["totaldeTraslados"]);
        $descuentos = $this->value($this->comprobante["descuento"]);
        $total = $this->value($this->comprobante["total"]);
        $calculated = $subtotal - $descuentos + $traslados - $retenidos + $localesTraslados - $localesRetenidos;
        if (! $this->compare($calculated, $total)) {
            $this->warnings->add(
                'El total no coincide con la suma del subtotal'
                . ' menos el descuento más los traslados menos las retenciones'
            );
        }
    }
}

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

        // obtain the sum of importes
        $nodesConcepto = $cfdi->node('conceptos', 'concepto');
        $importes = $this->sumNodes($nodesConcepto, 'importe');

        // obtain the subtotal
        $subtotal = $this->value($cfdi->attribute('subTotal'));

        // check subtotal versus importes
        if (! $this->compare($importes, $subtotal)) {
            $this->warnings->add('El subtotal no coincide con la suma de los importes');
        }

        // obtain retenidos and traslados
        $retenidos = $this->value($cfdi->attribute('impuestos', 'totalImpuestosRetenidos'));
        $traslados = $this->value($cfdi->attribute('impuestos', 'totalImpuestosTrasladados'));
        $locRetenidos = $this->value($cfdi->attribute('complemento', 'impuestosLocales', 'totaldeRetenciones'));
        $locTraslados = $this->value($cfdi->attribute('complemento', 'impuestosLocales', 'totalImpuestosRetenidos'));

        // obtain descuentos
        $descuentos = $this->value($cfdi->attribute('descuento'));

        // calculate the amount of the total
        $calculated = $subtotal - $descuentos + $traslados - $retenidos + $locTraslados - $locRetenidos;

        // obtain the total
        $total = $this->value($cfdi->attribute('total'));

        // compare total versus calculated
        if (! $this->compare($calculated, $total)) {
            $this->warnings->add(
                'El total no coincide con la suma del subtotal'
                . ' menos el descuento más los traslados menos las retenciones'
            );
        }
    }
}

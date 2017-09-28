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
        $nodesConcepto = $cfdi->node('conceptos', 'concepto');
        if (null === $nodesConcepto) {
            $this->warnings->add('El comprobante no contiene conceptos');
            return;
        }
        // only get here if conceptos/concepto is not null
        foreach ($nodesConcepto as $concepto) {
            $extended = $this->value($concepto['valorUnitario']) * $this->value($concepto['cantidad']);
            $importe = $this->value($concepto['importe']);
            if (! $this->compare($extended, $importe)) {
                $this->warnings->add(sprintf(
                    'El importe del concepto %s no coincide con el producto del valor unitario y el total',
                    $concepto['descripcion']
                ));
            }
        }
    }
}

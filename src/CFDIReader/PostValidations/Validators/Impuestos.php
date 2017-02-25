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
        $retenidos = $this->value($this->comprobante->impuestos['totalImpuestosRetenidos']);
        $retenciones = $this->sumNodes($this->comprobante->impuestos->retenciones->retencion, 'importe');
        if (! $this->compare($retenciones, $retenidos)) {
            $this->warnings->add('El total de impuestos retenidos difiere de la suma de los nodos de las retenciones');
        }
        $traslados = $this->value($this->comprobante->impuestos['totalImpuestosTrasladados']);
        $trasladados = $this->sumNodes($this->comprobante->impuestos->traslados->traslado, 'importe');
        if (! $this->compare($traslados, $trasladados)) {
            $this->warnings->add('El total de impuestos trasladados difiere de la suma de los nodos de los traslados');
        }
        if (isset($this->comprobante->complemento->impuestosLocales)) {
            $localesRetenidos = $this->value($this->comprobante->complemento->impuestosLocales['totaldeRetenciones']);
            $localesRetenciones = $this->sumNodes(
                $this->comprobante->complemento->impuestosLocales->retencionesLocales,
                'importe'
            );
            if (! $this->compare($localesRetenciones, $localesRetenidos)) {
                $this->warnings->add(
                    'El total de impuestos locales retenidos difiere de la suma de los nodos de las retenciones'
                );
            }
            $localesTraslados = $this->value($this->comprobante->complemento->impuestosLocales['totaldeTraslados']);
            $localesTrasladados = $this->sumNodes(
                $this->comprobante->complemento->impuestosLocales->trasladosLocales,
                'importe'
            );
            if (! $this->compare($localesTrasladados, $localesTraslados)) {
                $this->warnings->add(
                    'El total de impuestos locales trasladados difiere de la suma de los nodos de los traslados'
                );
            }
        }
    }
}

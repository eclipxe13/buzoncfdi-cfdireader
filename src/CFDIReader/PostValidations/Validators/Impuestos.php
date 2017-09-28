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

        // validate impuestos@totalImpuestosRetenidos vs sum(impuestos/retenciones/retencion@importe)
        $retenidos = $this->value($cfdi->attribute('impuestos', 'totalImpuestosRetenidos'));
        $retenciones = $this->sumNodes($cfdi->node('impuestos', 'retenciones', 'retencion'), 'importe');
        if (! $this->compare($retenciones, $retenidos)) {
            $this->warnings->add('El total de impuestos retenidos difiere de la suma de los nodos de las retenciones');
        }

        // validate impuestos@totalImpuestosTrasladados vs sum(impuestos/traslados/traslado@importe)
        $traslados = $this->value($cfdi->attribute('impuestos', 'totalImpuestosTrasladados'));
        $trasladados = $this->sumNodes($cfdi->node('impuestos', 'traslados', 'traslado'), 'importe');
        if (! $this->compare($traslados, $trasladados)) {
            $this->warnings->add('El total de impuestos trasladados difiere de la suma de los nodos de los traslados');
        }

        // validate "impuestos locales"
        $this->validateImpuestosLocales($cfdi);
    }

    private function validateImpuestosLocales(CFDIReader $cfdi)
    {
        $nodeImpuestosLocales = $cfdi->node('complemento', 'impuestosLocales');
        if (null === $nodeImpuestosLocales) {
            // nothing to do
            return;
        }

        // retenciones
        $nodesRetenciones = $cfdi->node('complemento', 'impuestosLocales', 'retencionesLocales');
        $retenciones = $this->sumNodes($nodesRetenciones, 'importe');
        $retenidos = $this->value($nodeImpuestosLocales['totaldeRetenciones']);
        if (! $this->compare($retenciones, $retenidos)) {
            $this->warnings->add(
                'El total de impuestos locales retenidos difiere de la suma de los nodos de las retenciones'
            );
        }

        // traslados
        $nodesTraslados = $cfdi->node('complemento', 'impuestosLocales', 'trasladosLocales');
        $trasladados = $this->sumNodes($nodesTraslados, 'importe');
        $traslados = $this->value($nodeImpuestosLocales['totaldeTraslados']);
        if (! $this->compare($trasladados, $traslados)) {
            $this->warnings->add(
                'El total de impuestos locales trasladados difiere de la suma de los nodos de los traslados'
            );
        }
    }
}

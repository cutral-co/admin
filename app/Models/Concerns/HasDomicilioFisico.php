<?php

namespace App\Models\Concerns;

use App\Models\Common\BarrioMunicipio;
use App\Models\Common\Provincia;

trait HasDomicilioFisico
{
    public function domicilioFisico(): array
    {
        $barrio = $this->resolveBarrioForDomicilio();
        $provincia = $this->resolveProvinciaForDomicilio($barrio);

        return [
            'calle' => $this->calle,
            'altura' => $this->altura,
            'manzana' => $this->manzana,
            'lote' => $this->lote,
            'piso' => $this->piso,
            'depto' => $this->depto,
            'barrio' => $barrio?->name ?? $this->otro_barrio,
            'municipio' => $this->municipio,
            'provincia' => $provincia?->name,
        ];
    }

    public function stringDatosDomicilio(): string
    {
        $domicilio = $this->domicilioFisico();

        $parts = array_filter([
            $domicilio['calle'],
            $domicilio['altura'] ? 'Nro. ' . $domicilio['altura'] : null,
            $domicilio['manzana'] ? 'Mz. ' . $domicilio['manzana'] : null,
            $domicilio['lote'] ? 'Lote ' . $domicilio['lote'] : null,
            $domicilio['piso'] ? 'Piso ' . $domicilio['piso'] : null,
            $domicilio['depto'] ? 'Depto ' . $domicilio['depto'] : null,
            $domicilio['barrio'],
            $domicilio['municipio'],
            $domicilio['provincia'],
        ]);

        return implode(', ', $parts);
    }

    private function resolveBarrioForDomicilio(): ?BarrioMunicipio
    {
        if (!$this->barrio_id) {
            return null;
        }

        if (method_exists($this, 'barrio')) {
            return $this->barrio;
        }

        if (method_exists($this, 'barrio_municipal')) {
            return $this->barrio_municipal;
        }

        return null;
    }

    private function resolveProvinciaForDomicilio(?BarrioMunicipio $barrio): ?Provincia
    {
        if ($barrio?->provincia) {
            return $barrio->provincia;
        }

        if (method_exists($this, 'provincia')) {
            return $this->provincia;
        }

        return null;
    }
}

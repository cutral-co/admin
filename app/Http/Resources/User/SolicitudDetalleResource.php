<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudDetalleResource extends JsonResource
{
    public function toArray($request)
    {
        $array = parent::toArray($request);

        unset(
            $array['barrio_id'],
            $array['otro_barrio'],
            $array['provincia_id']
        );

        return [
            ...$array,
            ...$this->resource->domicilioFisico(),
            'estado' => $this->estado?->value,
        ];
    }
}

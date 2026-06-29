<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudResource extends JsonResource
{
    public function toArray($request)
    {
        $array = parent::toArray($request);

        $array['barrio'] = null;
        if ($this->barrio) {
            $array['barrio'] = $this->barrio->name;
        } else {
            $array['barrio'] = $this->otro_barrio;
        }
        $array['estado'] = $this->estado->value;

        return $array;
    }
}

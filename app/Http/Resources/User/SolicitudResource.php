<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $array = parent::toArray($request);

        $array['barrio'] = null;
        if ($this->bar) {
            $array['barrio'] = $this->bar->name;
        } else {
            $array['barrio'] = $this->barrio;
        }
        $array['estado'] = $this->estado->name;

        return $array;
    }
}

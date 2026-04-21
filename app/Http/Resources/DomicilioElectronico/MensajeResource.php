<?php

namespace App\Http\Resources\DomicilioElectronico;

use App\Models\DomicilioElectronico\NotificacionArchivo;
use Illuminate\Http\Resources\Json\JsonResource;

class MensajeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $this->tipo_destinatario;
        $this->notificacion->origin;
        $this->notificacion->archivos;
        /* $this->domicilio; */
        $array = parent::toArray($request);

        $array["notificacion"]["data"] = json_decode($array["notificacion"]["data"]);

        /* foreach ($array["notificacion"]["archivos"] as $key => $value) {
            $notificacion_archivo = NotificacionArchivo::find($value['id']);
            $array["notificacion"]["archivos"][$key]['file'] = getFileStorage($notificacion_archivo->path);
        } */
        return $array;
    }
}

<?php

namespace App\Http\Resources\DomicilioElectronico;

use App\Models\DomicilioElectronico\NotificacionArchivo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificacionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {

        $this->tipo_destinatario;
        $this->notificacion->origin;
        $this->notificacion->archivos;

        $array = parent::toArray($request);

        $array["notificacion"]["data"] = json_decode($array["notificacion"]["data"]);

        foreach ($array["notificacion"]["archivos"] as $key => $value) {
            $notificacion_archivo = NotificacionArchivo::find($value['id']);
            $fileData = getFileStorage($notificacion_archivo->path);
            $array["notificacion"]["archivos"][$key]['file'] = $fileData['file_name'];
        }
        return $array;
    }
}

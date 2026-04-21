<?php

namespace App\Http\Controllers\DomicilioElectronico;

use App\Http\Controllers\Controller;
use App\Models\DomicilioElectronico\DomicilioNotificacion;
use App\Models\DomicilioElectronico\Notificacion;
use App\Models\DomicilioElectronico\NotificacionArchivo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificacionController extends Controller
{
    use DomicilioElectronicoTrait;

    public function generateNotificacionArchivo()
    {
        $file_path = 'domicilio_electronico/terminos_condiciones_domicilio_electronico_06_2024.pdf';
        // $fileContent = Storage::disk('serverdata')->get($filePath);

        // Buscar las notificaciones con el título especificado

        $domicilioNotificaciones = DomicilioNotificacion::whereHas('notificacion', function ($query) {
            $query->where('title', 'Te adheriste al Domicilio Electrónico');
        })->get();

        foreach ($domicilioNotificaciones as $domicilioNotificacion) {

            NotificacionArchivo::create([
                'name' => "terminos_condiciones_domicilio_electronico_06_2024",
                'path' => $file_path,
                'type' => 'pdf',
                'notificacion_id' => $domicilioNotificacion->notificacion_id
            ]);
        }

        return response()->json(['message' => 'Archivos adjuntados correctamente'], 200);
    }
}

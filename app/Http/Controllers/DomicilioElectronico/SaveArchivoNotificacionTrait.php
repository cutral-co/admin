<?php

namespace App\Http\Controllers\DomicilioElectronico;

use App\Models\DomicilioElectronico\Log;
use App\Models\DomicilioElectronico\NotificacionArchivo;
use Exception;
use Illuminate\Support\Facades\Storage;

trait SaveArchivoNotificacionTrait
{
    private function saveArchivos($files, $domicilioNotificacion,  $type = null, $name = null, $filesExistentes = [])
    {
        $file_paths = $filesExistentes;
        $dateSuffix = date('Ymd');

        if ($files) {
            foreach ($files as $index => $file) {
                if ($type) {
                    $fileName = md5($domicilioNotificacion->notificacion_id) . '_' . $dateSuffix . '.' . $type;
                    $filePath = 'domicilio_electronico/' . $fileName;

                    // Guardar el archivo en el disco especificado
                    $stored = Storage::disk('serverdata')->put($filePath, $file);

                    if ($stored) {
                        $file_paths[] = $filePath;
                    }
                } else {
                    $file_paths[] = Storage::disk('serverdata')->put('domicilio_electronico', $file);
                }
            }
        }

        foreach ($file_paths as $index => $file_path) {
            $nombreArchivo = $name ?? generarSlug($domicilioNotificacion->notificacion->title) . (count($file_paths) > 1 ? '_' . ($index + 1) : null);

            NotificacionArchivo::create([
                'name' => $nombreArchivo,
                'path' => $file_path,
                'notificacion_id' => $domicilioNotificacion->notificacion_id
            ]);
        }


        return $file_paths;
    }
}

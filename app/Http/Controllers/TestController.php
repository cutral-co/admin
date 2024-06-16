<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;

class TestController extends Controller
{
    public function file(Request $request)
    {
        /** Guardamos el archivo */
        $path = Storage::disk('serverdata')->put('solicitudes', $request->file('file'));

        /* Obtenemos el archivo */
        $file = Storage::disk('serverdata')->get($path);

        /* Obtenemos el parth del archivo para luego obtener toda la informacion del archivo */
        $fullPath = Storage::disk('serverdata')->path($path);
        $fileInfo = new File($fullPath);

        return sendResponse([
            'type' => $fileInfo->getExtension(),
            'file_name' => $fileInfo->getFilename(),
            'size' => $fileInfo->getSize(),

            /* Enviamos el archivo como base64 */
            'base64' => base64_encode($file),
        ]);
    }
}

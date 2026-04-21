<?php

namespace App\Http\Controllers\DomicilioElectronico;

use App\Models\DomicilioElectronico\{
    DomicilioNotificacion,
    Notificacion,
    NotificacionArchivo,
    TipoDestinatario,
    Log,
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Storage};


trait DomicilioElectronicoTrait
{    // Modificar el funcionamiento segun un solo parametro
    private function saveArchivos($files, $domicilio_notificacion,  $type = null, $name = null)
    {
        $file_paths = [];
        $dateSuffix = date('Ymd');
        if ($files) {
            foreach ($files as $index => $file) {
                if ($type) {
                    $fileName = md5($domicilio_notificacion->notificacion_id) . '_' . $dateSuffix . '.' . $type;
                    // Definir la ruta completa donde se guardará el archivo
                    // $filePath = 'domicilio_electronico/';
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

        $i = 1;

        foreach ($file_paths as $file_path) {

            // if (count($file_paths) > 1) {
            //     $name .= "_$i";
            // }
            NotificacionArchivo::create([
                'name' => $name ?? generarSlug($domicilio_notificacion->notificacion->title) . (count($file_paths) > 1 ? "_$i" : null),
                'path' => $file_path,

                'notificacion_id' => $domicilio_notificacion->notificacion_id
            ]);
            $i++;
        }

        return $file_paths;
    }
    private function validateOrResponse(Request $request)
    {
        $destinatarios = explode(',', $request->destinatarios);
        $representantes = explode(',', $request->representantes);

        if (count($destinatarios) !== count(array_unique($destinatarios))) {
            return sendResponse(null, 'Existen elementos repetidos dentro de destinatario', 401);
        }

        if (count($representantes) !== count(array_unique($representantes))) {
            return sendResponse(null, 'Existen elementos repetidos dentro de representantes', 401);
        }

        if (!empty(array_intersect($destinatarios, $representantes))) {
            return sendResponse(null, 'Existe interseccion entre destinatarios y representantes', 401);
        }

        /* Verificamos que cada uno de los elementos tenga 11 digitos */
        foreach ($destinatarios as $destinatario) {
            if (strlen($destinatario) !== 11 || !ctype_digit($destinatario)) {
                return sendResponse(null, 'Cada destinatario debe tener 11 dígitos numéricos', 401);
            }
        }

        foreach ($representantes as $representante) {
            if (strlen($representante) !== 11 || !ctype_digit($representante)) {
                return sendResponse(null, 'Cada representante debe tener 11 dígitos numéricos', 401);
            }
        }

        return ['destinatarios' => $destinatarios, 'representantes' => $representantes];
    }

    /**
     * Crea una notificación y la asocia con un domicilio electrónico y un destinatario.
     *
     * @param array $params  Los parámetros de la notificación, 'title', 'body', 'origin' y 'data'.
     * @param int   $domicilio_electronico_id  El ID del domicilio electrónico al que se enviará la notificación.
     * @param TipoDestinatario $tipo_destinatario  El tipo de destinatario de la notificación.
     * @param int  $user_id       El ID del usuario destinatario.
     * @param string $identificacion (Opcional) La identificación asociada con la notificación.
     *
     * @return DomicilioNotificacion El objeto DomicilioNotificacion recién creado.
     */
    private function createDomicilioNotificacion(
        $params,
        $domicilio_electronico_id,
        TipoDestinatario $tipo_destinatario,
        $identificacion = null
    ) {

        $notificacion = Notificacion::create([
            'origin_id' => $params['origin_id'],
            'title' => $params['title'],
            'body' => $params['body'],
            'hash' => md5(uniqid(rand(), true)),
            'block' => isset($params['block']) ? (bool) $params['block'] : false,
            'data' => isset($params['data']) ? json_encode($params['data']) : null
        ]);

        $domicilio_notificacion =  DomicilioNotificacion::create([
            'domicilio_id' =>  $domicilio_electronico_id,
            'notificacion_id' => $notificacion->id,
            'tipo_destinatario_id' => $tipo_destinatario->id,
            'fecha_recibido' => \Carbon\Carbon::now()->toDateTimeString(),
        ]);

        Log::create([
            'domicilio_id' =>  $domicilio_electronico_id,
            'notificacion_id' => $notificacion->id,
            'tipo_destinatario' => $tipo_destinatario->name,
            'message' => 'Se notifico correctamente al usuario',
            'attributes' => $identificacion ? json_encode(['identificacion' => $identificacion]) : null,
        ]);

        return $domicilio_notificacion;
    }
}

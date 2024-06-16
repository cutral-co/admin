<?php
use Symfony\Component\HttpFoundation\File\File;
use Illuminate\Support\Facades\Storage;

if (!function_exists('sendResponse')) {
    function sendResponse($data, $error = null, $status = 200, $params = null)
    {
        return response()->json([
            'data' => $data,
            'error' => $error,
            'params' => $params,
        ], $status);
    }
}

if (!function_exists('saveLog')) {
    function saveLog($message, $observation = null, $stack = null): object
    {
        try {
            $data = [
                'user_id' => auth()->user() ? auth()->user()->id : null,
                'message' => $message,
                'observation' => $observation,
                'stack' => $stack ? json_encode($stack) : null
            ];

            $log = new \App\Models\Log();
            $log->fill($data);
            $log->save();
            return $log;
        } catch (\Throwable $th) {
            return new stdClass();
        }
    }
}

if (!function_exists('is_email')) {
    function is_email($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('storage_file')) {
    function storage_file($file, string $folder = '/'): string
    {
        /** Guardamos el archivo */
        $path = Storage::disk('serverdata')->put($folder, $file);
        return $path;
    }
}

if (!function_exists('get_file')) {
    function get_file(string $path)
    {
        /* Obtenemos el parth del archivo para luego obtener toda la informacion del archivo */
        $fullPath = Storage::disk('serverdata')->path($path);
        $fileInfo = new File($fullPath);
        $type = $fileInfo->getExtension();
        return [
            'type' => $type,
            'file_name' => $fileInfo->getFilename(),
            'size' => $fileInfo->getSize(),

            /* Enviamos el archivo como base64 */
            'file' => 'data:application/' . $type . ';base64,' . base64_encode($fileInfo),
        ];
    }
}

if(!function_exists('log_send_response')){
    function log_send_response($log){
        if (!property_exists($log, 'attributes')) {
            $log->id = '1A';
            $log->message = 'Falló el log';
        }
        if (!env('APP_DEBUG')) {
            return sendResponse(null, ['general' => 'Ha ocurrido un error durante la consulta. Código ' . $log->id], 490);
        }
        return sendResponse(null, ['general' => $log->message], 490);
    }
}

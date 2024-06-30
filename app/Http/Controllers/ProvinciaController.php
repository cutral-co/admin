<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Provincia;
use Illuminate\Http\Request;

class ProvinciaController extends Controller
{
    /**
     * Para obtener todas las instancias de un modelo.
     */
    public function index()
    {
        try {
            $provincias = Provincia::all();
            return sendResponse($provincias);
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class().'::'. __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Para guardar una nueva instancia.
     */
    public function store(Request $request)
    {
        try {

        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class().'::'. __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Para mostrar una instancia en específico.
     */
    public function show($id)
    {
        try {

        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class().'::'. __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Para actualizar una instancia en específico.
     */
    public function update(Request $request, $id)
    {
        try {

        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class().'::'. __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Para eliminar una instancia.
     */
    public function destroy($id)
    {
        try {

        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class().'::'. __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }
}

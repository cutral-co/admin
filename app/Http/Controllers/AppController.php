<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AppController extends Controller
{
    /**
     * Para obtener todas las instancias de un modelo.
     */
    public function index()
    {
        try {
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
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
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
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
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
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
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
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
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }


    public static function filterForPermission(Collection $apps, $user)
    {
        $filteredApps = $apps->filter(function ($app) use ($user) {
            /** Nombre del permiso de ingreso */
            $can = "app.enter.$app->name";

            /** Validamos si la app requiere permiso y si el usuario lo tiene */
            return !(bool)$app->required_permission || $user->can($can);
        });

        return $filteredApps->values();
    }
}

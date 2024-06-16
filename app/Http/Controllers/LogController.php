<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class LogController extends Controller
{
    public function index()
    {
        $date = Carbon::today()->subDays(7);
        $logs = Log::where('created_at', '>=', $date)->get();

        return sendResponse($logs);
    }

    public function show($id)
    {
        $log = Log::find($id);
        if (!$log) {
            return sendResponse(null, 'No se encontro el log');
        }

        return sendResponse($log);
    }

    public function update(Request $request)
    {
        $log = Log::find($request->id);
        if (!$log) {
            return sendResponse(null, 'No se encontro el log');
        }
        try {
            DB::beginTransaction();
            $log->fecha_visto = Carbon::now();
            $log->save();
            DB::commit();

            return sendResponse($log);
        } catch (Throwable $th) {
            DB::rollBack();
            saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());

            return sendResponse(null, 'No se pudo actualizar el log');
        }
    }
}

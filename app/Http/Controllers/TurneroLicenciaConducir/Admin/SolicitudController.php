<?php

namespace App\Http\Controllers\TurneroLicenciaConducir\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TurneroLicenciaConducir\AssignTurnoRequest;
use App\Http\Requests\TurneroLicenciaConducir\UpdateSolicitudEstadoRequest;
use App\Models\TurneroLicenciaConducir\Solicitud;
use App\Services\TurneroLicenciaConducir\SolicitudService;
use Illuminate\Support\Carbon;

class SolicitudController extends Controller
{
    private const REQUIRED_PERMISSION = 'app.enter.adm-turnero-licencia-conducir';

    public function __construct(private readonly SolicitudService $solicitudService) {}

    public function index()
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            $solicitudes = Solicitud::query()
                ->orderBy('created_at', 'desc')
                ->get();

            return sendResponse($solicitudes);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function show(int $id)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            $solicitud = Solicitud::find($id);

            if (!$solicitud) {
                return sendResponse(null, 'No se encontró la solicitud', 404);
            }

            return sendResponse($solicitud);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function updateEstado(UpdateSolicitudEstadoRequest $request, int $id)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            $solicitud = Solicitud::find($id);

            if (!$solicitud) {
                return sendResponse(null, 'No se encontró la solicitud', 404);
            }

            $solicitud->estado = $request->validated()['estado'];
            $solicitud->save();

            return sendResponse($solicitud->fresh());
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function estados()
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        return sendResponse(Solicitud::estadoOptions());
    }

    public function assignTurno(AssignTurnoRequest $request, int $id)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            $solicitud = Solicitud::find($id);

            if (!$solicitud) {
                return sendResponse(null, 'No se encontró la solicitud', 404);
            }

            $solicitud = $this->solicitudService->assignTurno(
                $solicitud,
                Carbon::parse($request->validated()['fecha_turno']),
            );
            $this->solicitudService->sendTurnoAsignadoEmail($solicitud);

            return sendResponse($solicitud);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    private function authorizeAccess()
    {
        $user = auth()->user();

        if (!$user || !$user->can(self::REQUIRED_PERMISSION)) {
            return sendResponse(null, 'No tiene permisos para acceder al módulo', 403);
        }

        return null;
    }
}

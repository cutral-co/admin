<?php

namespace App\Http\Controllers\TurneroLicenciaConducir;

use App\Http\Controllers\Controller;
use App\Http\Requests\TurneroLicenciaConducir\LookupGestionTurnoRequest;
use App\Http\Requests\TurneroLicenciaConducir\RequestCambioTurnoRequest;
use App\Http\Requests\TurneroLicenciaConducir\StoreSolicitudRequest;
use App\Services\TurneroLicenciaConducir\SolicitudService;
use Illuminate\Support\Carbon;

class SolicitudController extends Controller
{
    public function __construct(private readonly SolicitudService $solicitudService) {}

    public function store(StoreSolicitudRequest $request)
    {
        try {
            $solicitud = $this->solicitudService->create($request->validated());
            $this->solicitudService->sendSolicitudRecibidaEmail($solicitud);

            return sendResponse($solicitud);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function gestionar(LookupGestionTurnoRequest $request)
    {
        try {
            $solicitud = $this->solicitudService->findByDniAndCodigo(
                $request->validated()['dni'],
                $request->validated()['codigo_verificacion'],
            );

            if (!$solicitud) {
                return sendResponse(null, 'No se encontró una solicitud con el DNI y código ingresados', 404);
            }

            return sendResponse($solicitud);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function solicitarCambio(RequestCambioTurnoRequest $request)
    {
        try {
            $validated = $request->validated();
            $solicitud = $this->solicitudService->findByDniAndCodigo(
                $validated['dni'],
                $validated['codigo_verificacion'],
            );

            if (!$solicitud) {
                return sendResponse(null, 'No se encontró una solicitud con el DNI y código ingresados', 404);
            }

            $solicitud = $this->solicitudService->requestCambioTurno(
                $solicitud,
                Carbon::parse($validated['fecha_turno']),
            );

            return sendResponse($solicitud);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }
}

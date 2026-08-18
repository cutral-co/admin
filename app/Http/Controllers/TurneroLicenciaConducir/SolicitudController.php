<?php

namespace App\Http\Controllers\TurneroLicenciaConducir;

use App\Http\Controllers\Controller;
use App\Http\Requests\TurneroLicenciaConducir\StoreSolicitudRequest;
use App\Services\TurneroLicenciaConducir\SolicitudService;

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
}

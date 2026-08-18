<?php

namespace App\Services\TurneroLicenciaConducir;

use App\Mail\TurneroLicenciaConducir\SolicitudRecibidaMail;
use App\Models\TurneroLicenciaConducir\Solicitud;
use App\Services\Email\EmailLogService;
use Illuminate\Support\Facades\DB;

class SolicitudService
{
    public function __construct(private readonly EmailLogService $emailLogService) {}

    public function create(array $attributes): Solicitud
    {
        return DB::transaction(function () use ($attributes) {
            return Solicitud::create([
                ...$attributes,
                'estado' => Solicitud::ESTADO_PENDIENTE,
            ]);
        });
    }

    public function sendSolicitudRecibidaEmail(Solicitud $solicitud): void
    {
        $this->emailLogService->send(
            $solicitud->email,
            new SolicitudRecibidaMail($solicitud),
            Solicitud::class,
            $solicitud->id,
            auth()->user()?->id,
            trim($solicitud->nombre . ' ' . $solicitud->apellido),
        );
    }
}

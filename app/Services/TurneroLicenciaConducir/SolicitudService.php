<?php

namespace App\Services\TurneroLicenciaConducir;

use App\Mail\TurneroLicenciaConducir\SolicitudRecibidaMail;
use App\Mail\TurneroLicenciaConducir\TurnoAsignadoMail;
use App\Models\TurneroLicenciaConducir\Solicitud;
use App\Services\Email\EmailLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function assignTurno(Solicitud $solicitud, Carbon $fechaTurno): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $fechaTurno) {
            $solicitud->fill([
                'fecha_turno' => $fechaTurno,
                'codigo_verificacion' => $this->generateUniqueVerificationCode(),
                'estado' => Solicitud::ESTADO_TURNO_ASIGNADO,
            ]);
            $solicitud->save();

            return $solicitud->fresh();
        });
    }

    public function sendTurnoAsignadoEmail(Solicitud $solicitud): void
    {
        $this->emailLogService->send(
            $solicitud->email,
            new TurnoAsignadoMail($solicitud),
            Solicitud::class,
            $solicitud->id,
            auth()->user()?->id,
            trim($solicitud->nombre . ' ' . $solicitud->apellido),
        );
    }

    public function findByDniAndCodigo(string $dni, string $codigoVerificacion): ?Solicitud
    {
        return Solicitud::query()
            ->where('dni', $dni)
            ->where('codigo_verificacion', strtoupper($codigoVerificacion))
            ->first();
    }

    public function requestCambioTurno(Solicitud $solicitud, Carbon $fechaTurno): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $fechaTurno) {
            $solicitud->fill([
                'fecha_turno' => $fechaTurno,
                'estado' => Solicitud::ESTADO_REPROGRAMACION_PENDIENTE,
            ]);
            $solicitud->save();

            return $solicitud->fresh();
        });
    }

    private function generateUniqueVerificationCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (
            Solicitud::query()
                ->where('codigo_verificacion', $code)
                ->exists()
        );

        return $code;
    }
}

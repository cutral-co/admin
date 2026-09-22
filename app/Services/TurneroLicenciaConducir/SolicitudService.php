<?php

namespace App\Services\TurneroLicenciaConducir;

use App\Mail\TurneroLicenciaConducir\SolicitudRecibidaMail;
use App\Mail\TurneroLicenciaConducir\TurnoAsignadoMail;
use App\Models\TurneroLicenciaConducir\Cupo;
use App\Models\TurneroLicenciaConducir\Solicitud;
use App\Services\Email\EmailLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DomainException;

class SolicitudService
{
    public function __construct(private readonly EmailLogService $emailLogService) {}

    public function create(array $attributes): Solicitud
    {
        return DB::transaction(function () use ($attributes) {
            $fechaTurno = Carbon::createFromFormat('Y-m-d H:i', $attributes['fecha_turno'])
                ->startOfMinute();

            if ($fechaTurno->isWeekend()) {
                throw new DomainException('Los turnos se asignan únicamente de lunes a viernes');
            }

            $estaDisponible = collect(
                $this->getDisponibilidad($fechaTurno->copy()->startOfDay()),
            )->contains(
                fn (array $dia) => $dia['fecha'] === $fechaTurno->format('Y-m-d')
                    && collect($dia['horarios'])->contains(
                        fn (array $horario) => $horario['hora'] === $fechaTurno->format('H:i'),
                    ),
            );

            if (!$estaDisponible) {
                throw new DomainException('La fecha y hora seleccionadas no están disponibles');
            }

            Solicitud::query()
                ->where('dni', $attributes['dni'])
                ->lockForUpdate()
                ->get();

            $tieneTurnoActivo = Solicitud::query()
                ->where('dni', $attributes['dni'])
                ->where('fecha_turno', '>', now())
                ->whereNotIn('estado', [
                    Solicitud::ESTADO_FINALIZADO,
                    Solicitud::ESTADO_CANCELADO,
                ])
                ->exists();

            if ($tieneTurnoActivo) {
                throw new DomainException('Ya existe un turno activo para el DNI ingresado');
            }

            $cupo = Cupo::query()
                ->where('hora', $fechaTurno->format('H:i:s'))
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            if (!$cupo) {
                throw new DomainException('El horario seleccionado no está disponible');
            }

            $reservados = Solicitud::query()
                ->where('fecha_turno', $fechaTurno)
                ->whereNotIn('estado', [Solicitud::ESTADO_CANCELADO])
                ->count();

            if ($reservados >= $cupo->cantidad) {
                throw new DomainException('El horario seleccionado ya no tiene cupos disponibles');
            }

            return Solicitud::create([
                ...$attributes,
                'codigo_verificacion' => $this->generateUniqueVerificationCode(),
                'fecha_turno' => $fechaTurno,
                'estado' => Solicitud::ESTADO_TURNO_ASIGNADO,
            ]);
        });
    }

    public function getDisponibilidad(?Carbon $fechaSeleccionada = null): array
    {
        $cupos = Cupo::query()
            ->where('activo', true)
            ->where('cantidad', '>', 0)
            ->orderBy('hora')
            ->get();

        if ($cupos->isEmpty()) {
            return [];
        }

        $hoy = now()->startOfDay();
        $reservados = Solicitud::query()
            ->where('fecha_turno', '>=', $hoy)
            ->whereNotIn('estado', [Solicitud::ESTADO_CANCELADO])
            ->get(['fecha_turno'])
            ->countBy(fn (Solicitud $solicitud) => $solicitud->fecha_turno->format('Y-m-d H:i'));

        $horariosPara = function (Carbon $fecha) use ($cupos, $reservados): array {
            if ($fecha->isWeekend()) {
                return [];
            }

            $horarios = [];

            foreach ($cupos as $cupo) {
                $hora = substr($cupo->hora, 0, 5);
                $fechaHora = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $fecha->format('Y-m-d') . ' ' . $hora,
                );
                $disponibles = $cupo->cantidad
                    - ($reservados[$fechaHora->format('Y-m-d H:i')] ?? 0);

                if ($fechaHora->isFuture() && $disponibles > 0) {
                    $horarios[] = [
                        'hora' => $hora,
                        'disponibles' => $disponibles,
                    ];
                }
            }

            return $horarios;
        };

        if ($fechaSeleccionada) {
            $horarios = $horariosPara($fechaSeleccionada);

            return $horarios === [] ? [] : [[
                'fecha' => $fechaSeleccionada->format('Y-m-d'),
                'horarios' => $horarios,
            ]];
        }

        $dias = [];
        $fecha = $hoy->copy();

        while (count($dias) < 5) {
            $horarios = $horariosPara($fecha);

            if ($horarios !== []) {
                $dias[] = [
                    'fecha' => $fecha->format('Y-m-d'),
                    'horarios' => $horarios,
                ];
            }

            $fecha->addDay();
        }

        return $dias;
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

    public function changeTurno(Solicitud $solicitud, Carbon $fechaTurno): Solicitud
    {
        return DB::transaction(function () use ($solicitud, $fechaTurno) {
            $fechaTurno = $fechaTurno->copy()->startOfMinute();

            if ($fechaTurno->isWeekend()) {
                throw new DomainException('Los turnos se asignan únicamente de lunes a viernes');
            }

            $solicitud = Solicitud::query()->lockForUpdate()->find($solicitud->id);

            if (!$solicitud) {
                throw new DomainException('No se encontró el turno');
            }

            if ($solicitud->estado !== Solicitud::ESTADO_TURNO_ASIGNADO) {
                throw new DomainException('El turno no puede modificarse en su estado actual');
            }

            if ($solicitud->fecha_turno?->equalTo($fechaTurno)) {
                return $solicitud?->fresh() ?? $solicitud;
            }

            $estaDisponible = collect($this->getDisponibilidad($fechaTurno->copy()->startOfDay()))
                ->contains(
                    fn (array $dia) => $dia['fecha'] === $fechaTurno->format('Y-m-d')
                        && collect($dia['horarios'])->contains(
                            fn (array $horario) => $horario['hora'] === $fechaTurno->format('H:i'),
                        ),
                );

            if (!$estaDisponible) {
                throw new DomainException('La fecha y hora seleccionadas no están disponibles');
            }

            Solicitud::query()
                ->where('dni', $solicitud->dni)
                ->where('id', '<>', $solicitud->id)
                ->lockForUpdate()
                ->get();

            $cupo = Cupo::query()
                ->where('hora', $fechaTurno->format('H:i:s'))
                ->where('activo', true)
                ->lockForUpdate()
                ->first();

            if (!$cupo) {
                throw new DomainException('El horario seleccionado no está disponible');
            }

            $reservados = Solicitud::query()
                ->where('fecha_turno', $fechaTurno)
                ->where('id', '<>', $solicitud->id)
                ->whereNotIn('estado', [Solicitud::ESTADO_CANCELADO])
                ->count();

            if ($reservados >= $cupo->cantidad) {
                throw new DomainException('El horario seleccionado ya no tiene cupos disponibles');
            }

            $solicitud->update([
                'fecha_turno' => $fechaTurno,
                'estado' => Solicitud::ESTADO_TURNO_ASIGNADO,
            ]);

            return $solicitud->fresh();
        });
    }

    public function sendTurnoModificadoEmail(Solicitud $solicitud): void
    {
        $this->emailLogService->send(
            $solicitud->email,
            new \App\Mail\TurneroLicenciaConducir\TurnoModificadoMail($solicitud),
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

    private function generateUniqueVerificationCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Solicitud::query()->where('codigo_verificacion', $code)->exists());

        return $code;
    }

    /* Flujo administrativo anterior conservado para una eventual reactivación.
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

    */
}

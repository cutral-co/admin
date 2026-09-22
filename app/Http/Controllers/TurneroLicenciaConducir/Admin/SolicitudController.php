<?php

namespace App\Http\Controllers\TurneroLicenciaConducir\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TurneroLicenciaConducir\Admin\UpdateTurnoRequest;
use App\Http\Requests\TurneroLicenciaConducir\UpdateSolicitudEstadoRequest;
use App\Models\TurneroLicenciaConducir\Solicitud;
use App\Services\TurneroLicenciaConducir\SolicitudService;
use Illuminate\Http\Request;

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

            if ($solicitud->estado !== Solicitud::ESTADO_TURNO_ASIGNADO) {
                return sendResponse(
                    null,
                    'El estado del turno ya fue definido y no puede modificarse',
                    422,
                );
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

        return sendResponse(Solicitud::estadoTurnoOptions());
    }

    public function updateTurno(UpdateTurnoRequest $request, int $id)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            $solicitud = Solicitud::find($id);

            if (!$solicitud) {
                return sendResponse(null, 'No se encontró la solicitud', 404);
            }

            if ($solicitud->estado !== Solicitud::ESTADO_TURNO_ASIGNADO) {
                return sendResponse(null, 'El turno no puede modificarse en su estado actual', 422);
            }

            $validated = $request->validated();
            $fechaAnterior = $solicitud->fecha_turno?->format('Y-m-d H:i');
            $emailAnterior = $solicitud->email;
            $solicitud = $this->solicitudService->changeTurno(
                $solicitud,
                \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i', $validated['fecha_turno']),
            );
            $solicitud->email = $validated['email'];
            $solicitud->save();
            $solicitud = $solicitud->fresh();

            if ($fechaAnterior !== $solicitud->fecha_turno?->format('Y-m-d H:i') || $emailAnterior !== $solicitud->email) {
                $this->solicitudService->sendTurnoModificadoEmail($solicitud);
            }

            return sendResponse($solicitud);
        } catch (\DomainException $exception) {
            return sendResponse(null, $exception->getMessage(), 422);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function turnosDelDia(Request $request)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        $fecha = $request->input('fecha', now()->toDateString());

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return sendResponse(null, 'La fecha ingresada es inválida', 422);
        }

        try {
            $turnos = Solicitud::query()
                ->whereDate('fecha_turno', $fecha)
                ->whereNotIn('estado', [Solicitud::ESTADO_CANCELADO])
                ->orderBy('fecha_turno')
                ->get();

            return sendResponse($turnos);
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function descargarTurnosDelDia()
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        return $this->descargarTurnos(
            Solicitud::query()->whereDate('fecha_turno', now()->toDateString()),
            'turnos-del-dia-' . now()->format('Y-m-d') . '.csv',
        );
    }

    public function descargarTodosLosTurnos()
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        return $this->descargarTurnos(
            Solicitud::query()->whereDate('fecha_turno', '>=', now()->toDateString()),
            'turnos-desde-' . now()->format('Y-m-d') . '.csv',
        );
    }

    /* Flujo anterior de asignación administrativa conservado para una eventual reactivación.
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
    */

    private function authorizeAccess()
    {
        $user = auth()->user();

        if (!$user || !$user->can(self::REQUIRED_PERMISSION)) {
            return sendResponse(null, 'No tiene permisos para acceder al módulo', 403);
        }

        return null;
    }

    private function descargarTurnos($query, string $filename)
    {
        $query
            ->whereNotNull('fecha_turno')
            ->whereNotIn('estado', [Solicitud::ESTADO_CANCELADO])
            ->orderBy('fecha_turno');

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['DNI', 'Nombre', 'Email', 'Fecha', 'Hora']);

            foreach ($query->cursor() as $solicitud) {
                fputcsv($handle, [
                    $solicitud->dni,
                    trim($solicitud->nombre . ' ' . $solicitud->apellido),
                    $solicitud->email,
                    $solicitud->fecha_turno?->format('d/m/Y'),
                    $solicitud->fecha_turno?->format('H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}

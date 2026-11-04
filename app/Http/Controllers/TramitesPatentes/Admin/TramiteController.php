<?php

namespace App\Http\Controllers\TramitesPatentes\Admin;

use App\Http\Controllers\Controller;
use App\Models\TramitesPatentes\Documento;
use App\Models\TramitesPatentes\RequisitoDocumentacion;
use App\Models\TramitesPatentes\Tramite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TramiteController extends Controller
{
    private const PERMISSION = 'admin.tramites-patentes';

    public function index()
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        return sendResponse(
            Tramite::query()
                ->with(['tipoTramite', 'solicitante.person', 'documentos'])
                ->latest()
                ->get(),
        );
    }

    public function show(int $id)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        $tramite = Tramite::query()
            ->with(['tipoTramite', 'documentos.requisitoDocumentacion', 'movimientos.autor', 'solicitante.person'])
            ->find($id);

        return $tramite
            ? sendResponse($tramite)
            : sendResponse(null, 'No se encontró el trámite', 404);
    }

    public function documento(int $id, int $documentoId)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        $documento = Documento::query()
            ->where('tramite_id', $id)
            ->find($documentoId);

        if (!$documento || !$documento->ruta || !Storage::disk('serverdata')->exists($documento->ruta)) {
            return sendResponse(null, 'No se encontró el documento solicitado', 404);
        }

        $file = get_file($documento->ruta);
        $file['file_name'] = $documento->nombre_original ?? $file['file_name'];

        return sendResponse($file);
    }

    public function aprobar(Request $request, int $id)
    {
        $validated = $request->validate(['observacion' => ['nullable', 'string', 'max:2000']]);

        return $this->changeState(
            $id,
            Tramite::ESTADO_APROBADO,
            'aprobado',
            $validated['observacion'] ?? null,
        );
    }

    public function rechazar(Request $request, int $id)
    {
        $validated = $request->validate(['observacion' => ['required', 'string', 'max:2000']]);

        return $this->changeState($id, Tramite::ESTADO_RECHAZADO, 'rechazado', $validated['observacion']);
    }

    public function devolver(Request $request, int $id)
    {
        $validated = $request->validate([
            'documento_ids' => ['nullable', 'array'],
            'documento_ids.*' => ['integer'],
            'observacion' => ['required', 'string', 'max:2000'],
        ]);

        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            return DB::transaction(function () use ($id, $validated) {
                $tramite = Tramite::find($id);
                if (!$tramite) {
                    return sendResponse(null, 'No se encontró el trámite', 404);
                }

                $documentoIds = collect($validated['documento_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();
                $documentos = $tramite->documentos()
                    ->whereIn('id', $documentoIds)
                    ->get();

                if ($documentos->count() !== $documentoIds->count()) {
                    return sendResponse(null, 'Uno o más documentos no corresponden al trámite', 422);
                }

                foreach ($documentos as $documento) {
                    $documento->estado = 'observado';
                    $documento->observacion = $validated['observacion'];
                    $documento->save();
                }

                $tramite->estado = Tramite::ESTADO_DOCUMENTACION_REQUERIDA;
                $tramite->observacion_final = $validated['observacion'];
                $tramite->save();
                $this->registerMovement($tramite, 'devuelto', $validated['observacion']);

                return sendResponse($tramite->fresh(['documentos', 'movimientos']));
            });
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    public function solicitarDocumento(Request $request, int $id)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        $validated = $request->validate([
            'requisito_documentacion_id' => ['nullable', 'integer', 'exists:tram_pat_requisitos_documentacion,id'],
            'titulo' => ['required', 'string', 'max:255'],
            'observacion' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            return DB::transaction(function () use ($id, $validated) {
                $tramite = Tramite::find($id);
                if (!$tramite) {
                    return sendResponse(null, 'No se encontró el trámite', 404);
                }

                if (isset($validated['requisito_documentacion_id'])) {
                    $validRequirement = RequisitoDocumentacion::query()
                        ->whereKey($validated['requisito_documentacion_id'])
                        ->where('tipo_tramite_id', $tramite->tipo_tramite_id)
                        ->exists();

                    if (!$validRequirement) {
                        return sendResponse(null, 'El requisito no corresponde al tipo de trámite', 422);
                    }
                }

                Documento::create([
                    'tramite_id' => $tramite->id,
                    'requisito_documentacion_id' => $validated['requisito_documentacion_id'] ?? null,
                    'titulo' => $validated['titulo'],
                    'estado' => 'solicitado',
                    'observacion' => $validated['observacion'] ?? null,
                ]);

                $tramite->estado = Tramite::ESTADO_DOCUMENTACION_REQUERIDA;
                $tramite->save();
                $this->registerMovement($tramite, 'documentacion-solicitada', $validated['observacion'] ?? null);

                return sendResponse($tramite->fresh('documentos'));
            });
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    private function changeState(int $id, string $state, string $action, ?string $observation = null)
    {
        if ($response = $this->authorizeAccess()) {
            return $response;
        }

        try {
            return DB::transaction(function () use ($id, $state, $action, $observation) {
                $tramite = Tramite::find($id);
                if (!$tramite) {
                    return sendResponse(null, 'No se encontró el trámite', 404);
                }

                $tramite->estado = $state;
                $tramite->observacion_final = $observation;
                $tramite->save();
                $this->registerMovement($tramite, $action, $observation);

                return sendResponse($tramite->fresh());
            });
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    private function registerMovement(Tramite $tramite, string $action, ?string $observation): void
    {
        $tramite->movimientos()->create([
            'user_id' => auth()->id(),
            'accion' => $action,
            'observacion' => $observation,
        ]);
    }

    private function authorizeAccess()
    {
        $user = auth()->user();

        return $user && $user->can(self::PERMISSION)
            ? null
            : sendResponse(null, 'No tiene permisos para acceder al módulo', 403);
    }
}

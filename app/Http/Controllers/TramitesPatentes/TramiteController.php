<?php

namespace App\Http\Controllers\TramitesPatentes;

use App\Http\Controllers\Controller;
use App\Models\TramitesPatentes\RequisitoDocumentacion;
use App\Models\TramitesPatentes\TipoTramite;
use App\Models\TramitesPatentes\Tramite;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TramiteController extends Controller
{
    public function index()
    {
        return sendResponse(
            Tramite::query()
                ->where('user_id', auth()->id())
                ->with(['tipoTramite', 'documentos.requisitoDocumentacion'])
                ->latest()
                ->get(),
        );
    }

    public function show(int $id)
    {
        $tramite = Tramite::query()
            ->where('user_id', auth()->id())
            ->with(['tipoTramite', 'documentos.requisitoDocumentacion', 'movimientos'])
            ->find($id);

        return $tramite
            ? sendResponse($tramite)
            : sendResponse(null, 'No se encontró el trámite solicitado', 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_tramite_codigo' => ['required', 'string', 'max:50'],
            'dominio' => ['required', 'string', 'max:20'],
            'documentos' => ['required', 'array'],
            'documentos.*' => ['file', 'max:10240'],
        ]);

        $tipoTramite = TipoTramite::query()
            ->where('codigo', $validated['tipo_tramite_codigo'])
            ->where('activo', true)
            ->with([
                'requisitosDocumentacion' => fn ($query) => $query
                    ->where('activo', true)
                    ->orderBy('orden'),
            ])
            ->first();

        if (!$tipoTramite) {
            return sendResponse(null, 'El tipo de trámite solicitado no está disponible', 422);
        }

        $user = auth()->user()->loadMissing('person');
        if (!$user->person?->email) {
            return sendResponse(null, 'Tu cuenta debe tener un correo electrónico para iniciar el trámite', 422);
        }

        $documents = $request->file('documentos', []);
        $requirements = $tipoTramite->requisitosDocumentacion;
        $this->validateDocuments($requirements, $documents);

        try {
            return DB::transaction(function () use ($validated, $tipoTramite, $user, $documents, $requirements) {
                $tramite = Tramite::create([
                    'user_id' => $user->id,
                    'tipo_tramite_id' => $tipoTramite->id,
                    'dominio' => strtoupper(trim($validated['dominio'])),
                    'email_contacto' => $user->person->email,
                    'estado' => Tramite::ESTADO_PENDIENTE,
                ]);

                foreach ($requirements as $requirement) {
                    $file = $documents[$requirement->id] ?? null;
                    if (!$file instanceof UploadedFile) {
                        continue;
                    }

                    $path = storage_file($file, "tramites-patentes/{$tramite->id}");
                    $tramite->documentos()->create([
                        'requisito_documentacion_id' => $requirement->id,
                        'titulo' => $requirement->titulo,
                        'ruta' => $path,
                        'nombre_original' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'estado' => 'presentado',
                    ]);
                }

                $tramite->movimientos()->create([
                    'user_id' => $user->id,
                    'accion' => 'solicitado',
                ]);

                return sendResponse(
                    $tramite->fresh(['tipoTramite', 'documentos.requisitoDocumentacion', 'movimientos']),
                    null,
                    201,
                );
            });
        } catch (\Throwable $th) {
            $log = saveLog($th->getMessage(), get_class() . '::' . __FUNCTION__, $th->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * @param \Illuminate\Support\Collection<int, RequisitoDocumentacion> $requirements
     * @param array<int, UploadedFile> $documents
     */
    private function validateDocuments($requirements, array $documents): void
    {
        $requirementIds = $requirements->pluck('id')->map(fn ($id) => (int) $id)->all();
        $unexpectedDocumentIds = array_diff(array_map('intval', array_keys($documents)), $requirementIds);

        if ($unexpectedDocumentIds) {
            throw ValidationException::withMessages([
                'documentos' => ['Se adjuntó un documento que no corresponde al trámite seleccionado.'],
            ]);
        }

        foreach ($requirements as $requirement) {
            $file = $documents[$requirement->id] ?? null;

            if ($requirement->requerido && !$file instanceof UploadedFile) {
                throw ValidationException::withMessages([
                    "documentos.{$requirement->id}" => ["El documento {$requirement->titulo} es obligatorio."],
                ]);
            }

            if (!$file instanceof UploadedFile) {
                continue;
            }

            if (!in_array($file->getMimeType(), $requirement->formatos_permitidos, true)) {
                throw ValidationException::withMessages([
                    "documentos.{$requirement->id}" => ["El formato de {$requirement->titulo} no está permitido."],
                ]);
            }
        }
    }
}

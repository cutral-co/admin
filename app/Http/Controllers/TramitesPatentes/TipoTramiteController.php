<?php

namespace App\Http\Controllers\TramitesPatentes;

use App\Http\Controllers\Controller;
use App\Models\TramitesPatentes\TipoTramite;

class TipoTramiteController extends Controller
{
    public function show(string $codigo)
    {
        $tipoTramite = TipoTramite::query()
            ->where('codigo', $codigo)
            ->where('activo', true)
            ->with([
                'requisitosDocumentacion' => fn ($query) => $query
                    ->where('activo', true)
                    ->orderBy('orden'),
            ])
            ->first();

        return $tipoTramite
            ? sendResponse($tipoTramite)
            : sendResponse(null, 'No se encontró el tipo de trámite solicitado', 404);
    }
}

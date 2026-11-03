<?php

namespace App\Models\TramitesPatentes;

use Illuminate\Database\Eloquent\Model;

class RequisitoDocumentacion extends Model
{
    protected $table = 'tram_pat_requisitos_documentacion';

    protected $fillable = [
        'tipo_tramite_id', 'titulo', 'descripcion', 'formatos_permitidos', 'requerido', 'orden', 'activo',
    ];

    protected $casts = ['formatos_permitidos' => 'array', 'requerido' => 'boolean', 'activo' => 'boolean'];
}

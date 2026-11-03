<?php

namespace App\Models\TramitesPatentes;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'tram_pat_documentos';

    protected $fillable = [
        'tramite_id', 'requisito_documentacion_id', 'titulo', 'ruta', 'nombre_original', 'mime_type',
        'estado', 'observacion',
    ];

    public function requisitoDocumentacion()
    {
        return $this->belongsTo(RequisitoDocumentacion::class, 'requisito_documentacion_id');
    }
}

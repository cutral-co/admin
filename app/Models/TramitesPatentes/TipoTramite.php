<?php

namespace App\Models\TramitesPatentes;

use Illuminate\Database\Eloquent\Model;

class TipoTramite extends Model
{
    protected $table = 'tram_pat_tipos_tramites';

    protected $fillable = ['codigo', 'titulo', 'descripcion', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function requisitosDocumentacion()
    {
        return $this->hasMany(RequisitoDocumentacion::class, 'tipo_tramite_id');
    }
}

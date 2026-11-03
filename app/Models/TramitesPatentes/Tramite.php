<?php

namespace App\Models\TramitesPatentes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Tramite extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_DOCUMENTACION_REQUERIDA = 'documentacion-requerida';
    public const ESTADO_APROBADO = 'aprobado';
    public const ESTADO_RECHAZADO = 'rechazado';

    protected $table = 'tram_pat_tramites';

    protected $fillable = [
        'user_id', 'tipo_tramite_id', 'dominio', 'email_contacto', 'estado', 'observacion_final',
    ];

    public function tipoTramite()
    {
        return $this->belongsTo(TipoTramite::class, 'tipo_tramite_id');
    }

    public function documentos()
    {
        return $this->hasMany(Documento::class, 'tramite_id');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'tramite_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

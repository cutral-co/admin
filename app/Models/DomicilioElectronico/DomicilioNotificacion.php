<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Model;

class DomicilioNotificacion extends Model
{
    protected $table = 'de_domicilio_notificacion';

    public $timestamps = false;

    protected $fillable = [
        'domicilio_id',
        'notificacion_id',
        'tipo_destinatario_id',
        'fecha_recibido',
        'fecha_visto',
        'fecha_archivado',
    ];

    protected $hidden = [
        'domicilio_id',
        'notificacion_id',
        'tipo_destinatario_id',
    ];

    public function notificacion()
    {
        return $this->hasOne(Notificacion::class, 'id', 'notificacion_id');
    }

    public function domicilio()
    {
        return $this->belongsTo(Domicilio::class);
    }

    public function tipo_destinatario()
    {
        return $this->belongsTo(TipoDestinatario::class);
    }
}

<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'de_logs';

    protected $fillable = [
        'domicilio_id',
        'notificacion_id',
        'tipo_destinatario',
        'message',
        'attributes',
    ];

    protected $hidden = [
        'notificacion_id',
        'created_at',
        'updated_at',
    ];

    public function notificacion()
    {
        return $this->hasOne(Notificacion::class);
    }
}

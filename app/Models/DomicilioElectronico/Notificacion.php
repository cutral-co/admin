<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notificacion extends Model
{
    use SoftDeletes;

    protected $table = 'de_notificacion';

    protected $fillable = [
        'origin_id',
        'title',
        'body',
        'block',
        'hash',
        'data',
    ];

    protected $hidden = [
        'origin_id',
        'block',
        'updated_at',
        'pivot',
    ];

    /* public function user()
    {
        return $this->belongsTo(User::class, 'de_id', 'de_id');
    }
 */
    public function archivos()
    {
        return $this->hasMany(NotificacionArchivo::class);
    }

    public function domicilio_notificacion()
    {
        return $this->belongsTo(DomicilioNotificacion::class, 'id', 'notificacion_id');
    }

    public function origin()
    {
        return $this->belongsTo(Origin::class);
    }
}

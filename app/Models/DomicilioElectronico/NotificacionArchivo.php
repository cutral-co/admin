<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Model;

class NotificacionArchivo extends Model
{
    protected $table = 'de_notificacion_archivo';

    public $timestamps = false;

    protected $fillable = [
        'notificacion_id',
        'name',
        'path',
    ];

    protected $hidden = [
        'notificacion_id',
        'path',
    ];

    public function notificacion()
    {
        return $this->belongsTo(Notificacion::class, 'notificacion_id');
    }

    public function domicilio_notificacion()
    {
        return $this->hasMany(DomicilioNotificacion::class, 'notificacion_id', 'notificacion_id');
    }

    /** Obtiene el archivo por id, si corresponde al usuario  */
    public static function obtenerArchivo($id)
    {
        $user = auth()->user();

        return NotificacionArchivo::where('id', $id)
            ->with('domicilio_notificacion.domicilio')
            ->whereHas('domicilio_notificacion.domicilio', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();
    }
}

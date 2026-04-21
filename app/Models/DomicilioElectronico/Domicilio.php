<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Domicilio extends Model
{
    protected $table = 'de_domicilio';

    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'domicilio_real',
        'is_verified',
        'token',
        'nombre',
        'documento',
        'renaper_id'
    ];

    protected $hidden = [
        'token',
        'id',
        // 'created_at',
        'updated_at',
        'mensajes',
    ];

    protected $appends  = [
        'has_notificaciones',
        'countNotificacionesSinVer'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function notificaciones()
    {
        return $this->belongsToMany(Notificacion::class, 'de_domicilio_notificacion', 'domicilio_id', 'notificacion_id');
    }

    public function mensajes()
    {
        return $this->hasMany(DomicilioNotificacion::class)
            ->whereHas('notificacion', function ($query) {
                $query->whereNull('deleted_at');
            });
    }

    public function getHasNotificacionesAttribute()
    {
        return $this->mensajes->where('fecha_visto', null)->count() > 0;
    }

    public function getCountNotificacionesSinVerAttribute()
    {
        return $this->mensajes->where('fecha_visto', null)->count();
    }
}

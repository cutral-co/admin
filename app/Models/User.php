<?php

namespace App\Models;

use App\Models\DomicilioElectronico\Domicilio;
use App\Models\DomicilioElectronico\DomicilioNotificacion;
use App\Models\DomicilioElectronico\Notificacion;
use App\Models\DomicilioElectronico\NotificacionArchivo;
use App\Models\DomicilioElectronico\Log;

class User extends \App\Models\Authenticatable\Auth
{
    protected $fillable = [
        'id',
        'cuit',
        'password',
        'person_id',
        'is_verified'
    ];

    protected $hidden = [
        'person_id',
        'password',
        'created_at',
        'updated_at',
        'permissions',
        'roles'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /** Relacion con \App\Models\DomicilioElectronico\Domicilio */
    public function de()
    {
        return $this->hasOne(Domicilio::class);
    }

    public function de_notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'domicilio_electronico_id', 'domicilio_electronico_id');
    }

    public function getDomicilioElectronicoDataAttribute()
    {
        if ($this->de) {
            $de = $this->de->toArray();
            $de['has_notificaciones'] = $this->de->hasNotificaciones;
            $de['count_notificaciones_sin_ver'] = $this->de->countNotificacionesSinVer;
        } else {
            $de = null;
        }

        return $de;
    }

    /**
     * Elimina todo lo relacionado al domicilio electrónico del usuario.
     * ATENCIÓN: Solo para debug en desarrollo.
     */
    public function delete_de()
    {
        $domicilio = $this->de;

        if (!$domicilio) {
            return false;
        }

        $domicilioId = $domicilio->id;

        $notificacionIds = DomicilioNotificacion::where('domicilio_id', $domicilioId)
            ->pluck('notificacion_id')
            ->toArray();

        if (!empty($notificacionIds)) {
            NotificacionArchivo::whereIn('notificacion_id', $notificacionIds)->delete();
            DomicilioNotificacion::where('domicilio_id', $domicilioId)->delete();
            Notificacion::whereIn('id', $notificacionIds)->delete();
        }

        Log::where('domicilio_id', $domicilioId)->delete();
        $domicilio->delete();

        $this->de_id = null;
        $this->save();

        return true;
    }
}

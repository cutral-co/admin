<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Common\{BarrioMunicipio, Provincia};
use App\Models\Table\EstadoUserSolicitud;

class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'users_solicitudes';

    protected $fillable = [
        "lastname",
        "name",
        "cuit",
        "email",
        "phone",

        /* Cuando no es de Cutral Co el valor es null */
        "barrio_id",

        /* Cuando selecciono otra localidad, barrio_id deberia ser null */
        'provincia_id',
        'municipio',
        'otro_barrio',

        "calle",
        "altura",
        "manzana",
        "lote",
        "piso",
        "depto",

        "token_verificacion",
        "ultimo_envio_email",
        "fecha_verificado",
        "estado_id",
    ];

    protected $hidden = [
        "token_verificacion",
        "ultimo_envio_email",
        "fecha_verificado",
        "estado_id",
        "barrio_id",
    ];

    public function barrio()
    {
        return $this->belongsTo(BarrioMunicipio::class, 'barrio_id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function estado()
    {
        return $this->belongsTo(EstadoUserSolicitud::class, 'estado_id', 'id');
    }

    public static function getCountSinVerificar()
    {
        return self::whereNull('fecha_verificado')->count();
    }

    public static function getCountPendientes()
    {
        $estadoNuevo = EstadoUserSolicitud::get('nuevo');

        if (!$estadoNuevo) {
            return 0;
        }

        return self::whereNotNull('fecha_verificado')->where('estado_id', $estadoNuevo->id)->count();
    }

    public static function getCountAprobadas()
    {
        $estadoAprobado = EstadoUserSolicitud::get('aprobado');

        if (!$estadoAprobado) {
            return 0;
        }

        return self::where('estado_id', $estadoAprobado->id)->count();
    }
}

<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

use App\Models\Common\{BarrioMunicipio, Provincia};
use App\Models\Concerns\HasDomicilioFisico;
use App\Models\DomicilioElectronico\Domicilio;
use App\Models\Person;
use App\Models\Table\EstadoUserSolicitud;
use App\Models\User;

class Solicitud extends Model
{
    use HasFactory;
    use HasDomicilioFisico;

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

    public function aprobarSolicitud(): array
    {
        $this->assertPuedeCambiarEstado();

        $estadoAprobado = EstadoUserSolicitud::get('aprobado');
        if (!$estadoAprobado) {
            throw new \RuntimeException('No se encontro el estado aprobado');
        }

        $this->estado_id = $estadoAprobado->id;
        $this->save();

        $plainPassword = $this->generateSecurePassword();

        $person = Person::updateOrCreate(
            ['cuit' => $this->cuit],
            [
                'name' => $this->name,
                'lastname' => $this->lastname,
                'email' => $this->email,
                'phone' => $this->phone,
                'calle' => $this->calle,
                'altura' => $this->altura,
                'manzana' => $this->manzana,
                'lote' => $this->lote,
                'piso' => $this->piso,
                'depto' => $this->depto,
                'barrio_id' => $this->barrio_id,
                'municipio' => $this->municipio,
                'otro_barrio' => $this->otro_barrio,
                'provincia_id' => $this->provincia_id,
            ],
        );

        $user = User::updateOrCreate(
            ['cuit' => $person->cuit],
            [
                'password' => Hash::make($plainPassword),
                'is_verified' => true,
                'person_id' => $person->id,
            ],
        );

        $domicilio = Domicilio::updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $person->email,
                'phone' => $person->phone,
                'domicilio_real' => $person->stringDatosDomicilio(),
                'nombre' => trim($person->name . ' ' . $person->lastname),
                'documento' => $person->cuit,
                'is_verified' => true,
                'token' => null,
            ],
        );

        $user->de_id = $domicilio->id;
        $user->save();

        return [
            'user' => $user,
            'plainPassword' => $plainPassword,
        ];
    }

    public function rechazarSolicitud(): void
    {
        $this->assertPuedeCambiarEstado();

        $estadoRechazado = EstadoUserSolicitud::get('rechazado');
        if (!$estadoRechazado) {
            throw new \RuntimeException('No se encontro el estado rechazado');
        }

        $this->estado_id = $estadoRechazado->id;
        $this->save();
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

    private function assertPuedeCambiarEstado(): void
    {
        $estadoAprobado = EstadoUserSolicitud::get('aprobado');
        $estadoRechazado = EstadoUserSolicitud::get('rechazado');

        if (
            ($estadoAprobado && $this->estado_id === $estadoAprobado->id)
            || ($estadoRechazado && $this->estado_id === $estadoRechazado->id)
        ) {
            throw new \RuntimeException('No se puede cambiar el estado de esta solicitud');
        }
    }

    private function generateSecurePassword(int $length = 6): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }
}

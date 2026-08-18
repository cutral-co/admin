<?php

namespace App\Models\TurneroLicenciaConducir;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_EN_GESTION = 'en-gestion';
    public const ESTADO_CONTACTADO = 'contactado';
    public const ESTADO_FINALIZADO = 'finalizado';
    public const ESTADO_CANCELADO = 'cancelado';

    protected $table = 'turnero_lc_solicitudes';

    protected $fillable = [
        'cuit',
        'nombre',
        'apellido',
        'telefono',
        'email',
        'estado',
    ];

    public static function estadoOptions(): array
    {
        return [
            self::ESTADO_PENDIENTE,
            self::ESTADO_EN_GESTION,
            self::ESTADO_CONTACTADO,
            self::ESTADO_FINALIZADO,
            self::ESTADO_CANCELADO,
        ];
    }
}

<?php

namespace App\Models\Eventos;

use Illuminate\Database\Eloquent\Model;

class E202411Registros extends Model
{
    protected $connection = 'evento202411';

    protected $table = 'registros';

    protected $fillable = [
        'lastname',
        'name',
        'dni',
        'email',
        'phone',
        'opid',
        'nro_comprobante',
        'comprobante',
        'importe'
    ];
}

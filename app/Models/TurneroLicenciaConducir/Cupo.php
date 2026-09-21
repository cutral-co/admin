<?php

namespace App\Models\TurneroLicenciaConducir;

use Illuminate\Database\Eloquent\Model;

class Cupo extends Model
{
    protected $table = 'turnero_lc_cupos';

    protected $fillable = [
        'hora',
        'cantidad',
        'activo',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'activo' => 'boolean',
    ];
}

<?php

namespace App\Models;

use App\Models\Common\{Provincia, BarrioMunicipio};
use App\Models\Concerns\HasDomicilioFisico;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasDomicilioFisico;

    protected $table = 'persons';

    protected $fillable = [
        'id',
        'cuit',
        'name',
        'lastname',
        'is_company',

        /* Datos de contacto */
        'email',
        'phone',

        /* Direaccion Micro */
        'calle',
        'altura',
        'manzana',
        'lote',
        'piso',
        'depto',

        /* Direccion Macro */
        'barrio_id',
        'municipio',
        'otro_barrio',
        'municipio',
        'provincia_id',
    ];

    protected $hidden = [
        'calle',
        'altura',
        'manzana',
        'lote',
        'piso',
        'depto',

        'barrio_id',
        'otro_barrio',
        'municipio',
        'provincia_id',

        'created_at',
        'updated_at',

        'provincia'
    ];

    protected $casts = [
        'is_company' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
}

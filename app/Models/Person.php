<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
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
        'barrio',
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
        'barrio',
        'municipio',
        'provincia_id',

        'created_at',
        'updated_at',

        'barrio_municipal',
        'provincia'
    ];

    protected $appends = ['direccion'];

    protected $casts = [
        'is_company' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function barrio_municipal()
    {
        return $this->belongsTo(BarrioMunicipio::class, 'barrio_id');
    }

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

    public function getDireccionAttribute()
    {
        if (!$this->call && !$this->altura) {
            return null;
        }

        return [
            'calle' => $this->calle,
            'altura' => $this->altura,
            'manzana' => $this->manzana,
            'lote' => $this->lote,
            'piso' => $this->piso,
            'depto' => $this->depto,

            'municipio' => $this->municipio,
            'barrio' => $this->barrio_id ? $this->barrio_municipal : $this->barrio,
            'provincia' => $this->provincia ? $this->provincia : ($this->barrio_municipal ?  $this->barrio_municipal->provincia : null),
            'is_cutral' => (bool)$this->barrio_id
        ];
    }
}

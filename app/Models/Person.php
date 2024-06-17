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
        'email',
        'phone',

        'calle',
        'altura',
        'manzana',
        'lote',
        'piso',
        'depto',

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
        return [
            'calle' => $this->calle,
            'altura' => $this->altura,
            'manzana' => $this->manzana,
            'lote' => $this->lote,
            'piso' => $this->piso,
            'depto' => $this->depto,

            'municipio' => $this->municipio,
            'barrio' => $this->barrio_id ? $this->barrio_municipal->name : $this->barrio,
            'provincia' => $this->provincia ? $this->provincia : $this->barrio_municipal->provincia->name,
        ];
    }
}

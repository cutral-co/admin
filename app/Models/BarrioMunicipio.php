<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarrioMunicipio extends Model
{
    protected $table = 'barrios_municipio';

    protected $fillable = [
        'name',
        'provincia_id',
    ];

    protected $hidden = [
        'provincia_id',
        'created_at',
        'updated_at',
    ];

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }
}

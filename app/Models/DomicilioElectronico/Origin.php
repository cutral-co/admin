<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Model;

class Origin extends Model
{
    protected $table = 'de_origin';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'descripcion',
        'token',
    ];

    protected $hidden = [
        'token',
    ];
}

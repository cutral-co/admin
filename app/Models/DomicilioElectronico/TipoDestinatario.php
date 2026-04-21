<?php

namespace App\Models\DomicilioElectronico;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoDestinatario extends Model
{
    protected $table = 'de_tipo_destinatario';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'descripcion',
    ];

    protected $hidden = [
        'descripcion',
    ];
}

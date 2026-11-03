<?php

namespace App\Models\TramitesPatentes;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'tram_pat_movimientos';

    protected $fillable = ['tramite_id', 'user_id', 'accion', 'observacion'];

    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

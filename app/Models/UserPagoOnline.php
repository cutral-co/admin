<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPagoOnline extends Model
{
    protected $table = 'user_pago_online';
    protected $fillable = ['user_id', 'opid', 'nro_comprobante', 'comprobante', 'importe'];
}

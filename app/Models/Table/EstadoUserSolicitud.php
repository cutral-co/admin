<?php

namespace App\Models\Table;

use Illuminate\Database\Eloquent\Builder;

class EstadoUserSolicitud extends Table
{
    protected static function booted()
    {
        $sub_table_name = "users_solicitudes.estados";
        static::addGlobalScope($sub_table_name, function (Builder $builder) use ($sub_table_name) {
            $builder->where('name', $sub_table_name);
        });
    }

    public static function get(string $key)
    {
        return  self::where('value', $key)->first();
    }
}

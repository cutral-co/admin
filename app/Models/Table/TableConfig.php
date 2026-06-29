<?php

namespace App\Models\Table;

use Illuminate\Database\Eloquent\Model;

class TableConfig extends Model
{
    protected $table = 'table_config';

    protected $fillable = [
        'name',
        'color',
        'backgound',
        'classname',
        'json',
    ];

    protected $hidden = [
        'table_id',
        'created_at',
        'updated_at',
    ];
}

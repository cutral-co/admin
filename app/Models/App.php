<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $table = 'apps';

    protected $fillable = [
        'id',
        'name',
        'title',
        'description',
        'keywords',
        'url',
        'enabled',
        'image',
        'required_permission',
    ];

    protected $hidden = [
        'enabled',
        'required_permission',

        'created_at',
        'updated_at',
    ];
}

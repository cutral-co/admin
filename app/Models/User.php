<?php

namespace App\Models;

class User extends \App\Models\Authenticatable\Auth
{
    protected $fillable = [
        'id',
        'cuit',
        'password',
    ];

    protected $hidden = [
        'person_id',
        'password',
        'created_at',
        'updated_at',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}

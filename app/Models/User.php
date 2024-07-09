<?php

namespace App\Models;

class User extends \App\Models\Authenticatable\Auth
{
    protected $fillable = [
        'id',
        'cuit',
        'password',
        'person_id',
        'is_verified'
    ];

    protected $hidden = [
        'person_id',
        'password',
        'created_at',
        'updated_at',
        'permissions',
        'roles'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserChange extends Model
{
    protected $table = 'user_change';

    protected $fillable = [
        'user_id',
        'type',
        'old_value',
        'new_value',
        'token',
    ];

    protected $hidden = [
        'token',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

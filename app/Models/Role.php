<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{
    protected $hidden = ['pivot', 'guard_name',  'created_at', 'updated_at'];
}

<?php

namespace App\Models\Authenticatable;

class Permission extends \Spatie\Permission\Models\Permission
{
    protected $hidden = ['pivot', 'guard_name',  'created_at', 'updated_at'];
}

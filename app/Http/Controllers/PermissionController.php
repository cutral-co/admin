<?php

namespace App\Http\Controllers;

use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Permission\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function getPermissions()
    {
        $data = PermissionResource::collection(Permission::all());

        return sendResponse($data);
    }

    public function getPermission($id)
    {
        $permiso = Permission::where('id', $id)->first();
        if (!$permiso) {
            return sendResponse(null, 'No existe el rol');
        }

        return sendResponse(new PermissionResource($permiso));
    }

    public function getRoles()
    {
        $data = RoleResource::collection(Role::all());

        return sendResponse($data);
    }

    public function getRole($id)
    {
        $role = Role::where('id', $id)->first();
        if (!$role) {
            return sendResponse(null, 'No existe el rol');
        }

        return sendResponse(new RoleResource($role));
    }

    public function asignarPermisos(Request $request)
    {
        if (!$user = User::find($request->user_id)) {
            return sendResponse(null, 'No existe el usuario', 301);
        }

        $permission = Permission::where('name', $request->permission_name)->first();
        if (!$permission) {
            return sendResponse(null, 'No existe el permiso', 301);
        }

        $user->givePermissionTo($permission->name);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return sendResponse('Se otorgo el permiso', null, 200, [$user->email, $permission->name]);
    }

    public function retirarPermiso(Request $request)
    {
        if (!$user = User::find($request->user_id)) {
            return sendResponse(null, 'No existe el usuario', 301);
        }

        $permission = Permission::where('name', $request->permission_name)->first();
        if (!$permission) {
            return sendResponse(null, 'No existe el permiso', 301);
        }

        $user->revokePermissionTo($permission->name);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return sendResponse('Se retiro el permiso', null, 200, [$user->email, $permission->name]);
    }

    public function viewPermissions($user_id)
    {
        if (!$user = User::find($user_id)) {
            return sendResponse(null, 'No existe el usuario', 301);
        }

        $permissions = $user->permissions;

        return sendResponse($permissions);
    }

    public function asignarRol(Request $request)
    {
        if (!$user = User::find($request->user_id)) {
            return sendResponse(null, 'No existe el usuario', 301);
        }

        $role = Role::where('name', $request->role_name)->first();
        if (!$role) {
            return sendResponse(null, 'No existe el rol', 301);
        }

        $user->assignRole($role->name);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return sendResponse('Se otorgo el rol', null, 200, [$user->email, $role->name]);
    }

    public function retirarRol(Request $request)
    {
        if (!$user = User::find($request->user_id)) {
            return sendResponse(null, 'No existe el usuario', 301);
        }

        $role = Role::where('name', $request->role_name)->first();
        if (!$role) {
            return sendResponse(null, 'No existe el role', 301);
        }

        $user->removeRole($role->name);

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return sendResponse('Se retiro el role', null, 200, [$user->email, $role->name]);
    }

    public function viewRoles($user_id)
    {
        if (!$user = User::find($user_id)) {
            return sendResponse(null, 'No existe el usuario', 301);
        }

        $permissions = $user->roles;

        return sendResponse($permissions);
    }
}

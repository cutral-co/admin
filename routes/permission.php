<?php

use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['jwt.verify']], function () {
    /* Basicos */
    Route::get('permisos', [PermissionController::class, 'getPermissions'])->middleware(['permission:permission.view']);
    Route::get('permisos/{id}', [PermissionController::class, 'getPermission'])->middleware(['permission:permission.view']);

    Route::post('permiso/asignar/', [PermissionController::class, 'asignarPermisos'])->middleware(['permission:permission.asign']);
    Route::post('permiso/retirar/', [PermissionController::class, 'retirarPermiso'])->middleware(['permission:permission.asign']);

    Route::get('permiso/user/{id}', [PermissionController::class, 'viewPermissions'])->middleware(['permission:permission.view']);

    Route::get('roles', [PermissionController::class, 'getRoles'])->middleware(['permission:role.view']);
    Route::get('roles/{id}', [PermissionController::class, 'getRole'])->middleware(['permission:role.view']);

    Route::post('rol/asignar/', [PermissionController::class, 'asignarRol'])->middleware(['permission:role.asign']);
    Route::post('rol/retirar/', [PermissionController::class, 'retirarRol'])->middleware(['permission:role.asign']);

    Route::get('rol/user/{id}', [PermissionController::class, 'viewRoles'])->middleware(['permission:role.view']);
});

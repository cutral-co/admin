<?php

use App\Http\Controllers\{
    AuthController,
    BarrioController,
    LogController,
    ProvinciaController,
    TestController,
    TributariaController
};

use App\Http\Controllers\DomicilioElectronico\DomicilioElectronicoController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('activate_user', [AuthController::class, 'activate_user']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    /** Domicilio Electrónico */
    Route::post('user/domicilio-electronico/set', [DomicilioElectronicoController::class, 'set_domicilio']);
    Route::post('user/domicilio-electronico/has_new_message', [DomicilioElectronicoController::class, 'has_new_message']);

    Route::post('file', [TestController::class, 'file']);

    Route::group(['middleware' => ['user_verified']], function () {
        Route::post('cambios_datos_usuario', [AuthController::class, 'cambios_datos_usuario']);
    });
});

Route::get('barrios', [BarrioController::class, 'index']);
Route::post('pagar_online_mp', [TributariaController::class, 'pagar_online_mp']);
Route::get('get_preferenicia', [TributariaController::class, 'get_preferenicia']);

Route::get('mp/success', [TributariaController::class, 'success']);

Route::get('provincias', [ProvinciaController::class, 'index']);
Route::post('tributaria', [TributariaController::class, 'index']);

Route::get('logs', [LogController::class, 'index']);
Route::get('logs/{id}', [LogController::class, 'show']);
Route::post('dar_visto', [LogController::class, 'update']);

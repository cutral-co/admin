<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('internal_login', [AuthController::class, 'login'])->name('internal_login');

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('get_user_info', [UserController::class, 'get_user_info']);
    Route::get('get_persona', [UserController::class, 'get_persona']);

    Route::post('file', [TestController::class, 'file']);
});

Route::get('logs', [LogController::class, 'index']);
Route::get('logs/{id}', [LogController::class, 'show']);
Route::post('dar_visto', [LogController::class, 'update']);

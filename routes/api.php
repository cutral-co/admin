<?php

use App\Http\Controllers\{
    AuthController,
    BarrioController,
    Legal\LegalContentController,
    LogController,
    ProvinciaController,
    TestController,
    TributariaController
};

use App\Http\Controllers\DomicilioElectronico\DomicilioElectronicoController;
use App\Http\Controllers\User\SolicitudController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [SolicitudController::class, 'store']);
Route::get('register/token', [SolicitudController::class, 'get_by_token']);
Route::get('test/user-solicitud-email/{type}', [SolicitudController::class, 'testCorreoTemplate']);
Route::get('legal-content/{key}', [LegalContentController::class, 'show'])->where('key', '[A-Za-z0-9\.\-]+');
Route::post('activate_user', [AuthController::class, 'activate_user']);

Route::get('/test/correo', [SolicitudController::class, 'store']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    /** Domicilio Electrónico - Usuario */
    Route::post('user/solicitud/pendientes', [SolicitudController::class, 'pendientes']);
    Route::post('user/solicitud/aprobadas', [SolicitudController::class, 'aprobadas']);
    Route::post('user/solicitud/rechazadas', [SolicitudController::class, 'rechazadas']);
    Route::post('user/solicitud/cambiar-estado', [SolicitudController::class, 'cambiarEstado']);

    Route::post('user/domicilio-electronico/set', [DomicilioElectronicoController::class, 'set_domicilio']);
    Route::post('user/domicilio-electronico/enviar-notificacion', [DomicilioElectronicoController::class, 'enviar_notificacion']);
    Route::post('user/domicilio-electronico/has_new_message', [DomicilioElectronicoController::class, 'has_new_message']);
    Route::get('user/domicilio-electronico/notificaciones', [DomicilioElectronicoController::class, 'notificaciones']);
    Route::post("user/domicilio-electronico/set-view", [DomicilioElectronicoController::class, "set_view"]);
    Route::post("user/domicilio-electronico/set-archivado", [DomicilioElectronicoController::class, "set_archivado"]);

    /** Domicilio Electrónico - Admin/Backoffice */
    Route::get('admin/domicilio-electronico/pendientes', [DomicilioElectronicoController::class, 'getPendientes']);
    Route::post('admin/domicilio-electronico/verificar', [DomicilioElectronicoController::class, 'verificarDomicilio']);
    Route::get('admin/domicilio-electronico/notificaciones', [DomicilioElectronicoController::class, 'getNotificacionesAll']);
    Route::get('admin/domicilio-electronico/domicilios', [DomicilioElectronicoController::class, 'getDomiciliosVerificados']);
    Route::get('admin/domicilio-electronico/buscar-cuit/{cuit}', [DomicilioElectronicoController::class, 'buscarContribuyente']);
    Route::get('admin/domicilio-electronico/origenes', [DomicilioElectronicoController::class, 'getOrigenes']);
    Route::post('admin/domicilio-electronico/enviar', [DomicilioElectronicoController::class, 'enviarNotificacionAdmin']);

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
Route::match(['get', 'post'], 'tributaria/debug-xml', [TributariaController::class, 'debugXML']);

Route::get('logs', [LogController::class, 'index']);
Route::get('logs/{id}', [LogController::class, 'show']);
Route::post('dar_visto', [LogController::class, 'update']);

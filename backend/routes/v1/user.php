<?php

use App\Http\Controllers\VentaController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\SorteoController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes v1
|--------------------------------------------------------------------------
|
| Rutas para usuarios autenticados (no admin)
| Todas requieren token de autenticación y permisos específicos
|
*/

Route::middleware(['auth:sanctum', 'role:usuario,moderador,admin,super_admin'])->prefix('user')->group(function () {
    
    // Perfil de usuario
    Route::prefix('perfil')->group(function () {
        Route::get('/', [UserController::class, 'profile'])
            ->middleware('abilities:perfil.ver');
        Route::put('/actualizar', [UserController::class, 'updateProfile'])
            ->middleware('abilities:perfil.editar');
        Route::get('/actividad', [UserController::class, 'activity'])
            ->middleware('abilities:perfil.ver');
    });
    
    // Ventas de usuario autenticado
    Route::prefix('ventas')->group(function () {
        Route::post('/', [VentaController::class, 'store'])
            ->middleware('abilities:ventas.crear');
        Route::post('/{codigo}/confirmar-pago', [VentaController::class, 'confirmarPago'])
            ->middleware('abilities:ventas.confirmar.pago');
        Route::get('/mis-ventas', [VentaController::class, 'misVentas'])
            ->middleware('abilities:ventas.ver.propias');
    });

    // Favoritos
    Route::prefix('favoritos')->group(function () {
        Route::get('/', [FavoritoController::class, 'index'])
            ->middleware('abilities:perfil.ver');
        Route::post('/toggle', [FavoritoController::class, 'toggle'])
            ->middleware('abilities:perfil.editar');
        Route::get('/verificar/{rifaId}', [FavoritoController::class, 'verificar'])
            ->middleware('abilities:perfil.ver');
        Route::get('/rifas-con-favoritos', [FavoritoController::class, 'rifasConFavoritos'])
            ->middleware('abilities:perfil.ver');
        Route::post('/agregar-multiples', [FavoritoController::class, 'agregarMultiples'])
            ->middleware('abilities:perfil.editar');
        Route::delete('/eliminar-multiples', [FavoritoController::class, 'eliminarMultiples'])
            ->middleware('abilities:perfil.editar');
        Route::delete('/limpiar', [FavoritoController::class, 'limpiar'])
            ->middleware('abilities:perfil.editar');
        Route::get('/estadisticas', [FavoritoController::class, 'estadisticas'])
            ->middleware('abilities:perfil.ver');
    });

    // Comentarios
    Route::prefix('comentarios')->group(function () {
        Route::post('/', [ComentarioController::class, 'store'])
            ->middleware('abilities:perfil.editar');
        Route::put('/{id}', [ComentarioController::class, 'update'])
            ->middleware('abilities:perfil.editar');
        Route::delete('/{id}', [ComentarioController::class, 'destroy'])
            ->middleware('abilities:perfil.editar');
        Route::post('/{id}/reportar', [ComentarioController::class, 'reportar'])
            ->middleware('abilities:perfil.editar');
    });

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::get('/', [NotificacionController::class, 'index'])
            ->middleware('abilities:perfil.ver');
        Route::get('/resumen', [NotificacionController::class, 'resumen'])
            ->middleware('abilities:perfil.ver');
        Route::patch('/{id}/leida', [NotificacionController::class, 'marcarLeida'])
            ->middleware('abilities:perfil.editar');
        Route::patch('/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])
            ->middleware('abilities:perfil.editar');
        Route::patch('/marcar-multiples-leidas', [NotificacionController::class, 'marcarMultiplesLeidas'])
            ->middleware('abilities:perfil.editar');
        Route::delete('/{id}', [NotificacionController::class, 'destroy'])
            ->middleware('abilities:perfil.editar');
        Route::delete('/eliminar-multiples', [NotificacionController::class, 'eliminarMultiples'])
            ->middleware('abilities:perfil.editar');
        Route::delete('/limpiar-antiguas', [NotificacionController::class, 'limpiarAntiguas'])
            ->middleware('abilities:perfil.editar');
        Route::post('/configurar-preferencias', [NotificacionController::class, 'configurarPreferencias'])
            ->middleware('abilities:perfil.editar');
        Route::get('/preferencias', [NotificacionController::class, 'obtenerPreferencias'])
            ->middleware('abilities:perfil.ver');
    });

    // Cupones (para usuarios autenticados)
    Route::prefix('cupones')->group(function () {
        Route::post('/aplicar', [CuponController::class, 'aplicar'])
            ->middleware('abilities:ventas.crear');
    });

    // Sorteos (verificación de ganadores)
    Route::prefix('sorteos')->group(function () {
        Route::get('/{sorteoId}/verificar-ganador', [SorteoController::class, 'verificarGanador'])
            ->middleware('abilities:perfil.ver');
    });

    // Boletos
    Route::prefix('boletos')->group(function () {
        Route::get('/', [BoletoController::class, 'index'])
            ->middleware('abilities:ventas.ver.propias');
        Route::get('/{id}', [BoletoController::class, 'show'])
            ->middleware('abilities:ventas.ver.propias');
        Route::post('/{id}/transferir', [BoletoController::class, 'transferir'])
            ->middleware('abilities:ventas.ver.propias');
        Route::get('/{id}/historial-transferencias', [BoletoController::class, 'historialTransferencias'])
            ->middleware('abilities:ventas.ver.propias');
        Route::get('/rifa/{rifaId}/verificar-estado', [BoletoController::class, 'verificarEstado'])
            ->middleware('abilities:ventas.ver.propias');
    });

    // Pagos
    Route::prefix('pagos')->group(function () {
        Route::get('/', [PagoController::class, 'index'])
            ->middleware('abilities:ventas.ver.propias');
        Route::get('/{id}', [PagoController::class, 'show'])
            ->middleware('abilities:ventas.ver.propias');
        Route::post('/', [PagoController::class, 'store'])
            ->middleware('abilities:ventas.crear');
    });
});
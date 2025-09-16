<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\RifaController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\FavoritoController;
use App\Http\Controllers\ComentarioController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\SorteoController;
use App\Http\Controllers\BoletoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PremioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes v1
|--------------------------------------------------------------------------
|
| Rutas de administración que requieren rol admin/moderador y permisos específicos
| Todas las rutas requieren autenticación con token
|
*/

Route::middleware(['auth:sanctum', 'role:admin,super_admin,moderador'])->prefix('admin')->group(function () {
    
    // Dashboard
    Route::get('/dashboard/stats', [AdminController::class, 'getDashboardStats'])
        ->middleware('abilities:dashboard.ver');
    Route::get('/activity/recent', [AdminController::class, 'getRecentActivity'])
        ->middleware('abilities:actividad.ver');
    
    // Gestión de rifas
    Route::prefix('rifas')->group(function () {
        Route::get('/', [RifaController::class, 'adminIndex'])
            ->middleware('abilities:rifas.ver.admin');
        Route::post('/', [RifaController::class, 'store'])
            ->middleware('abilities:rifas.crear');
        Route::put('/{id}', [RifaController::class, 'update'])
            ->middleware('abilities:rifas.editar');
        Route::delete('/{id}', [RifaController::class, 'destroy'])
            ->middleware('abilities:rifas.eliminar');
        Route::get('/estadisticas', [AdminController::class, 'getRifasStats'])
            ->middleware('abilities:estadisticas.ver');
        Route::patch('/{id}/estado', [RifaController::class, 'changeEstado'])
            ->middleware('abilities:rifas.cambiar.estado');
        Route::get('/exportar', [AdminController::class, 'exportRifas'])
            ->middleware('abilities:rifas.exportar');
    });
    
    // Gestión de usuarios (solo admin y super_admin)
    Route::middleware('role:admin,super_admin')->prefix('usuarios')->group(function () {
        Route::get('/', [AdminController::class, 'getUsuarios'])
            ->middleware('abilities:usuarios.ver');
        Route::post('/', [AdminController::class, 'createUsuario'])
            ->middleware('abilities:usuarios.crear');
        Route::put('/{id}', [AdminController::class, 'updateUsuario'])
            ->middleware('abilities:usuarios.editar');
        Route::delete('/{id}', [AdminController::class, 'deleteUsuario'])
            ->middleware('abilities:usuarios.eliminar');
    });
    
    // Gestión de categorías
    Route::prefix('categorias')->group(function () {
        Route::post('/', [CategoriaController::class, 'store'])
            ->middleware('abilities:categorias.crear');
        Route::put('/{id}', [CategoriaController::class, 'update'])
            ->middleware('abilities:categorias.editar');
        Route::delete('/{id}', [CategoriaController::class, 'destroy'])
            ->middleware('abilities:categorias.eliminar');
    });

    // Gestión de premios
    Route::prefix('premios')->group(function () {
        Route::post('/', [PremioController::class, 'store'])
            ->middleware('abilities:premios.crear');
        Route::put('/{id}', [PremioController::class, 'update'])
            ->middleware('abilities:premios.editar');
        Route::delete('/{id}', [PremioController::class, 'destroy'])
            ->middleware('abilities:premios.eliminar');
    });

    // Gestión de niveles
    Route::prefix('niveles')->group(function () {
        Route::post('/', [NivelController::class, 'store'])
            ->middleware('abilities:niveles.crear');
        Route::put('/{id}', [NivelController::class, 'update'])
            ->middleware('abilities:niveles.editar');
        Route::delete('/{id}', [NivelController::class, 'destroy'])
            ->middleware('abilities:niveles.eliminar');
        Route::patch('/reordenar', [NivelController::class, 'reordenar'])
            ->middleware('abilities:niveles.editar');
        Route::get('/estadisticas', [NivelController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });
    
    // Reportes y ventas
    Route::prefix('ventas')->group(function () {
        Route::get('/', [AdminController::class, 'getVentas'])
            ->middleware('abilities:ventas.ver.admin');
        Route::get('/reportes', [AdminController::class, 'getReportes'])
            ->middleware('abilities:ventas.reportes');
    });

    // Configuraciones (solo admin y super_admin)
    Route::middleware('role:admin,super_admin')->prefix('configuraciones')->group(function () {
        Route::get('/admin', [ConfiguracionController::class, 'admin'])
            ->middleware('abilities:estadisticas.ver');
        Route::post('/', [ConfiguracionController::class, 'store'])
            ->middleware('abilities:rifas.crear'); // Reutilizamos permisos existentes
        Route::put('/{id}', [ConfiguracionController::class, 'update'])
            ->middleware('abilities:rifas.editar');
        Route::patch('/update-batch', [ConfiguracionController::class, 'updateBatch'])
            ->middleware('abilities:rifas.editar');
        Route::patch('/{id}/restore', [ConfiguracionController::class, 'restore'])
            ->middleware('abilities:rifas.editar');
        Route::get('/export', [ConfiguracionController::class, 'export'])
            ->middleware('abilities:rifas.exportar');
        Route::post('/import', [ConfiguracionController::class, 'import'])
            ->middleware('abilities:rifas.crear');
    });

    // Cupones
    Route::prefix('cupones')->group(function () {
        Route::get('/admin', [CuponController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::get('/{id}/admin', [CuponController::class, 'show'])
            ->middleware('abilities:ventas.ver.admin');
        Route::post('/', [CuponController::class, 'store'])
            ->middleware('abilities:rifas.crear');
        Route::put('/{id}', [CuponController::class, 'update'])
            ->middleware('abilities:rifas.editar');
        Route::delete('/{id}', [CuponController::class, 'destroy'])
            ->middleware('abilities:rifas.eliminar');
        Route::patch('/{id}/desactivar', [CuponController::class, 'desactivar'])
            ->middleware('abilities:rifas.editar');
        Route::get('/estadisticas', [CuponController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
        Route::get('/generar-codigo', [CuponController::class, 'generarCodigo'])
            ->middleware('abilities:rifas.crear');
    });

    // Favoritos
    Route::prefix('favoritos')->group(function () {
        Route::get('/admin', [FavoritoController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::get('/estadisticas-admin', [FavoritoController::class, 'estadisticasAdmin'])
            ->middleware('abilities:estadisticas.ver');
        Route::delete('/{id}', [FavoritoController::class, 'destroy'])
            ->middleware('abilities:rifas.editar');
    });

    // Comentarios
    Route::prefix('comentarios')->group(function () {
        Route::get('/admin', [ComentarioController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::patch('/{id}/aprobar', [ComentarioController::class, 'aprobar'])
            ->middleware('abilities:rifas.editar');
        Route::patch('/{id}/rechazar', [ComentarioController::class, 'rechazar'])
            ->middleware('abilities:rifas.editar');
        Route::delete('/{id}/admin', [ComentarioController::class, 'eliminarAdmin'])
            ->middleware('abilities:rifas.eliminar');
        Route::get('/estadisticas', [ComentarioController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::get('/admin', [NotificacionController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::post('/enviar-masiva', [NotificacionController::class, 'enviarMasiva'])
            ->middleware('abilities:rifas.crear'); // Para enviar notificaciones masivas
        Route::get('/estadisticas-admin', [NotificacionController::class, 'estadisticasAdmin'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Sorteos
    Route::prefix('sorteos')->group(function () {
        Route::get('/admin', [SorteoController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::post('/rifa/{rifaId}/ejecutar', [SorteoController::class, 'ejecutar'])
            ->middleware('abilities:rifas.editar');
        Route::post('/rifa/{rifaId}/programar', [SorteoController::class, 'programar'])
            ->middleware('abilities:rifas.editar');
        Route::patch('/{id}/cancelar', [SorteoController::class, 'cancelar'])
            ->middleware('abilities:rifas.editar');
        Route::patch('/{id}/reejecutar', [SorteoController::class, 'reejecutar'])
            ->middleware('abilities:rifas.editar');
        Route::get('/estadisticas', [SorteoController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Boletos
    Route::prefix('boletos')->group(function () {
        Route::get('/admin', [BoletoController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::post('/{id}/forzar-transferencia', [BoletoController::class, 'forzarTransferencia'])
            ->middleware('abilities:ventas.gestionar');
        Route::get('/estadisticas', [BoletoController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Pagos
    Route::prefix('pagos')->group(function () {
        Route::get('/admin', [PagoController::class, 'admin'])
            ->middleware('abilities:ventas.ver.admin');
        Route::patch('/{id}/aprobar', [PagoController::class, 'aprobar'])
            ->middleware('abilities:ventas.gestionar');
        Route::patch('/{id}/rechazar', [PagoController::class, 'rechazar'])
            ->middleware('abilities:ventas.gestionar');
        Route::get('/estadisticas', [PagoController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('/dashboard', [ReporteController::class, 'dashboard'])
            ->middleware('abilities:dashboard.ver');
        Route::get('/ventas', [ReporteController::class, 'ventas'])
            ->middleware('abilities:ventas.reportes');
        Route::get('/rifas', [ReporteController::class, 'rifas'])
            ->middleware('abilities:estadisticas.ver');
        Route::get('/usuarios', [ReporteController::class, 'usuarios'])
            ->middleware('abilities:usuarios.ver');
        Route::get('/financiero', [ReporteController::class, 'financiero'])
            ->middleware('abilities:ventas.reportes');
        Route::post('/exportar', [ReporteController::class, 'exportar'])
            ->middleware('abilities:rifas.exportar');
    });

    // Media/Imágenes
    Route::prefix('media')->group(function () {
        Route::post('/premios/{premioId}/images', [MediaController::class, 'uploadPremioImage'])
            ->middleware('abilities:premios.subir.imagenes');
        Route::delete('/premios/{premioId}/images', [MediaController::class, 'deletePremioImage'])
            ->middleware('abilities:premios.eliminar.imagenes');
        Route::get('/premios/{premioId}/images', [MediaController::class, 'getPremioImages'])
            ->middleware('abilities:media.gestionar');
        Route::put('/premios/{premioId}/images/reorder', [MediaController::class, 'reorderPremioImages'])
            ->middleware('abilities:premios.editar');
    });

    // Upload de imágenes
    Route::prefix('upload')->group(function () {
        Route::post('/rifa-image', [UploadController::class, 'uploadRifaImage'])
            ->middleware('abilities:media.subir');
        Route::post('/premio-image', [UploadController::class, 'uploadPremioImage'])
            ->middleware('abilities:premios.subir.imagenes');
        Route::post('/nivel-image', [UploadController::class, 'uploadNivelImage'])
            ->middleware('abilities:niveles.editar');
        Route::delete('/image', [UploadController::class, 'deleteImage'])
            ->middleware('abilities:media.eliminar');
    });
});
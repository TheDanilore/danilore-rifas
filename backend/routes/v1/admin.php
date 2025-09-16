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
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\VentaController;
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
            ->middleware('abilities:rifas.ver');
        Route::get('/{id}', [RifaController::class, 'show'])
            ->middleware('abilities:rifas.ver');
        Route::post('/', [RifaController::class, 'store'])
            ->middleware('abilities:rifas.crear');
        Route::put('/{id}', [RifaController::class, 'update'])
            ->middleware('abilities:rifas.editar');
        Route::delete('/{id}', [RifaController::class, 'destroy'])
            ->middleware('abilities:rifas.eliminar');
        Route::patch('/{id}/estado', [RifaController::class, 'changeEstado'])
            ->middleware('abilities:rifas.editar');
        Route::get('/estadisticas/general', [RifaController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
        Route::post('/exportar', [RifaController::class, 'exportar'])
            ->middleware('abilities:rifas.ver');
    });
    
    // Gestión de usuarios (completa con nuevo UserController)
    Route::middleware('role:admin,super_admin')->prefix('usuarios')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('abilities:usuarios.ver');
        Route::get('/{id}', [UserController::class, 'show'])
            ->middleware('abilities:usuarios.ver');
        Route::post('/', [UserController::class, 'store'])
            ->middleware('abilities:usuarios.crear');
        Route::put('/{id}', [UserController::class, 'update'])
            ->middleware('abilities:usuarios.editar');
        Route::delete('/{id}', [UserController::class, 'destroy'])
            ->middleware('abilities:usuarios.eliminar');
        Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])
            ->middleware('abilities:usuarios.editar');
        Route::get('/estadisticas/general', [UserController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Gestión de roles (solo super_admin)
    Route::middleware('role:super_admin')->prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('abilities:roles.gestionar');
        Route::get('/{id}', [RoleController::class, 'show'])
            ->middleware('abilities:roles.gestionar');
        Route::post('/', [RoleController::class, 'store'])
            ->middleware('abilities:roles.gestionar');
        Route::put('/{id}', [RoleController::class, 'update'])
            ->middleware('abilities:roles.gestionar');
        Route::delete('/{id}', [RoleController::class, 'destroy'])
            ->middleware('abilities:roles.gestionar');
        Route::post('/{id}/assign-permissions', [RoleController::class, 'assignPermissions'])
            ->middleware('abilities:roles.gestionar');
        Route::delete('/{id}/revoke-permissions', [RoleController::class, 'revokePermissions'])
            ->middleware('abilities:roles.gestionar');
        Route::get('/{id}/users', [RoleController::class, 'users'])
            ->middleware('abilities:roles.gestionar');
        Route::get('/estadisticas/general', [RoleController::class, 'estadisticas'])
            ->middleware('abilities:roles.gestionar');
    });

    // Gestión de permisos (solo super_admin)
    Route::middleware('role:super_admin')->prefix('permisos')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])
            ->middleware('abilities:permisos.gestionar');
        Route::get('/{id}', [PermissionController::class, 'show'])
            ->middleware('abilities:permisos.gestionar');
        Route::post('/', [PermissionController::class, 'store'])
            ->middleware('abilities:permisos.gestionar');
        Route::put('/{id}', [PermissionController::class, 'update'])
            ->middleware('abilities:permisos.gestionar');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])
            ->middleware('abilities:permisos.gestionar');
        Route::get('/{id}/roles', [PermissionController::class, 'roles'])
            ->middleware('abilities:permisos.gestionar');
        Route::get('/categoria/todos', [PermissionController::class, 'porCategoria'])
            ->middleware('abilities:permisos.gestionar');
        Route::post('/sincronizar', [PermissionController::class, 'sincronizar'])
            ->middleware('abilities:permisos.gestionar');
        Route::get('/estadisticas/general', [PermissionController::class, 'estadisticas'])
            ->middleware('abilities:permisos.gestionar');
    });
    
    // Gestión de categorías
    Route::prefix('categorias')->group(function () {
        Route::get('/', [CategoriaController::class, 'index'])
            ->middleware('abilities:categorias.ver');
        Route::post('/', [CategoriaController::class, 'store'])
            ->middleware('abilities:categorias.crear');
        Route::put('/{id}', [CategoriaController::class, 'update'])
            ->middleware('abilities:categorias.editar');
        Route::delete('/{id}', [CategoriaController::class, 'destroy'])
            ->middleware('abilities:categorias.eliminar');
    });

    // Gestión de premios
    Route::prefix('premios')->group(function () {
        Route::get('/', [PremioController::class, 'index'])
            ->middleware('abilities:premios.ver');
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
        Route::get('/', [VentaController::class, 'index'])
            ->middleware('abilities:ventas.ver.admin');
        Route::put('/{id}', [VentaController::class, 'update'])
            ->middleware('abilities:ventas.gestionar');
        Route::delete('/{id}', [VentaController::class, 'destroy'])
            ->middleware('abilities:ventas.eliminar');
        Route::get('/estadisticas', [VentaController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
        Route::get('/reportes', [AdminController::class, 'getReportes'])
            ->middleware('abilities:ventas.reportes');
    });

    // Configuraciones (solo admin y super_admin)
    Route::middleware('role:admin,super_admin')->prefix('configuraciones')->group(function () {
        Route::get('/admin', [ConfiguracionController::class, 'admin'])
            ->middleware('abilities:configuracion.editar');
        Route::put('/{clave}', [ConfiguracionController::class, 'update'])
            ->middleware('abilities:configuracion.editar');
        Route::patch('/update-batch', [ConfiguracionController::class, 'updateBatch'])
            ->middleware('abilities:configuracion.editar');
        Route::patch('/{clave}/restore', [ConfiguracionController::class, 'restore'])
            ->middleware('abilities:configuracion.editar');
        Route::get('/export', [ConfiguracionController::class, 'export'])
            ->middleware('abilities:configuracion.editar');
        Route::post('/import', [ConfiguracionController::class, 'import'])
            ->middleware('abilities:configuracion.editar');
    });

    // Cupones
    Route::prefix('cupones')->group(function () {
        Route::get('/admin', [CuponController::class, 'admin'])
            ->middleware('abilities:cupones.ver');
        Route::get('/{id}/admin', [CuponController::class, 'show'])
            ->middleware('abilities:cupones.ver');
        Route::post('/', [CuponController::class, 'store'])
            ->middleware('abilities:cupones.crear');
        Route::put('/{id}', [CuponController::class, 'update'])
            ->middleware('abilities:cupones.editar');
        Route::delete('/{id}', [CuponController::class, 'destroy'])
            ->middleware('abilities:cupones.eliminar');
        Route::patch('/{id}/desactivar', [CuponController::class, 'desactivar'])
            ->middleware('abilities:cupones.editar');
        Route::get('/estadisticas', [CuponController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
        Route::get('/generar-codigo', [CuponController::class, 'generarCodigo'])
            ->middleware('abilities:cupones.crear');
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
            ->middleware('abilities:comentarios.ver');
        Route::patch('/{id}/aprobar', [ComentarioController::class, 'aprobar'])
            ->middleware('abilities:comentarios.moderar');
        Route::patch('/{id}/rechazar', [ComentarioController::class, 'rechazar'])
            ->middleware('abilities:comentarios.moderar');
        Route::delete('/{id}/admin', [ComentarioController::class, 'eliminarAdmin'])
            ->middleware('abilities:comentarios.eliminar');
        Route::get('/estadisticas', [ComentarioController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Notificaciones
    Route::prefix('notificaciones')->group(function () {
        Route::get('/admin', [NotificacionController::class, 'admin'])
            ->middleware('abilities:notificaciones.ver');
        Route::post('/enviar-masiva', [NotificacionController::class, 'enviarMasiva'])
            ->middleware('abilities:notificaciones.enviar.masiva');
        Route::get('/estadisticas-admin', [NotificacionController::class, 'estadisticasAdmin'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Sorteos
    Route::prefix('sorteos')->group(function () {
        Route::get('/admin', [SorteoController::class, 'admin'])
            ->middleware('abilities:sorteos.ver');
        Route::post('/rifa/{rifaId}/ejecutar', [SorteoController::class, 'ejecutar'])
            ->middleware('abilities:sorteos.ejecutar');
        Route::post('/rifa/{rifaId}/programar', [SorteoController::class, 'programar'])
            ->middleware('abilities:sorteos.programar');
        Route::patch('/{id}/cancelar', [SorteoController::class, 'cancelar'])
            ->middleware('abilities:sorteos.ejecutar');
        Route::patch('/{id}/reejecutar', [SorteoController::class, 'reejecutar'])
            ->middleware('abilities:sorteos.ejecutar');
        Route::get('/estadisticas', [SorteoController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Boletos
    Route::prefix('boletos')->group(function () {
        Route::get('/admin', [BoletoController::class, 'admin'])
            ->middleware('abilities:boletos.ver');
        Route::post('/{id}/forzar-transferencia', [BoletoController::class, 'forzarTransferencia'])
            ->middleware('abilities:boletos.gestionar');
        Route::get('/estadisticas', [BoletoController::class, 'estadisticas'])
            ->middleware('abilities:estadisticas.ver');
    });

    // Pagos
    Route::prefix('pagos')->group(function () {
        Route::get('/admin', [PagoController::class, 'admin'])
            ->middleware('abilities:pagos.ver');
        Route::patch('/{id}/aprobar', [PagoController::class, 'aprobar'])
            ->middleware('abilities:pagos.aprobar');
        Route::patch('/{id}/rechazar', [PagoController::class, 'rechazar'])
            ->middleware('abilities:pagos.rechazar');
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
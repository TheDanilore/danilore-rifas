<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RifaController;
use App\Http\Controllers\Api\PremioController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\UploadController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas
Route::prefix('v1')->group(function () {
    
    // Autenticación
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Rifas públicas
    Route::prefix('rifas')->group(function () {
        Route::get('/', [RifaController::class, 'index']);
        Route::get('/actuales', [RifaController::class, 'actuales']);
        Route::get('/futuras', [RifaController::class, 'futuras']);
        Route::get('/destacadas', [RifaController::class, 'destacadas']);
        Route::get('/{codigo}', [RifaController::class, 'show']);
        Route::get('/{codigo}/progreso', [RifaController::class, 'progreso']);
    });

    // Categorías públicas
    Route::get('/categorias', [CategoriaController::class, 'index']);

    // Premios públicos
    Route::get('/premios/{rifaId}/{codigoPremio}', [PremioController::class, 'show']);

    // Ventas públicas (consulta por código)
    Route::get('/ventas/{codigo}', [VentaController::class, 'show']);
});

// Rutas protegidas (requieren autenticación)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Perfil de usuario
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // Ventas de usuario autenticado
    Route::prefix('ventas')->group(function () {
        Route::post('/', [VentaController::class, 'store']);
        Route::post('/{codigo}/confirmar-pago', [VentaController::class, 'confirmarPago']);
        Route::get('/mis-ventas', [VentaController::class, 'misVentas']);
    });

    // Media/Imágenes (admin only)
    Route::prefix('media')->group(function () {
        Route::post('/premios/{premioId}/images', [MediaController::class, 'uploadPremioImage']);
        Route::delete('/premios/{premioId}/images', [MediaController::class, 'deletePremioImage']);
        Route::get('/premios/{premioId}/images', [MediaController::class, 'getPremioImages']);
        Route::put('/premios/{premioId}/images/reorder', [MediaController::class, 'reorderPremioImages']);
    });

    // Upload de imágenes (admin only)
    Route::prefix('upload')->group(function () {
        Route::post('/rifa-image', [UploadController::class, 'uploadRifaImage']);
        Route::post('/premio-image', [UploadController::class, 'uploadPremioImage']);
        Route::post('/nivel-image', [UploadController::class, 'uploadNivelImage']);
        Route::delete('/image', [UploadController::class, 'deleteImage']);
    });

    // Rutas de administración (solo para admins)
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/dashboard/stats', [AdminController::class, 'getDashboardStats']);
        Route::get('/activity/recent', [AdminController::class, 'getRecentActivity']);
        
        // Gestión de rifas
        Route::prefix('rifas')->group(function () {
            Route::get('/', [RifaController::class, 'adminIndex']);
            Route::post('/', [RifaController::class, 'store']);
            Route::put('/{id}', [RifaController::class, 'update']);
            Route::delete('/{id}', [RifaController::class, 'destroy']);
            Route::get('/estadisticas', [AdminController::class, 'getRifasStats']);
            Route::patch('/{id}/estado', [RifaController::class, 'changeEstado']);
            Route::get('/exportar', [AdminController::class, 'exportRifas']);
        });
        
        // Gestión de usuarios
        Route::prefix('usuarios')->group(function () {
            Route::get('/', [AdminController::class, 'getUsuarios']);
            Route::post('/', [AdminController::class, 'createUsuario']);
            Route::put('/{id}', [AdminController::class, 'updateUsuario']);
            Route::delete('/{id}', [AdminController::class, 'deleteUsuario']);
        });
        
        // Reportes y ventas
        Route::prefix('ventas')->group(function () {
            Route::get('/', [AdminController::class, 'getVentas']);
            Route::get('/reportes', [AdminController::class, 'getReportes']);
        });
    });
});

// Ruta para obtener información del usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

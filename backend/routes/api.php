<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RifaController;
use App\Http\Controllers\Api\PremioController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\MediaController;

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
});

// Ruta para obtener información del usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

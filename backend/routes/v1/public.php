<?php

use App\Http\Controllers\RifaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PremioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\SorteoController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\ComentarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes v1
|--------------------------------------------------------------------------
|
| Rutas públicas que no requieren autenticación
|
*/

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
Route::get('/premios/{rifaCodigoUnico}/{codigoPremio}', [PremioController::class, 'show']);

// Ventas públicas (consulta por código)
Route::get('/ventas/{codigo}', [VentaController::class, 'show']);

// Configuraciones públicas
Route::prefix('configuraciones')->group(function () {
    Route::get('/', [ConfiguracionController::class, 'index']);
    Route::get('/categoria/{categoria}', [ConfiguracionController::class, 'porCategoria']);
    Route::get('/{clave}', [ConfiguracionController::class, 'show']);
    Route::get('/{clave}/valor', [ConfiguracionController::class, 'valor']);
});

// Cupones públicos
Route::prefix('cupones')->group(function () {
    Route::get('/', [CuponController::class, 'index']);
    Route::post('/validar', [CuponController::class, 'validar']);
});

// Sorteos públicos
Route::prefix('sorteos')->group(function () {
    Route::get('/', [SorteoController::class, 'index']);
    Route::get('/{id}', [SorteoController::class, 'show']);
});

// Niveles públicos
Route::prefix('niveles')->group(function () {
    Route::get('/', [NivelController::class, 'index']);
    Route::get('/{id}', [NivelController::class, 'show']);
});

// Comentarios públicos (por rifa)
Route::get('/rifas/{rifaId}/comentarios', [ComentarioController::class, 'index']);
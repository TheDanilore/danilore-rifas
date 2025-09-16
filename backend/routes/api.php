<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// API v1 Routes
Route::prefix('v1')->group(function () {
    
    // Autenticación
    require __DIR__.'/v1/auth.php';
    
    // Rutas públicas
    require __DIR__.'/v1/public.php';
    
    // Rutas para usuarios autenticados (no admin)
    require __DIR__.'/v1/user.php';
    
    // Rutas de administración (requieren rol admin/moderador)
    require __DIR__.'/v1/admin.php';
});


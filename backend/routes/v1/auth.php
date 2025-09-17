<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes v1
|--------------------------------------------------------------------------
|
| Rutas de autenticación con Personal Access Tokens
| Incluye gestión de tokens, roles y permisos
|
*/

Route::prefix('auth')->group(function () {
    
    // Rutas públicas (sin autenticación)
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');

    // Rutas protegidas (requieren token)
    Route::middleware('auth:sanctum')->group(function () {
        
        // Obtener información del usuario autenticado
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
        Route::get('/user', [AuthController::class, 'me'])->name('auth.user'); // Alias para compatibilidad
        
        // Actualizar perfil
        Route::put('/profile', [AuthController::class, 'updateProfile'])->name('auth.update-profile');
        
        // Cambiar contraseña
        Route::put('/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
        
        // Cerrar sesión (revocar token actual)
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        
        // Cerrar sesión de todos los dispositivos (revocar todos los tokens)
        Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
        
        // Obtener tokens del usuario autenticado
        Route::get('/tokens', [AuthController::class, 'getTokens'])->name('auth.tokens');
        
        // Revocar un token específico
        Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken'])->name('auth.revoke-token');
    });
});
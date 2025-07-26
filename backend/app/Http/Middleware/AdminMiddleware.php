<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado con Sanctum
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        $user = $request->user();

        // Verificar que el usuario sea administrador
        if ($user->rol !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos de administrador'
            ], 403);
        }

        // Verificar que el usuario esté activo
        if (!$user->activo) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está desactivada'
            ], 403);
        }

        return $next($request);
    }
}

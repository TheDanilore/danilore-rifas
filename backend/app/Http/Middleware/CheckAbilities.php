<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAbilities
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $token = $user->currentAccessToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido'
            ], 401);
        }

        // Si no se especifican abilities, permitir acceso
        if (empty($abilities)) {
            return $next($request);
        }

        // Verificar si el token tiene al menos una de las abilities requeridas
        foreach ($abilities as $ability) {
            if ($token->can($ability)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No tiene permisos suficientes para realizar esta acción',
            'required_abilities' => $abilities,
            'user_abilities' => $token->abilities ?? []
        ], 403);
    }
}
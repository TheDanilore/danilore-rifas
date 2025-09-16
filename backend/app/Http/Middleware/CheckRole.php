<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        // Si no se especifican roles, permitir acceso
        if (empty($roles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene uno de los roles requeridos usando Spatie Permission
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        return response()->json([
            'success' => false,
            'message' => 'No tiene el rol necesario para acceder a este recurso',
            'required_roles' => $roles,
            'user_roles' => $user->getRoleNames()->toArray()
        ], 403);
    }
}
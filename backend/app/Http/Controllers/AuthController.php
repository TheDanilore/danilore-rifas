<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * AuthController
 * 
 * Maneja la autenticación de usuarios con Personal Access Tokens
 * Incluye registro, login, logout y gestión de tokens
 */
class AuthController extends Controller
{
    /**
     * Registrar nuevo usuario
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombres' => 'required|string|max:100',
                'apellidos' => 'required|string|max:100',
                'telefono' => 'required|string|max:20|unique:users,telefono', // Permitir más caracteres para códigos de país
                'email' => 'sometimes|nullable|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'tipo_documento' => 'required|string|in:dni,ce,passport,ruc,otros',
                'numero_documento' => 'required|string|max:20|unique:users,numero_documento',
                'fecha_nacimiento' => 'required|date|before:today',
                'genero' => 'required|string|in:masculino,femenino,otro,no_especificar',
                'direccion' => 'sometimes|nullable|string|max:500',
                'ciudad' => 'sometimes|nullable|string|max:100',
                'departamento' => 'sometimes|nullable|string|max:100',
                'codigo_postal' => 'sometimes|nullable|string|max:10',
                'pais' => 'sometimes|string|max:3',
                'device_name' => 'sometimes|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Combinar nombres y apellidos para el campo name
            $fullName = trim($request->nombres . ' ' . $request->apellidos);

            $user = User::create([
                'name' => $fullName,
                'nombre' => $request->nombres,
                'apellido' => $request->apellidos,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $request->telefono, // Ya viene con código de país desde frontend
                'tipo_documento' => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'genero' => $request->genero,
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'departamento' => $request->departamento,
                'codigo_postal' => $request->codigo_postal,
                'pais' => $request->pais ?? 'PE',
                'activo' => true,
                'ultimo_acceso' => now()
            ]);

            // Asignar rol por defecto usando Spatie Permission
            $user->assignRole('usuario');

            // Crear token con abilities según el rol
            $abilities = $this->getUserAbilities($user->getRoleNames()->first() ?? 'usuario');
            $deviceName = $request->device_name ?? 'Registration Token';
            $token = $user->createToken($deviceName, $abilities);

            Log::info('Usuario registrado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado correctamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'role' => $user->getRoleNames()->first(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'created_at' => $user->created_at
                    ],
                    'token' => [
                        'access_token' => $token->plainTextToken,
                        'token_type' => 'Bearer',
                        'expires_in' => null,
                        'abilities' => $abilities
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error en registro de usuario', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login de usuario
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string', // Cambiado para permitir email o teléfono
                'password' => 'required|string',
                'device_name' => 'sometimes|string|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales requeridas',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Determinar si es email o teléfono
            $loginField = $request->email;
            $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
            
            // Buscar usuario por email o teléfono
            $user = null;
            if ($isEmail) {
                $user = User::where('email', $loginField)->first();
            } else {
                // Buscar por teléfono (puede estar en campo 'telefono')
                $user = User::where('telefono', $loginField)->first();
            }

            if (!$user || !Hash::check($request->password, $user->password)) {
                Log::warning('Intento de login fallido', [
                    'login_field' => $loginField,
                    'is_email' => $isEmail,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            // Verificar que el usuario esté activo
            if (!$user->activo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario desactivado'
                ], 403);
            }

            // Crear nuevo token con abilities según rol
            $userRole = $user->getRoleNames()->first() ?? 'usuario';
            $abilities = $this->getUserAbilities($userRole);
            $deviceName = $request->device_name ?? 'API Client';
            $token = $user->createToken($deviceName, $abilities);

            // Actualizar último acceso
            $user->update(['ultimo_acceso' => now()]);

            Log::info('Usuario autenticado exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $userRole,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'role' => $userRole,
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'ultimo_acceso' => $user->ultimo_acceso
                    ],
                    'token' => [
                        'access_token' => $token->plainTextToken,
                        'token_type' => 'Bearer',
                        'expires_in' => null,
                        'abilities' => $abilities
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en login', [
                'email' => $request->email ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentToken = $request->user()->currentAccessToken();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'role' => $user->getRoleNames()->first(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'ultimo_acceso' => $user->ultimo_acceso,
                        'created_at' => $user->created_at
                    ],
                    'token_info' => [
                        'name' => $currentToken->name,
                        'abilities' => $currentToken->abilities,
                        'created_at' => $currentToken->created_at,
                        'last_used_at' => $currentToken->last_used_at
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del usuario'
            ], 500);
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentToken = $request->user()->currentAccessToken();

            // Revocar solo el token actual
            $currentToken->delete();

            Log::info('Usuario cerró sesión', [
                'user_id' => $user->id,
                'email' => $user->email,
                'token_name' => $currentToken->name,
                'logout_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso',
                'data' => [
                    'logout_at' => now()->toIsoString(),
                    'token_revoked' => $currentToken->name
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }

    /**
     * Cerrar todas las sesiones (revocar todos los tokens)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokensCount = $user->tokens()->count();

            // Revocar todos los tokens del usuario
            $user->tokens()->delete();

            Log::info('Usuario cerró todas las sesiones', [
                'user_id' => $user->id,
                'email' => $user->email,
                'tokens_revoked' => $tokensCount,
                'logout_all_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Todas las sesiones han sido cerradas',
                'data' => [
                    'logout_all_at' => now()->toIsoString(),
                    'tokens_revoked' => $tokensCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar todas las sesiones'
            ], 500);
        }
    }

    /**
     * Listar tokens activos del usuario
     */
    public function tokens(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentTokenId = $request->user()->currentAccessToken()->id;

            $tokens = $user->tokens()->get()->map(function ($token) use ($currentTokenId) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities ?? [],
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                    'is_current' => $token->id === $currentTokenId
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'tokens' => $tokens,
                    'count' => $tokens->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener tokens', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? 'unknown'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revocar token específico
     */
    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        try {
            $user = $request->user();
            $token = $user->tokens()->where('id', $tokenId)->first();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no encontrado'
                ], 404);
            }

            $tokenName = $token->name;
            $token->delete();

            Log::info('Token revocado', [
                'user_id' => $user->id,
                'token_id' => $tokenId,
                'token_name' => $tokenName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Token revocado exitosamente',
                'data' => [
                    'revoked_token_id' => $tokenId,
                    'revoked_token_name' => $tokenName
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al revocar token', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id ?? 'unknown',
                'token_id' => $tokenId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al revocar token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:15',
            'fecha_nacimiento' => 'sometimes|date',
            'genero' => 'sometimes|in:masculino,femenino,otro',
            'direccion' => 'sometimes|string|max:255',
            'ciudad' => 'sometimes|string|max:100',
            'departamento' => 'sometimes|string|max:100',
            'codigo_postal' => 'sometimes|string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->update($request->only([
                'name', 'telefono', 'fecha_nacimiento', 'genero',
                'direccion', 'ciudad', 'departamento', 'codigo_postal'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener abilities según el rol del usuario
     */
    private function getUserAbilities(string $role): array
    {
        // Retornamos los permisos definidos en el seeder de Spatie Permission
        // Esto asegura consistencia con el sistema de permisos
        
        switch ($role) {
            case 'super_admin':
                return [
                    // Super admin tiene todos los permisos
                    'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
                    'rifas.ver.admin', 'rifas.crear', 'rifas.editar', 'rifas.eliminar', 'rifas.cambiar.estado', 'rifas.exportar',
                    'ventas.ver.admin', 'ventas.reportes', 'ventas.gestionar',
                    'premios.crear', 'premios.editar', 'premios.eliminar', 'premios.subir.imagenes', 'premios.eliminar.imagenes',
                    'categorias.crear', 'categorias.editar', 'categorias.eliminar',
                    'niveles.crear', 'niveles.editar', 'niveles.eliminar',
                    'dashboard.ver', 'estadisticas.ver', 'actividad.ver',
                    'media.subir', 'media.eliminar', 'media.gestionar',
                    'ventas.crear', 'ventas.confirmar.pago', 'ventas.ver.propias',
                    'perfil.ver', 'perfil.editar'
                ];
                
            case 'admin':
                return [
                    'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',
                    'rifas.ver.admin', 'rifas.crear', 'rifas.editar', 'rifas.eliminar', 'rifas.cambiar.estado', 'rifas.exportar',
                    'ventas.ver.admin', 'ventas.reportes', 'ventas.gestionar',
                    'premios.crear', 'premios.editar', 'premios.eliminar', 'premios.subir.imagenes', 'premios.eliminar.imagenes',
                    'categorias.crear', 'categorias.editar', 'categorias.eliminar',
                    'niveles.crear', 'niveles.editar', 'niveles.eliminar',
                    'dashboard.ver', 'estadisticas.ver', 'actividad.ver',
                    'media.subir', 'media.eliminar', 'media.gestionar',
                    'perfil.ver', 'perfil.editar'
                ];
            
            case 'moderador':
                return [
                    'rifas.ver.admin', 'rifas.editar', 'rifas.cambiar.estado',
                    'ventas.ver.admin', 'ventas.gestionar',
                    'premios.editar', 'premios.subir.imagenes',
                    'dashboard.ver', 'estadisticas.ver', 'actividad.ver',
                    'media.subir',
                    'perfil.ver', 'perfil.editar'
                ];
            
            case 'usuario':
            default:
                return [
                    'ventas.crear', 'ventas.confirmar.pago', 'ventas.ver.propias',
                    'perfil.ver', 'perfil.editar'
                ];
        }
    }
}

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
            // Log de datos recibidos para debugging
            Log::info('Datos recibidos en register:', [
                'all_data' => $request->all(),
                'telefono' => $request->get('telefono'),
                'pais' => $request->get('pais'),
                'ip' => $request->ip()
            ]);

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
                'device_name' => 'sometimes|string|max:255',
                // Nuevos campos de términos y preferencias
                'accept_terms' => 'required|boolean',
                'accept_marketing' => 'sometimes|boolean',
                'preferencias_notificacion' => 'sometimes|array'
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
            
            // Formatear teléfono con código de país
            $telefonoCompleto = $this->formatearTelefonoConPais($request->telefono, $request->pais ?? 'PE');

            $user = User::create([
                'name' => $fullName,
                'nombre' => $request->nombres,
                'apellido' => $request->apellidos,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telefono' => $telefonoCompleto,
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
                // Guardar preferencias de notificación como JSON
                'preferencias_notificacion' => $request->preferencias_notificacion ?? [
                    'email_promociones' => $request->accept_marketing ?? false,
                    'email_rifas' => $request->accept_marketing ?? false,
                    'push_promociones' => $request->accept_marketing ?? false,
                    'sms_promociones' => false
                ],
                // Términos y condiciones
                'acepta_terminos' => $request->accept_terms ?? false,
                'fecha_aceptacion_terminos' => $request->accept_terms ? now() : null,
                'acepta_marketing' => $request->accept_marketing ?? false,
                'fecha_aceptacion_marketing' => $request->accept_marketing ? now() : null,
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
                        'nombre' => $user->nombre,
                        'apellido' => $user->apellido,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'tipo_documento' => $user->tipo_documento,
                        'numero_documento' => $user->numero_documento,
                        'fecha_nacimiento' => $user->fecha_nacimiento,
                        'genero' => $user->genero,
                        'direccion' => $user->direccion,
                        'ciudad' => $user->ciudad,
                        'departamento' => $user->departamento,
                        'codigo_postal' => $user->codigo_postal,
                        'pais' => $user->pais,
                        'avatar' => $user->avatar,
                        'zona_horaria' => $user->zona_horaria,
                        'preferencias_notificacion' => $user->preferencias_notificacion,
                        'total_boletos_comprados' => $user->total_boletos_comprados,
                        'total_gastado' => $user->total_gastado,
                        'total_rifas_participadas' => $user->total_rifas_participadas,
                        'rifas_ganadas' => $user->rifas_ganadas,
                        'primera_compra' => $user->primera_compra,
                        'ultima_compra' => $user->ultima_compra,
                        'role' => $user->getRoleNames()->first(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'verificado' => $user->verificado,
                        'ultimo_acceso' => $user->ultimo_acceso,
                        'doble_autenticacion' => $user->doble_autenticacion,
                        'acepta_terminos' => $user->acepta_terminos,
                        'fecha_aceptacion_terminos' => $user->fecha_aceptacion_terminos,
                        'acepta_marketing' => $user->acepta_marketing,
                        'fecha_aceptacion_marketing' => $user->fecha_aceptacion_marketing,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
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
                'identifier' => 'required|string', // ✅ Cambiado de 'email' a 'identifier' 
                'password' => 'required|string',
                'pais' => 'sometimes|string|max:3',
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
            $loginField = $request->identifier; // ✅ Cambiado de $request->email a $request->identifier
            $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
            
            // Buscar usuario por email o teléfono
            $user = null;
            if ($isEmail) {
                $user = User::where('email', $loginField)->first();
            } else {
                // Formatear teléfono con código de país para búsqueda consistente
                $telefonoFormateado = $this->formatearTelefonoConPais($loginField, $request->pais ?? 'PE');
                
                // Buscar por teléfono (tanto el formato original como el formateado)
                $user = User::where('telefono', $loginField)
                          ->orWhere('telefono', $telefonoFormateado)
                          ->first();
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
                        'nombre' => $user->nombre,
                        'apellido' => $user->apellido,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'tipo_documento' => $user->tipo_documento,
                        'numero_documento' => $user->numero_documento,
                        'fecha_nacimiento' => $user->fecha_nacimiento,
                        'genero' => $user->genero,
                        'direccion' => $user->direccion,
                        'ciudad' => $user->ciudad,
                        'departamento' => $user->departamento,
                        'codigo_postal' => $user->codigo_postal,
                        'pais' => $user->pais,
                        'avatar' => $user->avatar,
                        'zona_horaria' => $user->zona_horaria,
                        'preferencias_notificacion' => $user->preferencias_notificacion,
                        'total_boletos_comprados' => $user->total_boletos_comprados,
                        'total_gastado' => $user->total_gastado,
                        'total_rifas_participadas' => $user->total_rifas_participadas,
                        'rifas_ganadas' => $user->rifas_ganadas,
                        'primera_compra' => $user->primera_compra,
                        'ultima_compra' => $user->ultima_compra,
                        'role' => $userRole,
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'verificado' => $user->verificado,
                        'ultimo_acceso' => $user->ultimo_acceso,
                        'doble_autenticacion' => $user->doble_autenticacion,
                        'acepta_terminos' => $user->acepta_terminos,
                        'fecha_aceptacion_terminos' => $user->fecha_aceptacion_terminos,
                        'acepta_marketing' => $user->acepta_marketing,
                        'fecha_aceptacion_marketing' => $user->fecha_aceptacion_marketing,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
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
                'identifier' => $request->identifier ?? 'unknown', // ✅ Cambiado de 'email' a 'identifier'
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
                        'nombre' => $user->nombre,
                        'apellido' => $user->apellido,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'tipo_documento' => $user->tipo_documento,
                        'numero_documento' => $user->numero_documento,
                        'fecha_nacimiento' => $user->fecha_nacimiento,
                        'genero' => $user->genero,
                        'direccion' => $user->direccion,
                        'ciudad' => $user->ciudad,
                        'departamento' => $user->departamento,
                        'codigo_postal' => $user->codigo_postal,
                        'pais' => $user->pais,
                        'avatar' => $user->avatar,
                        'zona_horaria' => $user->zona_horaria,
                        'preferencias_notificacion' => $user->preferencias_notificacion,
                        'total_boletos_comprados' => $user->total_boletos_comprados,
                        'total_gastado' => $user->total_gastado,
                        'total_rifas_participadas' => $user->total_rifas_participadas,
                        'rifas_ganadas' => $user->rifas_ganadas,
                        'primera_compra' => $user->primera_compra,
                        'ultima_compra' => $user->ultima_compra,
                        'role' => $user->getRoleNames()->first(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'verificado' => $user->verificado,
                        'ultimo_acceso' => $user->ultimo_acceso,
                        'doble_autenticacion' => $user->doble_autenticacion,
                        'acepta_terminos' => $user->acepta_terminos,
                        'fecha_aceptacion_terminos' => $user->fecha_aceptacion_terminos,
                        'acepta_marketing' => $user->acepta_marketing,
                        'fecha_aceptacion_marketing' => $user->fecha_aceptacion_marketing,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
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
     * Actualizar perfil
     */
    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'telefono' => 'sometimes|string|max:20|unique:users,telefono,' . $user->id,
            'fecha_nacimiento' => 'sometimes|date|before:today',
            'genero' => 'sometimes|in:masculino,femenino,otro,no_especificar',
            'direccion' => 'sometimes|string|max:500',
            'ciudad' => 'sometimes|string|max:100',
            'departamento' => 'sometimes|string|max:100',
            'codigo_postal' => 'sometimes|string|max:10',
            'pais' => 'sometimes|string|max:3',
            'avatar' => 'sometimes|string|max:255',
            'zona_horaria' => 'sometimes|string|max:50',
            'preferencias_notificacion' => 'sometimes|array',
            'acepta_marketing' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Actualizar nombre completo si se proporcionan nombre y apellido
            $updateData = $request->only([
                'nombre', 'apellido', 'email', 'telefono', 'fecha_nacimiento', 'genero',
                'direccion', 'ciudad', 'departamento', 'codigo_postal', 'pais',
                'avatar', 'zona_horaria', 'preferencias_notificacion', 'acepta_marketing'
            ]);

            // Si se actualizan nombre o apellido, actualizar también el campo name
            if ($request->has('nombre') || $request->has('apellido')) {
                $nombre = $request->get('nombre', $user->nombre);
                $apellido = $request->get('apellido', $user->apellido);
                $updateData['name'] = trim($nombre . ' ' . $apellido);
            } elseif ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            // Actualizar fecha de aceptación de marketing si cambia
            if ($request->has('acepta_marketing')) {
                $updateData['fecha_aceptacion_marketing'] = $request->acepta_marketing ? now() : null;
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado correctamente',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'nombre' => $user->nombre,
                        'apellido' => $user->apellido,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'tipo_documento' => $user->tipo_documento,
                        'numero_documento' => $user->numero_documento,
                        'fecha_nacimiento' => $user->fecha_nacimiento,
                        'genero' => $user->genero,
                        'direccion' => $user->direccion,
                        'ciudad' => $user->ciudad,
                        'departamento' => $user->departamento,
                        'codigo_postal' => $user->codigo_postal,
                        'pais' => $user->pais,
                        'avatar' => $user->avatar,
                        'zona_horaria' => $user->zona_horaria,
                        'preferencias_notificacion' => $user->preferencias_notificacion,
                        'total_boletos_comprados' => $user->total_boletos_comprados,
                        'total_gastado' => $user->total_gastado,
                        'total_rifas_participadas' => $user->total_rifas_participadas,
                        'rifas_ganadas' => $user->rifas_ganadas,
                        'primera_compra' => $user->primera_compra,
                        'ultima_compra' => $user->ultima_compra,
                        'role' => $user->getRoleNames()->first(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'activo' => $user->activo,
                        'verificado' => $user->verificado,
                        'ultimo_acceso' => $user->ultimo_acceso,
                        'doble_autenticacion' => $user->doble_autenticacion,
                        'acepta_terminos' => $user->acepta_terminos,
                        'fecha_aceptacion_terminos' => $user->fecha_aceptacion_terminos,
                        'acepta_marketing' => $user->acepta_marketing,
                        'fecha_aceptacion_marketing' => $user->fecha_aceptacion_marketing,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ]
                ]
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

    /**
     * Formatear teléfono con código de país
     */
    private function formatearTelefonoConPais(string $telefono, string $pais = 'PE'): string
    {
        // Códigos de país disponibles
        $codigosPais = [
            'PE' => '+51',
            'CO' => '+57',
            'AR' => '+54',
            'CL' => '+56',
            'BO' => '+591',
            'EC' => '+593',
            'UY' => '+598',
            'PY' => '+595',
            'VE' => '+58',
            'US' => '+1',
            'MX' => '+52',
            'ES' => '+34'
        ];

        // Limpiar el número de caracteres no numéricos excepto '+'
        $telefonoLimpio = preg_replace('/[^\d+]/', '', $telefono);
        
        // Si ya tiene código de país, devolverlo tal como está
        if (str_starts_with($telefonoLimpio, '+')) {
            return $telefonoLimpio;
        }
        
        // Si no tiene código de país, agregarlo
        $codigoPais = $codigosPais[$pais] ?? $codigosPais['PE'];
        return $codigoPais . $telefonoLimpio;
    }

    /**
     * Cambiar contraseña del usuario autenticado
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Verificar la contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }

            // Actualizar la contraseña
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            Log::info('Contraseña cambiada exitosamente', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cambiar contraseña', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener lista de tokens activos (dispositivos)
     */
    public function getTokens(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $currentTokenId = $request->user()->currentAccessToken()->id;
            
            $tokens = $user->tokens()->where('name', '!=', 'admin-token')->get()->map(function ($token) use ($currentTokenId) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                    'is_current' => $token->id === $currentTokenId
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $tokens,
                'message' => 'Tokens obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener tokens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revocar un token específico
     */
    public function revokeToken(Request $request, $tokenId): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            $token = $user->tokens()->where('id', $tokenId)->first();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token no encontrado'
                ], 404);
            }

            // No permitir revocar el token actual
            if ($token->id === $user->currentAccessToken()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes revocar el token de la sesión actual'
                ], 422);
            }

            $token->delete();

            Log::info('Token revocado', [
                'user_id' => $user->id,
                'token_id' => $tokenId,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Dispositivo desconectado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al revocar token: ' . $e->getMessage()
            ], 500);
        }
    }
}

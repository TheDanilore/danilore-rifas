<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Obtener perfil del usuario autenticado
     */
    public function profile()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Devolver todos los campos del usuario
            $perfil = [
                'id' => $user->id,
                'name' => $user->name,
                'nombres' => $user->nombre, // Mapear nombre a nombres para el frontend
                'apellidos' => $user->apellido, // Mapear apellido a apellidos para el frontend
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
                'activo' => $user->activo,
                'verificado' => $user->verificado,
                'ultimo_acceso' => $user->ultimo_acceso,
                'avatar' => $user->avatar,
                'zona_horaria' => $user->zona_horaria,
                'preferencias_notificacion' => $user->preferencias_notificacion,
                'total_boletos_comprados' => $user->total_boletos_comprados,
                'total_gastado' => $user->total_gastado,
                'total_rifas_participadas' => $user->total_rifas_participadas,
                'rifas_ganadas' => $user->rifas_ganadas,
                'primera_compra' => $user->primera_compra,
                'ultima_compra' => $user->ultima_compra,
                'doble_autenticacion' => $user->doble_autenticacion,
                'intentos_login_fallidos' => $user->intentos_login_fallidos,
                'bloqueado_hasta' => $user->bloqueado_hasta,
                'acepta_terminos' => $user->acepta_terminos,
                'fecha_aceptacion_terminos' => $user->fecha_aceptacion_terminos,
                'acepta_marketing' => $user->acepta_marketing,
                'fecha_aceptacion_marketing' => $user->fecha_aceptacion_marketing,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')
            ];

            // Estadísticas calculadas
            $estadisticas = [
                'total_boletos' => $user->total_boletos_comprados,
                'rifas_ganadas' => $user->rifas_ganadas,
                'favoritos' => $user->favoritos()->count(),
                'total_gastado' => $user->total_gastado,
                'total_ventas' => $user->ventas()->count(),
                'total_gastado_ventas' => $user->ventas()->where('estado', 'completada')->sum('total'),
                'boletos_comprados_ventas' => $user->ventas()->where('estado', 'completada')->sum('cantidad_boletos'),
                'total_rifas_participadas' => $user->total_rifas_participadas
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $perfil,
                    'stats' => $estadisticas
                ],
                'message' => 'Perfil obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'nombre' => 'sometimes|string|max:100',
                'apellido' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|max:255|unique:users,email,' . Auth::id(),
                'telefono' => 'sometimes|string|max:20|unique:users,telefono,' . Auth::id(),
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
                'acepta_marketing' => 'sometimes|boolean',
                'password' => 'sometimes|string|min:8|confirmed',
                'password_actual' => 'required_with:password|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Verificar contraseña actual si se quiere cambiar
            if ($request->password && !Hash::check($request->password_actual, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }

            // Preparar datos para actualizar
            $updateData = $request->only([
                'nombre', 'apellido', 'email', 'telefono', 'fecha_nacimiento', 'genero',
                'direccion', 'ciudad', 'departamento', 'codigo_postal', 'pais',
                'avatar', 'zona_horaria', 'preferencias_notificacion', 'acepta_marketing'
            ]);

            // Actualizar nombre completo si se proporcionan nombre y apellido
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
            
            if ($request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Obtener datos completos actualizados
            $userUpdated = $user->fresh();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $userUpdated->id,
                        'name' => $userUpdated->name,
                        'nombre' => $userUpdated->nombre,
                        'apellido' => $userUpdated->apellido,
                        'email' => $userUpdated->email,
                        'telefono' => $userUpdated->telefono,
                        'tipo_documento' => $userUpdated->tipo_documento,
                        'numero_documento' => $userUpdated->numero_documento,
                        'fecha_nacimiento' => $userUpdated->fecha_nacimiento,
                        'genero' => $userUpdated->genero,
                        'direccion' => $userUpdated->direccion,
                        'ciudad' => $userUpdated->ciudad,
                        'departamento' => $userUpdated->departamento,
                        'codigo_postal' => $userUpdated->codigo_postal,
                        'pais' => $userUpdated->pais,
                        'avatar' => $userUpdated->avatar,
                        'zona_horaria' => $userUpdated->zona_horaria,
                        'preferencias_notificacion' => $userUpdated->preferencias_notificacion,
                        'total_boletos_comprados' => $userUpdated->total_boletos_comprados,
                        'total_gastado' => $userUpdated->total_gastado,
                        'total_rifas_participadas' => $userUpdated->total_rifas_participadas,
                        'rifas_ganadas' => $userUpdated->rifas_ganadas,
                        'primera_compra' => $userUpdated->primera_compra,
                        'ultima_compra' => $userUpdated->ultima_compra,
                        'role' => $userUpdated->getRoleNames()->first(),
                        'permissions' => $userUpdated->getAllPermissions()->pluck('name'),
                        'activo' => $userUpdated->activo,
                        'verificado' => $userUpdated->verificado,
                        'ultimo_acceso' => $userUpdated->ultimo_acceso,
                        'doble_autenticacion' => $userUpdated->doble_autenticacion,
                        'acepta_terminos' => $userUpdated->acepta_terminos,
                        'fecha_aceptacion_terminos' => $userUpdated->fecha_aceptacion_terminos,
                        'acepta_marketing' => $userUpdated->acepta_marketing,
                        'fecha_aceptacion_marketing' => $userUpdated->fecha_aceptacion_marketing,
                        'email_verified_at' => $userUpdated->email_verified_at,
                        'created_at' => $userUpdated->created_at,
                        'updated_at' => $userUpdated->updated_at
                    ]
                ],
                'message' => 'Perfil actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener actividad del usuario
     */
    public function activity(Request $request)
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Crear un timeline de actividad combinando diferentes tipos de eventos
            $actividades = collect();

            // Ventas
            $ventas = $user->ventas()
                ->with(['rifa.categoria'])
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get();

            foreach ($ventas as $venta) {
                $actividades->push([
                    'id' => 'venta_' . $venta->id,
                    'tipo' => 'compra',
                    'titulo' => 'Compra de boletos',
                    'descripcion' => "Compraste {$venta->cantidad_boletos} boleto(s) para la rifa '{$venta->rifa->nombre}'",
                    'metadata' => [
                        'cantidad_boletos' => $venta->cantidad_boletos,
                        'total' => 'S/ ' . $venta->total,
                        'estado' => $venta->estado
                    ],
                    'created_at' => $venta->created_at
                ]);
            }

            // Boletos ganadores
            $boletosGanadores = $user->boletos()
                ->whereHas('sorteoGanador')
                ->with(['venta.rifa', 'sorteoGanador.premio'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($boletosGanadores as $boleto) {
                $actividades->push([
                    'id' => 'ganador_' . $boleto->id,
                    'tipo' => 'ganador',
                    'titulo' => '¡Felicidades! Ganaste un premio',
                    'descripcion' => "Tu boleto #{$boleto->numero} ganó en la rifa '{$boleto->venta->rifa->nombre}'",
                    'metadata' => [
                        'boleto_numero' => $boleto->numero,
                        'premio' => $boleto->sorteoGanador->premio->nombre ?? 'Premio'
                    ],
                    'created_at' => $boleto->sorteoGanador->created_at ?? $boleto->created_at
                ]);
            }

            // Comentarios
            $comentarios = $user->comentarios()
                ->with(['rifa'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($comentarios as $comentario) {
                $actividades->push([
                    'id' => 'comentario_' . $comentario->id,
                    'tipo' => 'comentario',
                    'titulo' => 'Comentario en rifa',
                    'descripcion' => "Comentaste en la rifa '{$comentario->rifa->nombre}'",
                    'metadata' => [
                        'comentario' => substr($comentario->contenido, 0, 100) . (strlen($comentario->contenido) > 100 ? '...' : '')
                    ],
                    'created_at' => $comentario->created_at
                ]);
            }

            // Favoritos
            $favoritos = $user->favoritos()
                ->with(['rifa.categoria'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            foreach ($favoritos as $favorito) {
                $actividades->push([
                    'id' => 'favorito_' . $favorito->id,
                    'tipo' => 'favorito',
                    'titulo' => 'Agregado a favoritos',
                    'descripcion' => "Agregaste la rifa '{$favorito->rifa->nombre}' a tus favoritos",
                    'metadata' => [
                        'categoria' => $favorito->rifa->categoria->nombre ?? 'Sin categoría'
                    ],
                    'created_at' => $favorito->created_at
                ]);
            }

            // Transferencias
            $transferencias = $user->boletos()
                ->where('transferido', true)
                ->with(['venta.rifa'])
                ->orderBy('transferido_at', 'desc')
                ->take(10)
                ->get();

            foreach ($transferencias as $boleto) {
                $actividades->push([
                    'id' => 'transferencia_' . $boleto->id,
                    'tipo' => 'transferencia',
                    'titulo' => 'Boleto transferido',
                    'descripcion' => "Transferiste el boleto #{$boleto->numero} de la rifa '{$boleto->venta->rifa->nombre}'",
                    'metadata' => [
                        'boleto_numero' => $boleto->numero
                    ],
                    'created_at' => $boleto->transferido_at
                ]);
            }

            // Ordenar por fecha y paginar
            $actividadesSorted = $actividades->sortByDesc('created_at')->values();

            // Aplicar filtros si existen
            if ($request->tipo) {
                $actividadesSorted = $actividadesSorted->where('tipo', $request->tipo)->values();
            }

            if ($request->periodo) {
                $fecha = null;
                switch ($request->periodo) {
                    case 'hoy':
                        $fecha = today();
                        break;
                    case 'semana':
                        $fecha = now()->startOfWeek();
                        break;
                    case 'mes':
                        $fecha = now()->startOfMonth();
                        break;
                    case 'año':
                        $fecha = now()->startOfYear();
                        break;
                }
                if ($fecha) {
                    $actividadesSorted = $actividadesSorted->filter(function($actividad) use ($fecha) {
                        return \Carbon\Carbon::parse($actividad['created_at'])->gte($fecha);
                    })->values();
                }
            }

            // Paginación manual
            $perPage = $request->per_page ?? 15;
            $currentPage = $request->page ?? 1;
            $total = $actividadesSorted->count();
            $items = $actividadesSorted->forPage($currentPage, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'actividades' => $items,
                    'pagination' => [
                        'total' => $total,
                        'current_page' => $currentPage,
                        'per_page' => $perPage,
                        'last_page' => ceil($total / $perPage),
                        'from' => ($currentPage - 1) * $perPage + 1,
                        'to' => min($currentPage * $perPage, $total)
                    ]
                ],
                'message' => 'Actividad obtenida exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividad: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Obtener todos los usuarios (administrador)
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['roles']);

            // Filtros
            if ($request->role) {
                $query->role($request->role);
            }

            if ($request->activo !== null) {
                $activo = filter_var($request->activo, FILTER_VALIDATE_BOOLEAN);
                $query->where('activo', $activo);
            }

            if ($request->verificado !== null) {
                $verificado = filter_var($request->verificado, FILTER_VALIDATE_BOOLEAN);
                $query->where('verificado', $verificado);
            }

            if ($request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $usuarios = $query->orderBy('created_at', 'desc')
                            ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $usuarios,
                'message' => 'Usuarios obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalles de un usuario específico (administrador)
     */
    public function show($id)
    {
        try {
            $usuario = User::with([
                'roles', 
                'permissions',
                'ventas.rifa',
                'favoritos.rifa',
                'comentarios.rifa'
            ])->find($id);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // Estadísticas del usuario
            $estadisticas = [
                'total_ventas' => $usuario->ventas()->count(),
                'ventas_completadas' => $usuario->ventas()->where('estado', 'completada')->count(),
                'total_gastado' => $usuario->ventas()->where('estado', 'completada')->sum('total'),
                'boletos_comprados' => $usuario->ventas()->where('estado', 'completada')->sum('cantidad_boletos'),
                'comentarios_realizados' => $usuario->comentarios()->count(),
                'favoritos_agregados' => $usuario->favoritos()->count(),
                'ultimo_acceso' => $usuario->ultimo_acceso,
                'registro' => $usuario->created_at
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'usuario' => $usuario,
                    'estadisticas' => $estadisticas
                ],
                'message' => 'Usuario obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo usuario (administrador)
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'telefono' => 'nullable|string|max:20',
                'fecha_nacimiento' => 'nullable|date|before:today',
                'ciudad' => 'nullable|string|max:100',
                'role' => 'required|string|exists:roles,name',
                'activo' => 'boolean',
                'verificado' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::transaction(function() use ($request, &$usuario) {
                $usuario = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'telefono' => $request->telefono,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'ciudad' => $request->ciudad,
                    'activo' => $request->activo ?? true,
                    'verificado' => $request->verificado ?? false,
                    'email_verified_at' => $request->verificado ? now() : null
                ]);

                // Asignar rol
                $usuario->assignRole($request->role);
            });

            return response()->json([
                'success' => true,
                'data' => $usuario->load('roles'),
                'message' => 'Usuario creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un usuario (administrador)
     */
    public function update(Request $request, $id)
    {
        try {
            $usuario = User::find($id);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|nullable|string|min:8',
                'telefono' => 'sometimes|nullable|string|max:20',
                'fecha_nacimiento' => 'sometimes|nullable|date|before:today',
                'ciudad' => 'sometimes|nullable|string|max:100',
                'role' => 'sometimes|string|exists:roles,name',
                'activo' => 'sometimes|boolean',
                'verificado' => 'sometimes|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::transaction(function() use ($request, $usuario) {
                // Preparar datos para actualizar
                $updateData = $request->only([
                    'name', 'email', 'telefono', 'fecha_nacimiento', 'ciudad', 'activo', 'verificado'
                ]);

                if ($request->password) {
                    $updateData['password'] = Hash::make($request->password);
                }

                if ($request->has('verificado') && $request->verificado) {
                    $updateData['email_verified_at'] = now();
                } elseif ($request->has('verificado') && !$request->verificado) {
                    $updateData['email_verified_at'] = null;
                }

                $usuario->update($updateData);

                // Actualizar rol si se proporciona
                if ($request->role) {
                    $usuario->syncRoles([$request->role]);
                }
            });

            return response()->json([
                'success' => true,
                'data' => $usuario->fresh(['roles']),
                'message' => 'Usuario actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un usuario (administrador)
     */
    public function destroy($id)
    {
        try {
            $usuario = User::find($id);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // No permitir eliminar super_admin
            if ($usuario->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un super administrador'
                ], 403);
            }

            // No permitir auto-eliminación
            if ($usuario->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propia cuenta'
                ], 403);
            }

            DB::transaction(function() use ($usuario) {
                // Eliminar relaciones relacionadas
                $usuario->favoritos()->delete();
                $usuario->comentarios()->delete();
                $usuario->tokens()->delete();
                
                // Eliminar usuario
                $usuario->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleStatus($id)
    {
        try {
            $usuario = User::find($id);

            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            // No permitir desactivar super_admin
            if ($usuario->hasRole('super_admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede desactivar un super administrador'
                ], 403);
            }

            $usuario->update(['activo' => !$usuario->activo]);

            $mensaje = $usuario->activo ? 'Usuario activado exitosamente' : 'Usuario desactivado exitosamente';

            return response()->json([
                'success' => true,
                'data' => $usuario,
                'message' => $mensaje
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar estado del usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de usuarios
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_usuarios' => User::count(),
                'usuarios_activos' => User::where('activo', true)->count(),
                'usuarios_verificados' => User::whereNotNull('email_verified_at')->count(),
                'por_rol' => Role::withCount('users')->get(),
                'registros_por_mes' => User::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get(),
                'usuarios_mas_activos' => User::withCount(['ventas', 'comentarios'])
                    ->orderBy('ventas_count', 'desc')
                    ->take(10)
                    ->get(['id', 'name', 'email']),
                'ultimos_accesos' => User::whereNotNull('ultimo_acceso')
                    ->orderBy('ultimo_acceso', 'desc')
                    ->take(10)
                    ->get(['id', 'name', 'ultimo_acceso'])
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
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
            
            $perfil = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'fecha_nacimiento' => $user->fecha_nacimiento,
                'ciudad' => $user->ciudad,
                'activo' => $user->activo,
                'verificado' => $user->verificado,
                'ultimo_acceso' => $user->ultimo_acceso,
                'created_at' => $user->created_at,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'estadisticas' => [
                    'total_ventas' => $user->ventas()->count(),
                    'total_gastado' => $user->ventas()->where('estado', 'completada')->sum('total'),
                    'boletos_comprados' => $user->ventas()->where('estado', 'completada')->sum('cantidad_boletos'),
                    'favoritos' => $user->favoritos()->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $perfil,
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
                'name' => 'sometimes|required|string|max:255',
                'telefono' => 'sometimes|nullable|string|max:20',
                'fecha_nacimiento' => 'sometimes|nullable|date|before:today',
                'ciudad' => 'sometimes|nullable|string|max:100',
                'password' => 'sometimes|nullable|string|min:8|confirmed',
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
            $updateData = $request->only(['name', 'telefono', 'fecha_nacimiento', 'ciudad']);
            
            if ($request->password) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $user->fresh(),
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
    public function activity()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();

            $actividad = [
                'ventas_recientes' => $user->ventas()
                    ->with(['rifa.categoria'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get(),
                'boletos_recientes' => $user->boletos()
                    ->with(['venta.rifa'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get(),
                'comentarios_recientes' => $user->comentarios()
                    ->with(['rifa'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
                'favoritos_recientes' => $user->favoritos()
                    ->with(['rifa.categoria'])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $actividad,
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
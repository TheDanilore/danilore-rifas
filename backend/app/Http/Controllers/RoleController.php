<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Obtener todos los roles
     */
    public function index()
    {
        try {
            $roles = Role::with(['permissions', 'users'])
                        ->withCount(['users'])
                        ->orderBy('name')
                        ->get();

            return response()->json([
                'success' => true,
                'data' => $roles,
                'message' => 'Roles obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver un rol específico
     */
    public function show($id)
    {
        try {
            $role = Role::with(['permissions', 'users'])
                       ->withCount('users')
                       ->find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => 'Rol obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo rol
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:roles,name|max:255',
                'guard_name' => 'sometimes|string|max:255',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name ?? 'web'
            ]);

            // Asignar permisos si se proporcionan
            if ($request->permissions) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'success' => true,
                'data' => $role->load('permissions'),
                'message' => 'Rol creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un rol
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            // No permitir modificar roles del sistema
            if (in_array($role->name, ['super_admin', 'admin', 'moderador', 'usuario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden modificar los roles del sistema'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|unique:roles,name,' . $id . '|max:255',
                'permissions' => 'sometimes|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar nombre si se proporciona
            if ($request->has('name')) {
                $role->update(['name' => $request->name]);
            }

            // Actualizar permisos si se proporcionan
            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return response()->json([
                'success' => true,
                'data' => $role->fresh(['permissions']),
                'message' => 'Rol actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un rol
     */
    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            // No permitir eliminar roles del sistema
            if (in_array($role->name, ['super_admin', 'admin', 'moderador', 'usuario'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar los roles del sistema'
                ], 403);
            }

            // Verificar si hay usuarios con este rol
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el rol porque tiene usuarios asignados'
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rol eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar permisos a un rol
     */
    public function assignPermissions(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role->syncPermissions($request->permissions);

            return response()->json([
                'success' => true,
                'data' => $role->load('permissions'),
                'message' => 'Permisos asignados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revocar permisos de un rol
     */
    public function revokePermissions(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'permissions' => 'required|array',
                'permissions.*' => 'exists:permissions,name'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $role->revokePermissionTo($request->permissions);

            return response()->json([
                'success' => true,
                'data' => $role->load('permissions'),
                'message' => 'Permisos revocados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al revocar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuarios de un rol
     */
    public function users($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rol no encontrado'
                ], 404);
            }

            $usuarios = $role->users()
                           ->select(['id', 'name', 'email', 'activo', 'created_at'])
                           ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => [
                    'role' => $role,
                    'usuarios' => $usuarios
                ],
                'message' => 'Usuarios del rol obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios del rol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de roles
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_roles' => Role::count(),
                'roles_con_usuarios' => Role::has('users')->count(),
                'distribucion_usuarios' => Role::withCount('users')
                    ->orderBy('users_count', 'desc')
                    ->get(['name', 'users_count']),
                'permisos_mas_usados' => Permission::withCount('roles')
                    ->orderBy('roles_count', 'desc')
                    ->take(10)
                    ->get(['name', 'roles_count']),
                'roles_sistema' => Role::whereIn('name', ['super_admin', 'admin', 'moderador', 'usuario'])
                    ->withCount('users')
                    ->get(['name', 'users_count']),
                'roles_personalizados' => Role::whereNotIn('name', ['super_admin', 'admin', 'moderador', 'usuario'])
                    ->withCount('users')
                    ->get(['name', 'users_count'])
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
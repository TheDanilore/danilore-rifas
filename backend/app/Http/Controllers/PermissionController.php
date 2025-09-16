<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Obtener todos los permisos
     */
    public function index()
    {
        try {
            $permisos = Permission::with(['roles'])
                                 ->withCount(['roles'])
                                 ->orderBy('name')
                                 ->get()
                                 ->groupBy(function($permission) {
                                     // Agrupar por categoría (primera parte antes del punto)
                                     $parts = explode('.', $permission->name);
                                     return $parts[0] ?? 'general';
                                 });

            return response()->json([
                'success' => true,
                'data' => $permisos,
                'message' => 'Permisos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver un permiso específico
     */
    public function show($id)
    {
        try {
            $permiso = Permission::with(['roles'])
                                ->withCount('roles')
                                ->find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $permiso,
                'message' => 'Permiso obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo permiso
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:permissions,name|max:255',
                'guard_name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:500',
                'category' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permiso = Permission::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name ?? 'web'
            ]);

            return response()->json([
                'success' => true,
                'data' => $permiso,
                'message' => 'Permiso creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un permiso
     */
    public function update(Request $request, $id)
    {
        try {
            $permiso = Permission::find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }

            // Lista de permisos del sistema que no se pueden modificar
            $sistemPermisos = [
                'dashboard.ver', 'rifas.crear', 'rifas.editar', 'rifas.eliminar', 'rifas.ver',
                'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar', 'usuarios.ver',
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.eliminar',
                'reportes.ver', 'configuracion.editar', 'roles.gestionar', 'permisos.gestionar'
            ];

            if (in_array($permiso->name, $sistemPermisos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden modificar los permisos del sistema'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|unique:permissions,name,' . $id . '|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('name')) {
                $permiso->update(['name' => $request->name]);
            }

            return response()->json([
                'success' => true,
                'data' => $permiso,
                'message' => 'Permiso actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un permiso
     */
    public function destroy($id)
    {
        try {
            $permiso = Permission::find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }

            // Lista de permisos del sistema que no se pueden eliminar
            $sistemPermisos = [
                'dashboard.ver', 'rifas.crear', 'rifas.editar', 'rifas.eliminar', 'rifas.ver',
                'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar', 'usuarios.ver',
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.eliminar',
                'reportes.ver', 'configuracion.editar', 'roles.gestionar', 'permisos.gestionar'
            ];

            if (in_array($permiso->name, $sistemPermisos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden eliminar los permisos del sistema'
                ], 403);
            }

            // Verificar si hay roles con este permiso
            if ($permiso->roles()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el permiso porque está asignado a roles'
                ], 422);
            }

            $permiso->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permiso eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener roles que tienen un permiso específico
     */
    public function roles($id)
    {
        try {
            $permiso = Permission::find($id);

            if (!$permiso) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permiso no encontrado'
                ], 404);
            }

            $roles = $permiso->roles()
                           ->withCount('users')
                           ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'permiso' => $permiso,
                    'roles' => $roles
                ],
                'message' => 'Roles del permiso obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener roles del permiso: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener permisos por categoría
     */
    public function porCategoria()
    {
        try {
            $categorias = [
                'dashboard' => 'Dashboard y estadísticas',
                'rifas' => 'Gestión de rifas',
                'usuarios' => 'Gestión de usuarios',
                'ventas' => 'Gestión de ventas',
                'reportes' => 'Reportes y análisis',
                'configuracion' => 'Configuración del sistema',
                'roles' => 'Gestión de roles',
                'permisos' => 'Gestión de permisos',
                'comentarios' => 'Gestión de comentarios',
                'notificaciones' => 'Gestión de notificaciones',
                'cupones' => 'Gestión de cupones',
                'sorteos' => 'Gestión de sorteos',
                'boletos' => 'Gestión de boletos',
                'pagos' => 'Gestión de pagos',
                'perfil' => 'Gestión de perfil personal'
            ];

            $permisosPorCategoria = [];

            foreach ($categorias as $categoria => $descripcion) {
                $permisos = Permission::where('name', 'like', $categoria . '.%')
                                    ->with(['roles'])
                                    ->withCount('roles')
                                    ->orderBy('name')
                                    ->get();

                if ($permisos->isNotEmpty()) {
                    $permisosPorCategoria[$categoria] = [
                        'descripcion' => $descripcion,
                        'permisos' => $permisos
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $permisosPorCategoria,
                'message' => 'Permisos por categoría obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener permisos por categoría: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sincronizar permisos del sistema
     */
    public function sincronizar()
    {
        try {
            $permisosCreados = 0;
            $permisosExistentes = 0;

            // Lista completa de permisos del sistema
            $permisosSistema = [
                // Dashboard
                'dashboard.ver',

                // Rifas
                'rifas.ver', 'rifas.crear', 'rifas.editar', 'rifas.eliminar', 'rifas.sortear',

                // Usuarios
                'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar',

                // Ventas
                'ventas.ver', 'ventas.crear', 'ventas.editar', 'ventas.eliminar', 'ventas.ver.propias', 'ventas.confirmar.pago',

                // Reportes
                'reportes.ver', 'reportes.exportar',

                // Configuración
                'configuracion.editar',

                // Roles y permisos
                'roles.gestionar', 'permisos.gestionar',

                // Comentarios
                'comentarios.ver', 'comentarios.crear', 'comentarios.editar', 'comentarios.eliminar', 'comentarios.moderar',

                // Notificaciones
                'notificaciones.ver', 'notificaciones.crear', 'notificaciones.enviar.masiva',

                // Cupones
                'cupones.ver', 'cupones.crear', 'cupones.editar', 'cupones.eliminar', 'cupones.usar',

                // Sorteos
                'sorteos.ver', 'sorteos.ejecutar', 'sorteos.programar',

                // Boletos
                'boletos.ver', 'boletos.transferir', 'boletos.gestionar',

                // Pagos
                'pagos.ver', 'pagos.aprobar', 'pagos.rechazar', 'pagos.gestionar',

                // Perfil personal
                'perfil.ver', 'perfil.editar'
            ];

            foreach ($permisosSistema as $nombrePermiso) {
                $permiso = Permission::firstOrCreate(
                    ['name' => $nombrePermiso],
                    ['guard_name' => 'web']
                );

                if ($permiso->wasRecentlyCreated) {
                    $permisosCreados++;
                } else {
                    $permisosExistentes++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'permisos_creados' => $permisosCreados,
                    'permisos_existentes' => $permisosExistentes,
                    'total_permisos' => count($permisosSistema)
                ],
                'message' => 'Sincronización de permisos completada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar permisos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de permisos
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_permisos' => Permission::count(),
                'permisos_asignados' => Permission::has('roles')->count(),
                'permisos_sin_asignar' => Permission::doesntHave('roles')->count(),
                'distribucion_por_categoria' => Permission::all()
                    ->groupBy(function($permission) {
                        $parts = explode('.', $permission->name);
                        return $parts[0] ?? 'general';
                    })
                    ->map(function($group) {
                        return $group->count();
                    }),
                'permisos_mas_asignados' => Permission::withCount('roles')
                    ->orderBy('roles_count', 'desc')
                    ->take(10)
                    ->get(['name', 'roles_count']),
                'permisos_nunca_asignados' => Permission::doesntHave('roles')
                    ->get(['name'])
                    ->pluck('name')
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
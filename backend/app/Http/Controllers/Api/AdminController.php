<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rifa;
use App\Models\Venta;
use App\Models\Boleto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Obtener estadísticas del dashboard
     */
    public function getDashboardStats()
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m');
            
            // Estadísticas de rifas
            $rifasStats = [
                'total' => Rifa::count(),
                'activas' => Rifa::where('estado', 'activa')->count(),
                'proximas' => Rifa::where('estado', 'proximamente')->count(),
                'completadas' => Rifa::where('estado', 'finalizada')->count()
            ];
            
            // Estadísticas de usuarios
            $usuariosStats = [
                'total' => User::count(),
                'nuevos_mes' => User::whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count(),
                'activos' => User::where('activo', true)->count()
            ];
            
            // Estadísticas de ventas
            $ventasStats = [
                'total_mes' => Venta::where('estado', 'completada')
                    ->whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->sum('total'),
                'tickets_vendidos' => Boleto::whereHas('venta', function($query) {
                    $query->where('estado', 'completada');
                })->count(),
                'pendientes' => Venta::where('estado', 'pendiente')->count()
            ];
            
            // Estadísticas de pagos
            $pagosStats = [
                'pendientes' => Venta::where('estado', 'pendiente')->count(),
                'verificados' => Venta::where('estado', 'completada')->count(),
                'rechazados' => Venta::where('estado', 'cancelada')->count()
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rifas' => $rifasStats,
                    'usuarios' => $usuariosStats,
                    'ventas' => $ventasStats,
                    'pagos' => $pagosStats
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener actividad reciente
     */
    public function getRecentActivity()
    {
        try {
            $activities = collect();
            
            // Ventas recientes
            $ventasRecientes = Venta::with(['user', 'rifa'])
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
                
            foreach ($ventasRecientes as $venta) {
                $activities->push([
                    'id' => 'venta_' . $venta->id,
                    'title' => 'Nueva compra de ticket',
                    'description' => "Usuario {$venta->user->name} compró {$venta->cantidad_boletos} tickets de {$venta->rifa->nombre}",
                    'time' => $this->getTimeAgo($venta->created_at),
                    'type' => 'sale',
                    'icon' => 'fas fa-shopping-cart',
                    'status' => $venta->estado === 'completada' ? 'success' : 'pending',
                    'statusText' => $venta->estado === 'completada' ? 'Completado' : 'Pendiente'
                ]);
            }
            
            // Usuarios nuevos
            $usuariosNuevos = User::where('created_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();
                
            foreach ($usuariosNuevos as $usuario) {
                $activities->push([
                    'id' => 'user_' . $usuario->id,
                    'title' => 'Nuevo usuario registrado',
                    'description' => "{$usuario->name} se registró en la plataforma",
                    'time' => $this->getTimeAgo($usuario->created_at),
                    'type' => 'user',
                    'icon' => 'fas fa-user-plus',
                    'status' => 'info',
                    'statusText' => 'Nuevo'
                ]);
            }
            
            // Rifas finalizadas recientemente
            $rifasFinalizadas = Rifa::where('estado', 'finalizada')
                ->where('updated_at', '>=', Carbon::now()->subDays(7))
                ->orderBy('updated_at', 'desc')
                ->take(2)
                ->get();
                
            foreach ($rifasFinalizadas as $rifa) {
                $activities->push([
                    'id' => 'rifa_' . $rifa->id,
                    'title' => 'Rifa finalizada',
                    'description' => "Rifa \"{$rifa->nombre}\" ha sido sorteada",
                    'time' => $this->getTimeAgo($rifa->updated_at),
                    'type' => 'rifa',
                    'icon' => 'fas fa-trophy',
                    'status' => 'completed',
                    'statusText' => 'Finalizada'
                ]);
            }
            
            // Ordenar por tiempo y tomar los más recientes
            $sortedActivities = $activities->sortByDesc('time')->take(10)->values();
            
            return response()->json([
                'success' => true,
                'data' => $sortedActivities
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener actividad reciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener usuarios para administración
     */
    public function getUsuarios(Request $request)
    {
        try {
            $query = User::query();
            
            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }
            
            if ($request->has('role') && $request->get('role') !== 'todos') {
                $query->where('rol', $request->get('role'));
            }
            
            if ($request->has('status') && $request->get('status') !== 'todos') {
                $query->where('activo', $request->get('status') === 'activo');
            }
            
            $usuarios = $query->withCount(['ventas', 'boletos'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $usuarios
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUsuario(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6',
                'rol' => 'required|in:usuario,admin',
                'telefono' => 'nullable|string|max:20'
            ]);
            
            $usuario = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'rol' => $request->rol,
                'telefono' => $request->telefono,
                'activo' => true
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => $usuario
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar usuario
     */
    public function updateUsuario(Request $request, $id)
    {
        try {
            $usuario = User::findOrFail($id);
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'rol' => 'required|in:usuario,admin',
                'telefono' => 'nullable|string|max:20',
                'activo' => 'required|boolean'
            ]);
            
            $usuario->update([
                'name' => $request->name,
                'email' => $request->email,
                'rol' => $request->rol,
                'telefono' => $request->telefono,
                'activo' => $request->activo
            ]);
            
            if ($request->filled('password')) {
                $usuario->update([
                    'password' => bcrypt($request->password)
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $usuario
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar usuario
     */
    public function deleteUsuario($id)
    {
        try {
            $usuario = User::findOrFail($id);
            
            // Verificar que no sea el último admin
            if ($usuario->rol === 'admin') {
                $adminCount = User::where('rol', 'admin')->where('activo', true)->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede eliminar el último administrador'
                    ], 400);
                }
            }
            
            $usuario->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener ventas para administración
     */
    public function getVentas(Request $request)
    {
        try {
            $query = Venta::with(['user', 'rifa', 'boletos']);
            
            // Filtros
            if ($request->has('estado') && $request->get('estado') !== 'todos') {
                $query->where('estado', $request->get('estado'));
            }
            
            if ($request->has('fecha_desde')) {
                $query->whereDate('created_at', '>=', $request->get('fecha_desde'));
            }
            
            if ($request->has('fecha_hasta')) {
                $query->whereDate('created_at', '<=', $request->get('fecha_hasta'));
            }
            
            $ventas = $query->orderBy('created_at', 'desc')->paginate(20);
            
            return response()->json([
                'success' => true,
                'data' => $ventas
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener reportes
     */
    public function getReportes(Request $request)
    {
        try {
            $periodo = $request->get('periodo', 'mes'); // mes, trimestre, año
            
            // Configurar fechas según el período
            switch ($periodo) {
                case 'mes':
                    $fechaInicio = Carbon::now()->startOfMonth();
                    $fechaFin = Carbon::now()->endOfMonth();
                    break;
                case 'trimestre':
                    $fechaInicio = Carbon::now()->startOfQuarter();
                    $fechaFin = Carbon::now()->endOfQuarter();
                    break;
                case 'año':
                    $fechaInicio = Carbon::now()->startOfYear();
                    $fechaFin = Carbon::now()->endOfYear();
                    break;
                default:
                    $fechaInicio = Carbon::now()->startOfMonth();
                    $fechaFin = Carbon::now()->endOfMonth();
            }
            
            // Reporte de ventas
            $ventasPorDia = Venta::where('estado', 'completada')
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->selectRaw('DATE(created_at) as fecha, SUM(total) as total, COUNT(*) as cantidad')
                ->orderBy('fecha')
                ->get();
            
            // Rifas más vendidas
            $rifasMasVendidas = Rifa::withCount(['boletos' => function($query) use ($fechaInicio, $fechaFin) {
                $query->whereHas('venta', function($q) use ($fechaInicio, $fechaFin) {
                    $q->where('estado', 'completada')
                      ->whereBetween('created_at', [$fechaInicio, $fechaFin]);
                });
            }])
            ->orderBy('boletos_count', 'desc')
            ->take(10)
            ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'periodo' => $periodo,
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                    'ventas_por_dia' => $ventasPorDia,
                    'rifas_mas_vendidas' => $rifasMasVendidas
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reportes',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper para calcular tiempo transcurrido
     */
    private function getTimeAgo($datetime)
    {
        $now = Carbon::now();
        $diff = $now->diff($datetime);
        
        if ($diff->days > 0) {
            return "Hace {$diff->days} día" . ($diff->days > 1 ? 's' : '');
        } elseif ($diff->h > 0) {
            return "Hace {$diff->h} hora" . ($diff->h > 1 ? 's' : '');
        } elseif ($diff->i > 0) {
            return "Hace {$diff->i} minuto" . ($diff->i > 1 ? 's' : '');
        } else {
            return "Hace un momento";
        }
    }

    /**
     * Get rifas statistics
     */
    public function getRifasStats()
    {
        try {
            $stats = [
                'rifas_activas' => Rifa::where('estado', 'activa')->count(),
                'total_ingresos' => Venta::where('estado', 'completada')->sum('total'),
                'total_tickets' => Boleto::whereHas('venta', function($query) {
                    $query->where('estado', 'completada');
                })->count(),
                'rifas_completadas' => Rifa::where('estado', 'finalizada')->count(),
                'rifas_pausadas' => Rifa::where('estado', 'pausada')->count(),
                'rifas_borrador' => Rifa::where('estado', 'borrador')->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export rifas data
     */
    public function exportRifas(Request $request)
    {
        try {
            $query = Rifa::with(['categoria', 'premios']);

            // Aplicar filtros si existen
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('busqueda')) {
                $search = $request->busqueda;
                $query->where(function($q) use ($search) {
                    $q->where('titulo', 'like', "%{$search}%")
                      ->orWhere('codigo_unico', 'like', "%{$search}%");
                });
            }

            $rifas = $query->get();

            // Por ahora retornamos los datos en JSON
            // En una implementación real, aquí generarías un archivo Excel/CSV
            return response()->json([
                'success' => true,
                'data' => $rifas,
                'message' => 'Datos de rifas exportados exitosamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar datos: ' . $e->getMessage()
            ], 500);
        }
    }
}

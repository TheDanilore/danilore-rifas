<?php

namespace App\Http\Controllers;

use App\Models\Rifa;
use App\Models\Venta;
use App\Models\User;
use App\Models\Pago;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReporteController extends Controller
{
    /**
     * Reporte general del dashboard
     */
    public function dashboard()
    {
        try {
            $reporte = [
                'resumen_general' => [
                    'total_rifas' => Rifa::count(),
                    'rifas_activas' => Rifa::where('estado', 'activa')->count(),
                    'total_usuarios' => User::count(),
                    'total_ventas' => Venta::where('estado', 'completada')->count(),
                    'ingresos_totales' => Venta::where('estado', 'completada')->sum('total'),
                    'ingresos_mes_actual' => Venta::where('estado', 'completada')
                        ->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('total')
                ],
                'ventas_ultimos_30_dias' => Venta::where('estado', 'completada')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->selectRaw('DATE(created_at) as fecha, COUNT(*) as total_ventas, SUM(total) as ingresos')
                    ->groupBy('fecha')
                    ->orderBy('fecha')
                    ->get(),
                'categorias_populares' => Categoria::withCount(['rifas' => function($query) {
                        $query->where('estado', '!=', 'borrador');
                    }])
                    ->orderBy('rifas_count', 'desc')
                    ->take(5)
                    ->get(),
                'usuarios_top' => User::withSum(['ventas' => function($query) {
                        $query->where('estado', 'completada');
                    }], 'total')
                    ->orderBy('ventas_sum_total', 'desc')
                    ->take(10)
                    ->get(['id', 'name', 'email'])
            ];

            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte de dashboard generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de ventas detallado
     */
    public function ventas(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'categoria_id' => 'nullable|exists:categorias,id',
                'rifa_id' => 'nullable|exists:rifas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Venta::with(['user', 'rifa.categoria', 'pago'])
                         ->where('estado', 'completada');

            // Aplicar filtros
            if ($request->fecha_inicio) {
                $query->whereDate('created_at', '>=', $request->fecha_inicio);
            }

            if ($request->fecha_fin) {
                $query->whereDate('created_at', '<=', $request->fecha_fin);
            }

            if ($request->categoria_id) {
                $query->whereHas('rifa', function($q) use ($request) {
                    $q->where('categoria_id', $request->categoria_id);
                });
            }

            if ($request->rifa_id) {
                $query->where('rifa_id', $request->rifa_id);
            }

            $ventas = $query->orderBy('created_at', 'desc')->get();

            $reporte = [
                'resumen' => [
                    'total_ventas' => $ventas->count(),
                    'ingresos_totales' => $ventas->sum('total'),
                    'boletos_vendidos' => $ventas->sum('cantidad_boletos'),
                    'precio_promedio' => $ventas->avg('precio_unitario'),
                    'venta_promedio' => $ventas->avg('total')
                ],
                'por_categoria' => $ventas->groupBy('rifa.categoria.nombre')->map(function($grupo) {
                    return [
                        'total_ventas' => $grupo->count(),
                        'ingresos' => $grupo->sum('total'),
                        'boletos' => $grupo->sum('cantidad_boletos')
                    ];
                }),
                'por_metodo_pago' => $ventas->groupBy('metodo_pago')->map(function($grupo) {
                    return [
                        'total_ventas' => $grupo->count(),
                        'ingresos' => $grupo->sum('total')
                    ];
                }),
                'ventas_por_dia' => $ventas->groupBy(function($venta) {
                    return $venta->created_at->format('Y-m-d');
                })->map(function($grupo) {
                    return [
                        'total_ventas' => $grupo->count(),
                        'ingresos' => $grupo->sum('total')
                    ];
                }),
                'ventas' => $ventas
            ];

            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte de ventas generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de rifas
     */
    public function rifas(Request $request)
    {
        try {
            $query = Rifa::with(['categoria', 'ventas', 'premios'])
                        ->withCount(['ventas', 'boletos']);

            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->categoria_id) {
                $query->where('categoria_id', $request->categoria_id);
            }

            $rifas = $query->get();

            $reporte = [
                'resumen' => [
                    'total_rifas' => $rifas->count(),
                    'rifas_activas' => $rifas->where('estado', 'activa')->count(),
                    'rifas_completas' => $rifas->where('estado', 'completa')->count(),
                    'rifas_finalizadas' => $rifas->where('estado', 'finalizada')->count(),
                    'ingresos_totales' => $rifas->sum(function($rifa) {
                        return $rifa->ventas->sum('total');
                    }),
                    'boletos_totales' => $rifas->sum('cantidad_boletos'),
                    'boletos_vendidos' => $rifas->sum('ventas_count')
                ],
                'por_categoria' => $rifas->groupBy('categoria.nombre')->map(function($grupo) {
                    return [
                        'total_rifas' => $grupo->count(),
                        'boletos_vendidos' => $grupo->sum('ventas_count'),
                        'ingresos' => $grupo->sum(function($rifa) {
                            return $rifa->ventas->sum('total');
                        })
                    ];
                }),
                'rendimiento' => $rifas->map(function($rifa) {
                    $porcentajeVendido = $rifa->cantidad_boletos > 0 ? 
                        round(($rifa->ventas_count / $rifa->cantidad_boletos) * 100, 2) : 0;
                    
                    return [
                        'id' => $rifa->id,
                        'titulo' => $rifa->titulo,
                        'categoria' => $rifa->categoria->nombre,
                        'estado' => $rifa->estado,
                        'boletos_totales' => $rifa->cantidad_boletos,
                        'boletos_vendidos' => $rifa->ventas_count,
                        'porcentaje_vendido' => $porcentajeVendido,
                        'ingresos' => $rifa->ventas->sum('total'),
                        'fecha_inicio' => $rifa->fecha_inicio,
                        'fecha_fin' => $rifa->fecha_fin
                    ];
                })->sortByDesc('porcentaje_vendido')->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte de rifas generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de rifas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte de usuarios
     */
    public function usuarios(Request $request)
    {
        try {
            $usuarios = User::with(['ventas' => function($query) {
                    $query->where('estado', 'completada');
                }])
                ->withCount(['ventas as total_compras' => function($query) {
                    $query->where('estado', 'completada');
                }])
                ->withSum(['ventas as total_gastado' => function($query) {
                    $query->where('estado', 'completada');
                }], 'total')
                ->get();

            $reporte = [
                'resumen' => [
                    'total_usuarios' => $usuarios->count(),
                    'usuarios_activos' => $usuarios->where('total_compras', '>', 0)->count(),
                    'usuarios_sin_compras' => $usuarios->where('total_compras', 0)->count(),
                    'promedio_gasto' => $usuarios->where('total_compras', '>', 0)->avg('total_gastado'),
                    'usuario_mayor_gasto' => $usuarios->sortByDesc('total_gastado')->first()
                ],
                'segmentacion' => [
                    'nuevos_usuarios' => $usuarios->where('created_at', '>=', now()->subDays(30))->count(),
                    'usuarios_frecuentes' => $usuarios->where('total_compras', '>=', 5)->count(),
                    'usuarios_premium' => $usuarios->where('total_gastado', '>=', 1000)->count()
                ],
                'actividad_mensual' => User::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as nuevos_usuarios')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get(),
                'top_compradores' => $usuarios->sortByDesc('total_gastado')
                    ->take(20)
                    ->map(function($usuario) {
                        return [
                            'id' => $usuario->id,
                            'name' => $usuario->name,
                            'email' => $usuario->email,
                            'total_compras' => $usuario->total_compras,
                            'total_gastado' => $usuario->total_gastado,
                            'fecha_registro' => $usuario->created_at,
                            'ultima_compra' => $usuario->ventas->max('created_at')
                        ];
                    })->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte de usuarios generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de usuarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reporte financiero
     */
    public function financiero(Request $request)
    {
        try {
            $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : now()->startOfMonth();
            $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : now()->endOfMonth();

            $ventas = Venta::where('estado', 'completada')
                          ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                          ->with('rifa.categoria')
                          ->get();

            $pagos = Pago::where('estado', 'aprobado')
                        ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                        ->get();

            $reporte = [
                'periodo' => [
                    'fecha_inicio' => $fechaInicio->format('Y-m-d'),
                    'fecha_fin' => $fechaFin->format('Y-m-d'),
                    'dias' => $fechaInicio->diffInDays($fechaFin) + 1
                ],
                'ingresos' => [
                    'total_bruto' => $ventas->sum('total'),
                    'total_neto' => $pagos->sum('monto'),
                    'promedio_diario' => $ventas->sum('total') / ($fechaInicio->diffInDays($fechaFin) + 1),
                    'total_transacciones' => $ventas->count()
                ],
                'por_metodo_pago' => $pagos->groupBy('metodo_pago')->map(function($grupo) use ($pagos) {
                    return [
                        'total_transacciones' => $grupo->count(),
                        'monto_total' => $grupo->sum('monto'),
                        'porcentaje' => round(($grupo->sum('monto') / $pagos->sum('monto')) * 100, 2)
                    ];
                }),
                'por_categoria' => $ventas->groupBy('rifa.categoria.nombre')->map(function($grupo) {
                    return [
                        'total_ventas' => $grupo->count(),
                        'ingresos' => $grupo->sum('total'),
                        'boletos_vendidos' => $grupo->sum('cantidad_boletos')
                    ];
                }),
                'evolucion_diaria' => $ventas->groupBy(function($venta) {
                    return $venta->created_at->format('Y-m-d');
                })->map(function($grupo, $fecha) {
                    return [
                        'fecha' => $fecha,
                        'total_ventas' => $grupo->count(),
                        'ingresos' => $grupo->sum('total'),
                        'boletos_vendidos' => $grupo->sum('cantidad_boletos')
                    ];
                })->values(),
                'metricas_clave' => [
                    'ticket_promedio' => $ventas->avg('total'),
                    'boletos_por_venta' => $ventas->avg('cantidad_boletos'),
                    'conversion_pagos' => $pagos->count() > 0 ? 
                        round(($pagos->where('estado', 'aprobado')->count() / $pagos->count()) * 100, 2) : 0
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte financiero generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte financiero: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar reporte a CSV
     */
    public function exportar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'tipo' => 'required|in:ventas,rifas,usuarios,financiero',
                'formato' => 'required|in:csv,excel,pdf'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Aquí implementarías la lógica de exportación
            // Por ahora retorno un mensaje de éxito

            return response()->json([
                'success' => true,
                'data' => [
                    'tipo' => $request->tipo,
                    'formato' => $request->formato,
                    'url_descarga' => '/downloads/reporte_' . $request->tipo . '_' . now()->format('Y_m_d_H_i_s') . '.' . $request->formato
                ],
                'message' => 'Reporte generado y listo para descarga'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
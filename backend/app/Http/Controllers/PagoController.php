<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PagoController extends Controller
{
    /**
     * Obtener pagos del usuario autenticado
     */
    public function index(Request $request)
    {
        try {
            $query = Pago::where('user_id', Auth::id())
                        ->with(['venta.rifa']);

            // Filtros
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->metodo) {
                $query->where('metodo_pago', $request->metodo);
            }

            if ($request->periodo) {
                switch ($request->periodo) {
                    case 'hoy':
                        $query->whereDate('created_at', today());
                        break;
                    case 'semana':
                        $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                        break;
                    case 'mes':
                        $query->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                        break;
                    case 'año':
                        $query->whereYear('created_at', now()->year);
                        break;
                }
            }

            // Ordenamiento
            $sort = $request->sort ?? 'fecha_desc';
            switch ($sort) {
                case 'fecha_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'monto_desc':
                    $query->orderBy('monto', 'desc');
                    break;
                case 'monto_asc':
                    $query->orderBy('monto', 'asc');
                    break;
                default: // fecha_desc
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = $request->per_page ?? 20;
            $pagos = $query->paginate($perPage);

            // Formatear pagos para el frontend
            $pagosFormatted = $pagos->getCollection()->map(function($pago) {
                return [
                    'id' => $pago->id,
                    'referencia' => $pago->referencia,
                    'monto' => $pago->monto,
                    'estado' => $pago->estado,
                    'metodo' => $pago->metodo_pago,
                    'numero_operacion' => $pago->numero_operacion,
                    'fecha_transaccion' => $pago->fecha_transaccion,
                    'comprobante_url' => $pago->comprobante_url,
                    'created_at' => $pago->created_at,
                    'updated_at' => $pago->updated_at,
                    'venta' => $pago->venta ? [
                        'id' => $pago->venta->id,
                        'codigo' => $pago->venta->codigo_venta,
                        'total' => $pago->venta->total,
                        'estado' => $pago->venta->estado,
                        'rifa' => $pago->venta->rifa ? [
                            'id' => $pago->venta->rifa->id,
                            'nombre' => $pago->venta->rifa->nombre
                        ] : null
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'pagos' => $pagosFormatted,
                    'pagination' => [
                        'total' => $pagos->total(),
                        'current_page' => $pagos->currentPage(),
                        'per_page' => $pagos->perPage(),
                        'last_page' => $pagos->lastPage(),
                        'from' => $pagos->firstItem(),
                        'to' => $pagos->lastItem()
                    ]
                ],
                'message' => 'Pagos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un pago específico
     */
    public function show($id)
    {
        try {
            $pago = Pago::with(['venta.rifa', 'user'])->find($id);

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            // Verificar permisos
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($pago->user_id !== Auth::id() && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver este pago'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $pago,
                'message' => 'Pago obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo pago
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'venta_id' => 'required|exists:ventas,id',
                'metodo_pago' => 'required|in:yape,plin,transferencia,efectivo',
                'monto' => 'required|numeric|min:0',
                'referencia' => 'nullable|string|max:100',
                'comprobante' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $venta = Venta::find($request->venta_id);

            // Verificar que la venta pertenece al usuario
            if ($venta->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para pagar esta venta'
                ], 403);
            }

            // Subir comprobante si existe
            $comprobanteUrl = null;
            if ($request->hasFile('comprobante')) {
                $comprobanteUrl = $request->file('comprobante')->store('comprobantes', 'public');
            }

            $pago = Pago::create([
                'user_id' => Auth::id(),
                'venta_id' => $request->venta_id,
                'metodo_pago' => $request->metodo_pago,
                'monto' => $request->monto,
                'referencia' => $request->referencia,
                'comprobante_url' => $comprobanteUrl,
                'estado' => 'pendiente'
            ]);

            return response()->json([
                'success' => true,
                'data' => $pago,
                'message' => 'Pago registrado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Obtener todos los pagos (administrador)
     */
    public function admin(Request $request)
    {
        try {
            $query = Pago::with(['user', 'venta.rifa']);

            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->metodo_pago) {
                $query->where('metodo_pago', $request->metodo_pago);
            }

            $pagos = $query->orderBy('created_at', 'desc')->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $pagos,
                'message' => 'Pagos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar pago
     */
    public function aprobar($id)
    {
        try {
            $pago = Pago::with('venta')->find($id);

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            $pago->update([
                'estado' => 'aprobado',
                'aprobado_by' => Auth::id(),
                'aprobado_at' => now()
            ]);

            // Actualizar estado de la venta
            $pago->venta->update(['estado' => 'completada']);

            return response()->json([
                'success' => true,
                'data' => $pago->fresh(),
                'message' => 'Pago aprobado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar pago
     */
    public function rechazar(Request $request, $id)
    {
        try {
            $pago = Pago::find($id);

            if (!$pago) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pago no encontrado'
                ], 404);
            }

            $pago->update([
                'estado' => 'rechazado',
                'motivo_rechazo' => $request->motivo,
                'rechazado_by' => Auth::id(),
                'rechazado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $pago->fresh(),
                'message' => 'Pago rechazado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de pagos
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_pagos' => Pago::count(),
                'pagos_pendientes' => Pago::where('estado', 'pendiente')->count(),
                'pagos_aprobados' => Pago::where('estado', 'aprobado')->count(),
                'pagos_rechazados' => Pago::where('estado', 'rechazado')->count(),
                'monto_total' => Pago::where('estado', 'aprobado')->sum('monto'),
                'por_metodo' => Pago::selectRaw('metodo_pago, COUNT(*) as total, SUM(monto) as monto_total')
                    ->where('estado', 'aprobado')
                    ->groupBy('metodo_pago')
                    ->get(),
                'pagos_por_mes' => Pago::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total, SUM(monto) as monto')
                    ->where('estado', 'aprobado')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get()
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
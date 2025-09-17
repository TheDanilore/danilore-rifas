<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Boleto;
use App\Models\Rifa;
use App\Models\Pago;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class VentaController extends Controller
{
    /**
     * Crear una nueva venta (reservar boletos)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rifa_codigo' => 'required|string',
            'numeros_boletos' => 'required|array|min:1',
            'numeros_boletos.*' => 'required|string',
            'comprador_nombre' => 'required|string|max:255',
            'comprador_email' => 'required|email|max:255',
            'comprador_telefono' => 'required|string|max:15',
            'comprador_tipo_documento' => 'required|in:dni,ce,passport,ruc,otros',
            'comprador_numero_documento' => 'required|string|max:20',
            'metodo_pago' => 'required|in:yape,plin,transferencia,efectivo'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verificar autenticación
            /** @var User|null $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            // Verificar rifa
            $rifa = Rifa::where('codigo_unico', $request->rifa_codigo)
                        ->where('estado', 'en_venta')
                        ->firstOrFail();

            $numerosRequeridos = $request->numeros_boletos;
            $cantidadBoletos = count($numerosRequeridos);

            // Verificar disponibilidad de números
            $numerosOcupados = Boleto::where('rifa_id', $rifa->id)
                ->whereIn('numero', $numerosRequeridos)
                ->where(function($query) {
                    $query->where('estado', 'pagado')
                          ->orWhere(function($q) {
                              $q->where('estado', 'reservado')
                                ->where('fecha_expiracion_reserva', '>', now());
                          });
                })
                ->pluck('numero')
                ->toArray();

            if (!empty($numerosOcupados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos números ya están ocupados',
                    'numeros_ocupados' => $numerosOcupados
                ], 409);
            }

            // Crear venta
            $venta = Venta::create([
                'user_id' => $user->id,
                'rifa_id' => $rifa->id,
                'codigo_venta' => Venta::generarCodigoVenta(),
                'cantidad_boletos' => $cantidadBoletos,
                'subtotal' => $rifa->precio_boleto * $cantidadBoletos,
                'total' => $rifa->precio_boleto * $cantidadBoletos,
                'estado' => 'pendiente',
                'metodo_pago' => $request->metodo_pago,
                'fecha_expiracion' => now()->addMinutes(15), // 15 minutos para pagar
                'comprador_nombre' => $request->comprador_nombre,
                'comprador_email' => $request->comprador_email,
                'comprador_telefono' => $request->comprador_telefono,
                'comprador_tipo_documento' => $request->comprador_tipo_documento,
                'comprador_numero_documento' => $request->comprador_numero_documento
            ]);

            // Crear boletos reservados
            foreach ($numerosRequeridos as $numero) {
                Boleto::create([
                    'rifa_id' => $rifa->id,
                    'user_id' => $user->id,
                    'venta_id' => $venta->id,
                    'numero' => $numero,
                    'precio_pagado' => $rifa->precio_boleto,
                    'estado' => 'reservado',
                    'fecha_reserva' => now(),
                    'fecha_expiracion_reserva' => now()->addMinutes(15),
                    'codigo_verificacion' => Boleto::generarCodigoVerificacion()
                ]);
            }

            DB::commit();

            $venta->load(['boletos', 'rifa']);

            return response()->json([
                'success' => true,
                'message' => 'Venta creada correctamente',
                'data' => $venta
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar pago de una venta
     */
    public function confirmarPago(Request $request, $codigoVenta): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'numero_operacion' => 'required|string|max:50',
            'monto_pagado' => 'required|numeric|min:0',
            'comprobante' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $venta = Venta::where('codigo_venta', $codigoVenta)
                         ->where('estado', 'pendiente')
                         ->firstOrFail();

            // Verificar que no esté expirada
            if ($venta->estaExpirada()) {
                return response()->json([
                    'success' => false,
                    'message' => 'La venta ha expirado'
                ], 410);
            }

            // Subir comprobante
            $comprobante = $request->file('comprobante');
            $nombreArchivo = 'comprobante_' . $venta->codigo_venta . '.' . $comprobante->getClientOriginalExtension();
            $rutaComprobante = $comprobante->storeAs('comprobantes', $nombreArchivo, 'public');

            // Crear registro de pago
            $pago = Pago::create([
                'venta_id' => $venta->id,
                'metodo_pago' => $venta->metodo_pago,
                'monto' => $request->monto_pagado,
                'numero_operacion' => $request->numero_operacion,
                'fecha_transaccion' => now(),
                'estado' => 'pendiente',
                'comprobante_url' => $rutaComprobante
            ]);

            // Actualizar venta
            $venta->update([
                'fecha_pago' => now(),
                'monto_pagado' => $request->monto_pagado,
                'comprobante_pago' => $rutaComprobante,
                'referencia_pago' => $request->numero_operacion
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Comprobante subido correctamente. Esperando verificación.',
                'data' => [
                    'venta' => $venta,
                    'pago' => $pago
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de una venta
     */
    public function show($codigoVenta): JsonResponse
    {
        $venta = Venta::with(['rifa', 'boletos', 'pagos'])
                     ->where('codigo_venta', $codigoVenta)
                     ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $venta
        ]);
    }

    /**
     * Obtener ventas del usuario autenticado
     */
    public function misVentas(Request $request): JsonResponse
    {
        try {
            /** @var User|null $user */
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $query = Venta::with([
                'rifa.categoria', 
                'rifa.premios',
                'boletos',
                'pagos'
            ])->where('user_id', $user->id);

            // Filtros
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->metodo_pago) {
                $query->where('metodo_pago', $request->metodo_pago);
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
                    $query->orderBy('total', 'desc');
                    break;
                case 'monto_asc':
                    $query->orderBy('total', 'asc');
                    break;
                default: // fecha_desc
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = $request->per_page ?? 10;
            $ventas = $query->paginate($perPage);

            // Formatear ventas para el frontend
            $ventasFormatted = $ventas->getCollection()->map(function($venta) {
                return [
                    'id' => $venta->id,
                    'codigo' => $venta->codigo_venta,
                    'estado' => $venta->estado,
                    'cantidad_boletos' => $venta->cantidad_boletos,
                    'total' => $venta->total,
                    'metodo_pago' => $venta->metodo_pago,
                    'fecha_pago' => $venta->fecha_pago,
                    'created_at' => $venta->created_at,
                    'updated_at' => $venta->updated_at,
                    'rifa' => [
                        'id' => $venta->rifa->id,
                        'nombre' => $venta->rifa->nombre,
                        'descripcion' => $venta->rifa->descripcion,
                        'imagen' => $venta->rifa->imagen_url,
                        'estado' => $venta->rifa->estado,
                        'fecha_sorteo' => $venta->rifa->fecha_sorteo,
                        'precio_boleto' => $venta->rifa->precio_boleto,
                        'categoria' => $venta->rifa->categoria ? [
                            'id' => $venta->rifa->categoria->id,
                            'nombre' => $venta->rifa->categoria->nombre
                        ] : null
                    ],
                    'boletos' => $venta->boletos->map(function($boleto) {
                        return [
                            'id' => $boleto->id,
                            'numero' => $boleto->numero,
                            'precio' => $boleto->precio_pagado,
                            'estado' => $boleto->estado
                        ];
                    }),
                    'pagos' => $venta->pagos->map(function($pago) {
                        return [
                            'id' => $pago->id,
                            'referencia' => $pago->referencia,
                            'monto' => $pago->monto,
                            'estado' => $pago->estado,
                            'metodo' => $pago->metodo_pago,
                            'created_at' => $pago->created_at
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'ventas' => $ventasFormatted,
                    'pagination' => [
                        'total' => $ventas->total(),
                        'current_page' => $ventas->currentPage(),
                        'per_page' => $ventas->perPage(),
                        'last_page' => $ventas->lastPage(),
                        'from' => $ventas->firstItem(),
                        'to' => $ventas->lastItem()
                    ]
                ],
                'message' => 'Ventas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Obtener todas las ventas (administrador)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Venta::with(['user', 'rifa', 'boletos', 'pagos'])
                         ->orderBy('created_at', 'desc');

            // Filtros
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->has('metodo_pago')) {
                $query->where('metodo_pago', $request->metodo_pago);
            }

            if ($request->has('rifa_id')) {
                $query->where('rifa_id', $request->rifa_id);
            }

            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin]);
            }

            $ventas = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $ventas,
                'message' => 'Ventas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar estado de una venta (administrador)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'estado' => 'sometimes|required|in:pendiente,pagada,cancelada,expirada',
                'observaciones_admin' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $venta = Venta::with(['boletos', 'rifa'])->find($id);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada'
                ], 404);
            }

            DB::beginTransaction();

            $estadoAnterior = $venta->estado;

            // Actualizar venta
            $venta->update([
                'estado' => $request->estado ?? $venta->estado,
                'observaciones_admin' => $request->observaciones_admin,
                'fecha_actualizacion_admin' => now()
            ]);

            // Si se confirma la venta, actualizar boletos
            if ($request->estado === 'pagada' && $estadoAnterior !== 'pagada') {
                $venta->boletos()->update([
                    'estado' => 'pagado',
                    'fecha_pago' => now()
                ]);
            }

            // Si se cancela la venta, liberar boletos
            if ($request->estado === 'cancelada' && in_array($estadoAnterior, ['pendiente', 'reservado'])) {
                $venta->boletos()->update([
                    'estado' => 'disponible',
                    'user_id' => null,
                    'venta_id' => null,
                    'fecha_liberacion' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $venta->fresh(['boletos', 'rifa', 'user']),
                'message' => 'Venta actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una venta (administrador)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $venta = Venta::with(['boletos', 'pagos'])->find($id);

            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada'
                ], 404);
            }

            // Solo se pueden eliminar ventas canceladas o expiradas
            if (!in_array($venta->estado, ['cancelada', 'expirada'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar ventas canceladas o expiradas'
                ], 422);
            }

            DB::beginTransaction();

            // Liberar boletos
            $venta->boletos()->update([
                'estado' => 'disponible',
                'user_id' => null,
                'venta_id' => null,
                'fecha_liberacion' => now()
            ]);

            // Eliminar pagos asociados
            $venta->pagos()->delete();

            // Eliminar venta
            $venta->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Venta eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de ventas
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $estadisticas = [
                'total_ventas' => Venta::count(),
                'ventas_pagadas' => Venta::where('estado', 'pagada')->count(),
                'ventas_pendientes' => Venta::where('estado', 'pendiente')->count(),
                'ventas_canceladas' => Venta::where('estado', 'cancelada')->count(),
                'ventas_expiradas' => Venta::where('estado', 'expirada')->count(),
                'ingresos_totales' => Venta::where('estado', 'pagada')->sum('total'),
                'ingresos_mes_actual' => Venta::where('estado', 'pagada')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total'),
                'ventas_por_metodo_pago' => Venta::select('metodo_pago')
                    ->selectRaw('COUNT(*) as cantidad')
                    ->selectRaw('SUM(total) as monto_total')
                    ->where('estado', 'pagada')
                    ->groupBy('metodo_pago')
                    ->get(),
                'ventas_por_mes' => Venta::select(
                        DB::raw('YEAR(created_at) as año'),
                        DB::raw('MONTH(created_at) as mes'),
                        DB::raw('COUNT(*) as cantidad'),
                        DB::raw('SUM(total) as monto')
                    )
                    ->where('estado', 'pagada')
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->get(),
                'top_rifas_ventas' => Venta::with('rifa')
                    ->select('rifa_id')
                    ->selectRaw('COUNT(*) as total_ventas')
                    ->selectRaw('SUM(total) as ingresos')
                    ->where('estado', 'pagada')
                    ->groupBy('rifa_id')
                    ->orderBy('total_ventas', 'desc')
                    ->take(10)
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

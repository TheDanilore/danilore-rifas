<?php

namespace App\Http\Controllers\Api;

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
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }

        $ventas = Venta::with(['rifa', 'boletos'])
                      ->where('user_id', $user->id)
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $ventas
        ]);
    }
}

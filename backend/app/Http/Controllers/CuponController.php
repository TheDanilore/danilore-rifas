<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CuponController extends Controller
{
    /**
     * Mostrar todos los cupones (público - solo activos y válidos)
     */
    public function index()
    {
        try {
            $cupones = Cupon::where('estado', 'activo')
                           ->where('fecha_inicio', '<=', now())
                           ->where('fecha_fin', '>=', now())
                           ->where('usos_restantes', '>', 0)
                           ->orderBy('descuento', 'desc')
                           ->get();

            return response()->json([
                'success' => true,
                'data' => $cupones,
                'message' => 'Cupones obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cupones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validar un cupón específico
     */
    public function validar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:50',
                'monto_compra' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cupon = Cupon::where('codigo', $request->codigo)->first();

            if (!$cupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no encontrado'
                ], 404);
            }

            // Verificar estado
            if ($cupon->estado !== 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón no está activo'
                ], 400);
            }

            // Verificar fechas
            $ahora = now();
            if ($cupon->fecha_inicio > $ahora) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón aún no está disponible'
                ], 400);
            }

            if ($cupon->fecha_fin < $ahora) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ha expirado'
                ], 400);
            }

            // Verificar usos restantes
            if ($cupon->usos_restantes <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El cupón ha alcanzado su límite de usos'
                ], 400);
            }

            // Verificar monto mínimo
            if ($request->monto_compra && $cupon->monto_minimo > $request->monto_compra) {
                return response()->json([
                    'success' => false,
                    'message' => "El monto mínimo para este cupón es $" . number_format($cupon->monto_minimo, 2)
                ], 400);
            }

            // Verificar uso por usuario si está autenticado
            if (Auth::check()) {
                $usuarioYaUso = Venta::where('user_id', Auth::id())
                                   ->where('cupon_id', $cupon->id)
                                   ->exists();

                if ($usuarioYaUso && $cupon->uso_por_usuario == 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya has utilizado este cupón anteriormente'
                    ], 400);
                }
            }

            // Calcular descuento
            $descuento = 0;
            if ($request->monto_compra) {
                if ($cupon->tipo_descuento === 'porcentaje') {
                    $descuento = ($request->monto_compra * $cupon->descuento) / 100;
                } else {
                    $descuento = $cupon->descuento;
                }

                // Aplicar descuento máximo si existe
                if ($cupon->descuento_maximo && $descuento > $cupon->descuento_maximo) {
                    $descuento = $cupon->descuento_maximo;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cupon' => $cupon,
                    'descuento_aplicado' => $descuento,
                    'monto_final' => $request->monto_compra ? max(0, $request->monto_compra - $descuento) : null,
                    'valido' => true
                ],
                'message' => 'Cupón válido'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al validar cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aplicar un cupón a una compra
     */
    public function aplicar(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:50',
                'venta_id' => 'required|exists:ventas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $venta = Venta::find($request->venta_id);

            // Verificar que la venta pertenece al usuario autenticado
            if ($venta->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para modificar esta venta'
                ], 403);
            }

            // Verificar que la venta no tenga ya un cupón aplicado
            if ($venta->cupon_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta venta ya tiene un cupón aplicado'
                ], 400);
            }

            // Validar cupón
            $validacionResponse = $this->validar(new Request([
                'codigo' => $request->codigo,
                'monto_compra' => $venta->total
            ]));

            $validacion = json_decode($validacionResponse->getContent(), true);

            if (!$validacion['success']) {
                return $validacionResponse;
            }

            $cupon = Cupon::where('codigo', $request->codigo)->first();

            DB::transaction(function() use ($cupon, $venta, $validacion) {
                // Aplicar descuento a la venta
                $descuento = $validacion['data']['descuento_aplicado'];
                $venta->cupon_id = $cupon->id;
                $venta->descuento = $descuento;
                $venta->total = max(0, $venta->total - $descuento);
                $venta->save();

                // Decrementar usos restantes del cupón
                $cupon->decrement('usos_restantes');
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'venta' => $venta->fresh(),
                    'cupon' => $cupon->fresh(),
                    'descuento_aplicado' => $validacion['data']['descuento_aplicado']
                ],
                'message' => 'Cupón aplicado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aplicar cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Mostrar todos los cupones para administradores
     */
    public function admin()
    {
        try {
            $cupones = Cupon::with(['ventas' => function($query) {
                $query->select('id', 'cupon_id', 'total', 'descuento', 'created_at');
            }])
            ->withCount('ventas')
            ->orderBy('created_at', 'desc')
            ->get();

            // Calcular estadísticas para cada cupón
            $cupones->each(function($cupon) {
                $cupon->total_descuento_otorgado = $cupon->ventas->sum('descuento');
                $cupon->ahorro_promedio = $cupon->ventas_count > 0 ? 
                    $cupon->total_descuento_otorgado / $cupon->ventas_count : 0;
            });

            return response()->json([
                'success' => true,
                'data' => $cupones,
                'message' => 'Cupones obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cupones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un cupón específico para administradores
     */
    public function show($id)
    {
        try {
            $cupon = Cupon::with(['ventas.user'])
                         ->withCount('ventas')
                         ->find($id);

            if (!$cupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no encontrado'
                ], 404);
            }

            // Estadísticas adicionales
            $cupon->total_descuento_otorgado = $cupon->ventas->sum('descuento');
            $cupon->usuarios_unicos = $cupon->ventas->pluck('user_id')->unique()->count();

            return response()->json([
                'success' => true,
                'data' => $cupon,
                'message' => 'Cupón obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo cupón
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:50|unique:cupones,codigo',
                'tipo_descuento' => 'required|in:porcentaje,fijo',
                'descuento' => 'required|numeric|min:0',
                'descuento_maximo' => 'nullable|numeric|min:0',
                'monto_minimo' => 'nullable|numeric|min:0',
                'fecha_inicio' => 'required|date|after_or_equal:today',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'usos_totales' => 'required|integer|min:1',
                'uso_por_usuario' => 'required|integer|min:1|max:10',
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|in:activo,inactivo'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validaciones adicionales
            if ($request->tipo_descuento === 'porcentaje' && $request->descuento > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'El descuento porcentual no puede ser mayor a 100%'
                ], 422);
            }

            $cupon = Cupon::create([
                'codigo' => strtoupper($request->codigo),
                'tipo_descuento' => $request->tipo_descuento,
                'descuento' => $request->descuento,
                'descuento_maximo' => $request->descuento_maximo,
                'monto_minimo' => $request->monto_minimo ?? 0,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'usos_totales' => $request->usos_totales,
                'usos_restantes' => $request->usos_totales,
                'uso_por_usuario' => $request->uso_por_usuario,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
                'created_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $cupon,
                'message' => 'Cupón creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar un cupón existente
     */
    public function update(Request $request, $id)
    {
        try {
            $cupon = Cupon::find($id);

            if (!$cupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo' => 'required|string|max:50|unique:cupones,codigo,' . $id,
                'tipo_descuento' => 'required|in:porcentaje,fijo',
                'descuento' => 'required|numeric|min:0',
                'descuento_maximo' => 'nullable|numeric|min:0',
                'monto_minimo' => 'nullable|numeric|min:0',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after:fecha_inicio',
                'usos_totales' => 'required|integer|min:1',
                'uso_por_usuario' => 'required|integer|min:1|max:10',
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|in:activo,inactivo'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validaciones adicionales
            if ($request->tipo_descuento === 'porcentaje' && $request->descuento > 100) {
                return response()->json([
                    'success' => false,
                    'message' => 'El descuento porcentual no puede ser mayor a 100%'
                ], 422);
            }

            // Calcular usos restantes si se cambian los usos totales
            $usosUtilizados = $cupon->usos_totales - $cupon->usos_restantes;
            $nuevosUsosRestantes = max(0, $request->usos_totales - $usosUtilizados);

            $cupon->update([
                'codigo' => strtoupper($request->codigo),
                'tipo_descuento' => $request->tipo_descuento,
                'descuento' => $request->descuento,
                'descuento_maximo' => $request->descuento_maximo,
                'monto_minimo' => $request->monto_minimo ?? 0,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'usos_totales' => $request->usos_totales,
                'usos_restantes' => $nuevosUsosRestantes,
                'uso_por_usuario' => $request->uso_por_usuario,
                'descripcion' => $request->descripcion,
                'estado' => $request->estado,
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $cupon->fresh(),
                'message' => 'Cupón actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un cupón
     */
    public function destroy($id)
    {
        try {
            $cupon = Cupon::find($id);

            if (!$cupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no encontrado'
                ], 404);
            }

            // Verificar si el cupón ha sido utilizado
            if ($cupon->ventas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar un cupón que ya ha sido utilizado'
                ], 400);
            }

            $cupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cupón eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Desactivar un cupón
     */
    public function desactivar($id)
    {
        try {
            $cupon = Cupon::find($id);

            if (!$cupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cupón no encontrado'
                ], 404);
            }

            $cupon->update([
                'estado' => 'inactivo',
                'updated_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => $cupon->fresh(),
                'message' => 'Cupón desactivado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar cupón: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de cupones
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_cupones' => Cupon::count(),
                'cupones_activos' => Cupon::where('estado', 'activo')->count(),
                'cupones_expirados' => Cupon::where('fecha_fin', '<', now())->count(),
                'total_usos' => Venta::whereNotNull('cupon_id')->count(),
                'descuento_total_otorgado' => Venta::whereNotNull('cupon_id')->sum('descuento'),
                'cupones_mas_usados' => Cupon::withCount('ventas')
                    ->orderBy('ventas_count', 'desc')
                    ->take(10)
                    ->get(['id', 'codigo', 'descuento', 'tipo_descuento']),
                'uso_por_mes' => Venta::whereNotNull('cupon_id')
                    ->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
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

    /**
     * Generar código de cupón automático
     */
    public function generarCodigo()
    {
        try {
            do {
                $codigo = 'CUP' . strtoupper(substr(md5(uniqid()), 0, 8));
            } while (Cupon::where('codigo', $codigo)->exists());

            return response()->json([
                'success' => true,
                'data' => ['codigo' => $codigo],
                'message' => 'Código generado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar código: ' . $e->getMessage()
            ], 500);
        }
    }
}
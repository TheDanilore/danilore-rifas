<?php

namespace App\Http\Controllers;

use App\Models\Sorteo;
use App\Models\Rifa;
use App\Models\Venta;
use App\Models\Boleto;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SorteoController extends Controller
{
    /**
     * Obtener sorteos públicos
     */
    public function index(Request $request)
    {
        try {
            $query = Sorteo::with(['rifa.categoria', 'rifa.premios'])
                          ->whereHas('rifa', function($q) {
                              $q->where('estado', 'finalizada');
                          });

            // Filtros
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->categoria_id) {
                $query->whereHas('rifa', function($q) use ($request) {
                    $q->where('categoria_id', $request->categoria_id);
                });
            }

            $sorteos = $query->orderBy('created_at', 'desc')
                           ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $sorteos,
                'message' => 'Sorteos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sorteos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un sorteo específico
     */
    public function show($id)
    {
        try {
            $sorteo = Sorteo::with([
                'rifa.categoria',
                'rifa.premios.nivel',
                'ganadores.boleto.venta.user',
                'ganadores.premio'
            ])->find($id);

            if (!$sorteo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorteo no encontrado'
                ], 404);
            }

            // Agregar estadísticas adicionales
            $sorteo->total_participantes = $sorteo->rifa->ventas()->distinct('user_id')->count();
            $sorteo->total_boletos_participantes = $sorteo->rifa->boletos()->count();

            return response()->json([
                'success' => true,
                'data' => $sorteo,
                'message' => 'Sorteo obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sorteo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si un usuario ganó en un sorteo
     */
    public function verificarGanador($sorteoId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debes estar autenticado para verificar'
                ], 401);
            }

            $sorteo = Sorteo::find($sorteoId);
            if (!$sorteo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorteo no encontrado'
                ], 404);
            }

            $userId = Auth::id();
            
            // Verificar si el usuario tiene boletos en esta rifa
            $boletosUsuario = Boleto::whereHas('venta', function($query) use ($userId, $sorteo) {
                $query->where('user_id', $userId)
                      ->where('rifa_id', $sorteo->rifa_id);
            })->pluck('numero')->toArray();

            if (empty($boletosUsuario)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'participo' => false,
                        'gano' => false,
                        'premios' => []
                    ],
                    'message' => 'No participaste en este sorteo'
                ]);
            }

            // Verificar si ganó algún premio
            $premiosGanados = $sorteo->ganadores()
                                   ->whereHas('boleto.venta', function($query) use ($userId) {
                                       $query->where('user_id', $userId);
                                   })
                                   ->with(['premio.nivel', 'boleto'])
                                   ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'participo' => true,
                    'gano' => $premiosGanados->isNotEmpty(),
                    'premios' => $premiosGanados,
                    'boletos_participantes' => $boletosUsuario,
                    'total_boletos' => count($boletosUsuario)
                ],
                'message' => $premiosGanados->isNotEmpty() ? 
                    '¡Felicidades! Ganaste en este sorteo' : 
                    'No ganaste en este sorteo, ¡suerte para la próxima!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar ganador: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Mostrar todos los sorteos (administrador)
     */
    public function admin(Request $request)
    {
        try {
            $query = Sorteo::with(['rifa', 'ganadores.premio'])
                         ->withCount('ganadores');

            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->rifa_id) {
                $query->where('rifa_id', $request->rifa_id);
            }

            $sorteos = $query->orderBy('created_at', 'desc')
                           ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $sorteos,
                'message' => 'Sorteos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener sorteos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejecutar sorteo manual
     */
    public function ejecutar(Request $request, $rifaId)
    {
        try {
            $rifa = Rifa::with(['premios.nivel', 'boletos.venta'])->find($rifaId);

            if (!$rifa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rifa no encontrada'
                ], 404);
            }

            // Verificar que la rifa esté lista para sorteo
            if ($rifa->estado !== 'completa') {
                return response()->json([
                    'success' => false,
                    'message' => 'La rifa debe estar completa para realizar el sorteo'
                ], 400);
            }

            // Verificar que no tenga sorteo previo
            if ($rifa->sorteos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta rifa ya tiene un sorteo realizado'
                ], 400);
            }

            // Verificar que tenga boletos vendidos
            if ($rifa->boletos()->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay boletos vendidos para esta rifa'
                ], 400);
            }

            $sorteoId = null;
            
            DB::transaction(function() use ($rifa, $request, &$sorteoId) {
                // Crear el sorteo
                $sorteo = Sorteo::create([
                    'rifa_id' => $rifa->id,
                    'fecha_sorteo' => now(),
                    'tipo' => 'manual',
                    'estado' => 'completado',
                    'ejecutado_by' => Auth::id(),
                    'metodo_sorteo' => $request->metodo ?? 'aleatorio_simple',
                    'semilla_aleatoria' => rand(100000, 999999),
                    'total_boletos_participantes' => $rifa->boletos()->count()
                ]);

                $sorteoId = $sorteo->id;

                // Ejecutar el sorteo para cada premio
                $this->ejecutarSorteoPremios($sorteo, $rifa);

                // Actualizar estado de la rifa
                $rifa->update([
                    'estado' => 'finalizada',
                    'fecha_sorteo' => now(),
                    'estado_sorteo' => 'completado'
                ]);

                // Enviar notificaciones
                $this->enviarNotificacionesSorteo($sorteo);
            });

            $sorteo = Sorteo::with([
                'rifa',
                'ganadores.boleto.venta.user',
                'ganadores.premio.nivel'
            ])->find($sorteoId);

            return response()->json([
                'success' => true,
                'data' => $sorteo,
                'message' => 'Sorteo ejecutado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar sorteo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Programar sorteo automático
     */
    public function programar(Request $request, $rifaId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fecha_sorteo' => 'required|date|after:now',
                'metodo' => 'in:aleatorio_simple,aleatorio_avanzado,manual'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $rifa = Rifa::find($rifaId);

            if (!$rifa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rifa no encontrada'
                ], 404);
            }

            // Verificar que no tenga sorteo programado
            if ($rifa->sorteos()->where('estado', 'programado')->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta rifa ya tiene un sorteo programado'
                ], 400);
            }

            $sorteo = Sorteo::create([
                'rifa_id' => $rifa->id,
                'fecha_sorteo' => $request->fecha_sorteo,
                'tipo' => 'automatico',
                'estado' => 'programado',
                'programado_by' => Auth::id(),
                'metodo_sorteo' => $request->metodo ?? 'aleatorio_simple'
            ]);

            // Aquí programarías el job para ejecutar automáticamente
            // SorteoAutomaticoJob::dispatch($sorteo)->delay($request->fecha_sorteo);

            return response()->json([
                'success' => true,
                'data' => $sorteo,
                'message' => 'Sorteo programado exitosamente para ' . $request->fecha_sorteo
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al programar sorteo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar sorteo programado
     */
    public function cancelar($id)
    {
        try {
            $sorteo = Sorteo::find($id);

            if (!$sorteo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorteo no encontrado'
                ], 404);
            }

            if ($sorteo->estado !== 'programado') {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar sorteos programados'
                ], 400);
            }

            $sorteo->update([
                'estado' => 'cancelado',
                'cancelado_by' => Auth::id(),
                'cancelado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $sorteo->fresh(),
                'message' => 'Sorteo cancelado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar sorteo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Re-ejecutar sorteo (en caso de error)
     */
    public function reejecutar($id)
    {
        try {
            $sorteo = Sorteo::with('rifa.premios')->find($id);

            if (!$sorteo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sorteo no encontrado'
                ], 404);
            }

            if ($sorteo->estado === 'completado') {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede re-ejecutar un sorteo completado'
                ], 400);
            }

            DB::transaction(function() use ($sorteo) {
                // Limpiar ganadores anteriores si existen
                $sorteo->ganadores()->delete();

                // Re-ejecutar sorteo
                $this->ejecutarSorteoPremios($sorteo, $sorteo->rifa);

                $sorteo->update([
                    'estado' => 'completado',
                    'fecha_sorteo' => now(),
                    'reejecutado' => true,
                    'reejecutado_by' => Auth::id(),
                    'reejecutado_at' => now()
                ]);
            });

            $sorteo = $sorteo->fresh(['ganadores.boleto.venta.user', 'ganadores.premio']);

            return response()->json([
                'success' => true,
                'data' => $sorteo,
                'message' => 'Sorteo re-ejecutado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al re-ejecutar sorteo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de sorteos
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_sorteos' => Sorteo::count(),
                'completados' => Sorteo::where('estado', 'completado')->count(),
                'programados' => Sorteo::where('estado', 'programado')->count(),
                'cancelados' => Sorteo::where('estado', 'cancelado')->count(),
                'sorteos_por_mes' => Sorteo::selectRaw('YEAR(fecha_sorteo) as año, MONTH(fecha_sorteo) as mes, COUNT(*) as total')
                    ->whereNotNull('fecha_sorteo')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get(),
                'ganadores_unicos' => DB::table('ganadores_sorteo')
                    ->join('boletos', 'ganadores_sorteo.boleto_id', '=', 'boletos.id')
                    ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
                    ->distinct('ventas.user_id')
                    ->count(),
                'total_premios_entregados' => DB::table('ganadores_sorteo')->count(),
                'rifas_con_sorteo' => Sorteo::distinct('rifa_id')->count(),
                'promedio_participantes' => round(
                    Sorteo::avg('total_boletos_participantes'), 2
                )
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

    // ===============================
    // MÉTODOS PRIVADOS
    // ===============================

    /**
     * Ejecutar sorteo de premios
     */
    private function ejecutarSorteoPremios($sorteo, $rifa)
    {
        $boletosDisponibles = $rifa->boletos()->pluck('id')->toArray();
        $boletosUsados = [];

        // Ordenar premios por nivel (mayor importancia primero)
        $premios = $rifa->premios()->with('nivel')->get()
                       ->sortBy('nivel.orden');

        foreach ($premios as $premio) {
            for ($i = 0; $i < $premio->cantidad; $i++) {
                // Filtrar boletos no utilizados
                $boletosDisponiblesParaSorteo = array_diff($boletosDisponibles, $boletosUsados);

                if (empty($boletosDisponiblesParaSorteo)) {
                    break; // No hay más boletos disponibles
                }

                // Seleccionar boleto ganador aleatoriamente
                $boletoGanadorId = $boletosDisponiblesParaSorteo[array_rand($boletosDisponiblesParaSorteo)];
                $boletosUsados[] = $boletoGanadorId;

                // Registrar ganador
                DB::table('ganadores_sorteo')->insert([
                    'sorteo_id' => $sorteo->id,
                    'boleto_id' => $boletoGanadorId,
                    'premio_id' => $premio->id,
                    'posicion' => $i + 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Enviar notificaciones del sorteo
     */
    private function enviarNotificacionesSorteo($sorteo)
    {
        $ganadores = DB::table('ganadores_sorteo')
                      ->join('boletos', 'ganadores_sorteo.boleto_id', '=', 'boletos.id')
                      ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
                      ->join('premios', 'ganadores_sorteo.premio_id', '=', 'premios.id')
                      ->where('ganadores_sorteo.sorteo_id', $sorteo->id)
                      ->select('ventas.user_id', 'premios.nombre as premio_nombre', 'boletos.numero')
                      ->get();

        foreach ($ganadores as $ganador) {
            Notificacion::create([
                'user_id' => $ganador->user_id,
                'titulo' => '¡Felicidades! Has ganado un premio',
                'mensaje' => "Tu boleto #{$ganador->numero} ha sido seleccionado ganador del premio: {$ganador->premio_nombre}",
                'tipo' => 'sorteo_ganador',
                'data' => [
                    'sorteo_id' => $sorteo->id,
                    'boleto_numero' => $ganador->numero,
                    'premio' => $ganador->premio_nombre
                ]
            ]);
        }

        // Notificar a participantes no ganadores
        $participantesNoGanadores = $sorteo->rifa->ventas()
                                         ->whereNotIn('user_id', $ganadores->pluck('user_id'))
                                         ->distinct('user_id')
                                         ->pluck('user_id');

        foreach ($participantesNoGanadores as $userId) {
            Notificacion::create([
                'user_id' => $userId,
                'titulo' => 'Sorteo realizado',
                'mensaje' => "El sorteo de \"{$sorteo->rifa->titulo}\" ha sido realizado. ¡Suerte para la próxima!",
                'tipo' => 'sorteo_realizado',
                'data' => [
                    'sorteo_id' => $sorteo->id,
                    'rifa_id' => $sorteo->rifa_id
                ]
            ]);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Premio;
use App\Models\Rifa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PremioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * Obtener premio específico por rifa ID y código de premio
     */
    public function show(string $rifaId, string $codigoPremio): JsonResponse
    {
        try {
            // Buscar la rifa
            $rifa = Rifa::findOrFail($rifaId);
            
            // Buscar el premio específico con sus relaciones
            $premio = Premio::with([
                'niveles' => function($query) {
                    $query->orderBy('orden');
                },
                'progreso',
                'rifa',
                'premioRequerido'
            ])
            ->where('rifa_id', $rifaId)
            ->where('codigo', $codigoPremio)
            ->firstOrFail();

            // Calcular el progreso de cada nivel usando progreso_premios
            $nivelesConProgreso = $premio->niveles->map(function($nivel) use ($rifa, $premio) {
                // Buscar el progreso específico para este nivel
                $progresoNivel = \App\Models\ProgresoPremio::where('premio_id', $premio->id)
                                                           ->where('nivel_id', $nivel->id)
                                                           ->first();
                
                // Si no existe progreso, crear valores por defecto
                if (!$progresoNivel) {
                    $progreso = $rifa->boletos_vendidos >= $nivel->tickets_necesarios ? 100 : 
                               ($rifa->boletos_vendidos / $nivel->tickets_necesarios) * 100;
                    $completado = $rifa->boletos_vendidos >= $nivel->tickets_necesarios;
                    $ticketsRestantes = max(0, $nivel->tickets_necesarios - $rifa->boletos_vendidos);
                } else {
                    $progreso = $progresoNivel->porcentaje_completado;
                    $completado = $progresoNivel->objetivo_alcanzado;
                    $ticketsRestantes = $progresoNivel->tickets_restantes;
                }
                
                // Determinar si es el nivel actual
                $esActual = !$completado && 
                           !$premio->niveles->where('orden', '<', $nivel->orden)
                                           ->contains(function($nivelAnterior) use ($rifa) {
                                               return $rifa->boletos_vendidos < $nivelAnterior->tickets_necesarios;
                                           });
                
                return [
                    'id' => $nivel->id,
                    'titulo' => $nivel->titulo,
                    'descripcion' => $nivel->descripcion,
                    'tickets_necesarios' => $nivel->tickets_necesarios,
                    'valor_aproximado' => $nivel->valor_aproximado,
                    'imagen' => $nivel->imagen,
                    'orden' => $nivel->orden,
                    'completado' => $completado,
                    'es_actual' => $esActual,
                    'progreso' => round($progreso, 2),
                    'tickets_restantes' => $ticketsRestantes,
                    'tickets_actuales' => $progresoNivel ? $progresoNivel->tickets_actuales : $rifa->boletos_vendidos,
                    'objetivo_alcanzado' => $progresoNivel ? $progresoNivel->objetivo_alcanzado : $completado,
                    'fecha_alcanzado' => $progresoNivel ? $progresoNivel->fecha_alcanzado : null
                ];
            });

            // Determinar el estado del premio
            $todosNivelesCompletados = $nivelesConProgreso->every(fn($nivel) => $nivel['completado']);
            $hayNivelesCompletados = $nivelesConProgreso->some(fn($nivel) => $nivel['completado']);

            // Usar el estado de la base de datos directamente
            $estadoPremio = $premio->estado;
            
            // Solo actualizar si todos los niveles están completados y el estado no es 'completado'
            if ($todosNivelesCompletados && $premio->estado !== 'completado') {
                $estadoPremio = 'completado';
            }

            // Calcular si el premio está desbloqueado
            $estaDesbloqueado = $premio->puedeDesbloquearse();

            return response()->json([
                'success' => true,
                'data' => [
                    'premio' => [
                        'id' => $premio->id,
                        'codigo' => $premio->codigo,
                        'titulo' => $premio->titulo,
                        'descripcion' => $premio->descripcion,
                        'imagen_principal' => $premio->imagen_principal,
                        'media_gallery' => $premio->media_gallery,
                        'orden' => $premio->orden,
                        'estado' => $estadoPremio,
                        'desbloqueado' => $estaDesbloqueado,
                        'completado' => $todosNivelesCompletados,
                        'fecha_desbloqueo' => $premio->fecha_desbloqueo,
                        'fecha_completado' => $premio->fecha_completado,
                        'premio_requerido' => $premio->premioRequerido ? [
                            'id' => $premio->premioRequerido->id,
                            'titulo' => $premio->premioRequerido->titulo,
                            'codigo' => $premio->premioRequerido->codigo
                        ] : null,
                        'niveles' => $nivelesConProgreso
                    ],
                    'rifa' => [
                        'id' => $rifa->id,
                        'titulo' => $rifa->titulo,
                        'descripcion' => $rifa->descripcion,
                        'codigo_unico' => $rifa->codigo_unico,
                        'precio_boleto' => $rifa->precio_boleto,
                        'boletos_vendidos' => $rifa->boletos_vendidos,
                        'boletos_minimos' => $rifa->boletos_minimos,
                        'fecha_sorteo' => $rifa->fecha_sorteo,
                        'estado' => $rifa->estado,
                        'tipo' => $rifa->tipo,
                        'porcentaje_completado' => round(($rifa->boletos_vendidos / $rifa->boletos_minimos) * 100, 2),
                        'total_premios' => $rifa->premios()->count(),
                        'premios' => $rifa->premios()->select('id', 'codigo', 'titulo', 'orden')->orderBy('orden')->get()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Premio no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

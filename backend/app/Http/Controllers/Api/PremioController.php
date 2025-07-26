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

            // Calcular el progreso de cada nivel
            $nivelesConProgreso = $premio->niveles->map(function($nivel) use ($rifa, $premio) {
                $progreso = $rifa->boletos_vendidos >= $nivel->tickets_necesarios ? 100 : 
                           ($rifa->boletos_vendidos / $nivel->tickets_necesarios) * 100;
                
                return [
                    'id' => $nivel->id,
                    'titulo' => $nivel->titulo,
                    'descripcion' => $nivel->descripcion,
                    'tickets_necesarios' => $nivel->tickets_necesarios,
                    'valor_aproximado' => $nivel->valor_aproximado,
                    'imagen' => $nivel->imagen,
                    'orden' => $nivel->orden,
                    'completado' => $rifa->boletos_vendidos >= $nivel->tickets_necesarios,
                    'es_actual' => !($rifa->boletos_vendidos >= $nivel->tickets_necesarios) && 
                                  !$premio->niveles->where('orden', '<', $nivel->orden)
                                         ->where('tickets_necesarios', '>', $rifa->boletos_vendidos)->count(),
                    'progreso' => round($progreso, 2),
                    'tickets_restantes' => max(0, $nivel->tickets_necesarios - $rifa->boletos_vendidos)
                ];
            });

            // Determinar el estado del premio
            $todosNivelesCompletados = $nivelesConProgreso->every(fn($nivel) => $nivel['completado']);
            $hayNivelesCompletados = $nivelesConProgreso->some(fn($nivel) => $nivel['completado']);

            $estadoPremio = 'bloqueado';
            if ($premio->desbloqueado) {
                if ($todosNivelesCompletados) {
                    $estadoPremio = 'completado';
                } else {
                    $estadoPremio = 'en_progreso';
                }
            }

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
                        'desbloqueado' => $premio->desbloqueado,
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
                        'porcentaje_completado' => round(($rifa->boletos_vendidos / $rifa->boletos_minimos) * 100, 2)
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

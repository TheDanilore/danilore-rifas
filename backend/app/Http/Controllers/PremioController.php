<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Premio;
use App\Models\Rifa;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PremioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Premio::with(['rifa']);

            // Filtrar por rifa si se especifica
            if ($request->has('rifa_id')) {
                $query->where('rifa_id', $request->rifa_id);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'posicion');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $premios = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $premios,
                'message' => 'Premios obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener premios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rifa_id' => 'required|exists:rifas,id',
                'codigo_premio' => 'required|string|unique:premios,codigo_premio',
                'nombre' => 'required|string|max:255',
                'descripcion' => 'nullable|string',
                'valor_estimado' => 'required|numeric|min:0',
                'imagen_principal' => 'nullable|string',
                'imagenes_galeria' => 'nullable|array',
                'es_principal' => 'sometimes|boolean',
                'posicion' => 'sometimes|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $premio = Premio::create([
                'rifa_id' => $request->rifa_id,
                'codigo_premio' => $request->codigo_premio,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'valor_estimado' => $request->valor_estimado,
                'imagen_principal' => $request->imagen_principal,
                'imagenes_galeria' => $request->imagenes_galeria ? json_encode($request->imagenes_galeria) : null,
                'es_principal' => $request->es_principal ?? false,
                'posicion' => $request->posicion ?? 1
            ]);

            return response()->json([
                'success' => true,
                'data' => $premio,
                'message' => 'Premio creado exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear premio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * Obtener premio específico por código único de rifa y código de premio
     */
    public function show(string $rifaCodigoUnico, string $codigoPremio): JsonResponse
    {
        try {
            // Buscar la rifa por codigo_unico en lugar de ID
            $rifa = Rifa::where('codigo_unico', $rifaCodigoUnico)->firstOrFail();
            
            // Verificar si la rifa permite participación
            $rifaPuedeParticipar = $this->puedeParticipar($rifa);
            $rifaEstaBloquada = !$rifaPuedeParticipar;
            $rifaRazonBloqueo = $rifaEstaBloquada ? $this->getRazonBloqueo($rifa) : null;
            
            // Buscar el premio específico con sus relaciones
            $premio = Premio::with([
                'niveles' => function($query) {
                    $query->orderBy('orden');
                },
                'progreso',
                'rifa',
                'premioRequerido'
            ])
            ->where('rifa_id', $rifa->id)
            ->where('codigo', $codigoPremio)
            ->firstOrFail();

            // Calcular el progreso de cada nivel usando progreso_premios
            $nivelesConProgreso = $premio->niveles->map(function($nivel) use ($rifa, $premio, $rifaEstaBloquada, $rifaRazonBloqueo) {
                // Si la rifa está bloqueada, todos los niveles están bloqueados
                if ($rifaEstaBloquada) {
                    return [
                        'id' => $nivel->id,
                        'titulo' => $nivel->titulo,
                        'descripcion' => $nivel->descripcion,
                        'tickets_necesarios' => $nivel->tickets_necesarios,
                        'valor_aproximado' => $nivel->valor_aproximado,
                        'imagen' => $nivel->imagen,
                        'media_gallery' => $nivel->media_gallery,
                        'orden' => $nivel->orden,
                        'desbloqueado' => false,
                        'completado' => false,
                        'es_actual' => false,
                        'progreso' => 0,
                        'tickets_restantes' => $nivel->tickets_necesarios,
                        'tickets_actuales' => 0,
                        'objetivo_alcanzado' => false,
                        'fecha_alcanzado' => null,
                        'estado_bloqueo' => 'Rifa Bloqueada',
                        'razon_bloqueo' => $rifaRazonBloqueo
                    ];
                }
                
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
                    'media_gallery' => $nivel->media_gallery, // ✅ Agregar campo media_gallery
                    'orden' => $nivel->orden,
                    'desbloqueado' => $completado, // ✅ Usar 'desbloqueado' para consistencia
                    'completado' => $completado,   // ✅ Mantener ambos por compatibilidad
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
                'message' => 'Premio no encontrado para la rifa ' . $rifaCodigoUnico . ' y premio ' . $codigoPremio . ': ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $premio = Premio::find($id);

            if (!$premio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Premio no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'codigo_premio' => 'sometimes|required|string|unique:premios,codigo_premio,' . $id,
                'nombre' => 'sometimes|required|string|max:255',
                'descripcion' => 'nullable|string',
                'valor_estimado' => 'sometimes|required|numeric|min:0',
                'imagen_principal' => 'nullable|string',
                'imagenes_galeria' => 'nullable|array',
                'es_principal' => 'sometimes|boolean',
                'posicion' => 'sometimes|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only([
                'codigo_premio', 'nombre', 'descripcion', 'valor_estimado',
                'imagen_principal', 'es_principal', 'posicion'
            ]);

            if ($request->has('imagenes_galeria')) {
                $updateData['imagenes_galeria'] = json_encode($request->imagenes_galeria);
            }

            $premio->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $premio->fresh(),
                'message' => 'Premio actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar premio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $premio = Premio::find($id);

            if (!$premio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Premio no encontrado'
                ], 404);
            }

            // Verificar si hay sorteos o ganadores asociados
            if ($premio->sorteos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el premio porque tiene sorteos asociados'
                ], 422);
            }

            $premio->delete();

            return response()->json([
                'success' => true,
                'message' => 'Premio eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar premio: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica si se puede participar en la rifa asociada al premio.
     */
    private function puedeParticipar($rifa)
    {
        // Verificar estado
        if ($rifa->estado !== 'activa') {
            return false;
        }

        // Verificar fechas
        $ahora = now();
        if ($rifa->fecha_inicio && $ahora < $rifa->fecha_inicio) {
            return false;
        }
        if ($rifa->fecha_fin && $ahora > $rifa->fecha_fin) {
            return false;
        }

        // Verificar dependencias de rifas anteriores
        if ($rifa->rifa_dependiente_id) {
            $rifaDependiente = \App\Models\Rifa::find($rifa->rifa_dependiente_id);
            if (!$rifaDependiente || $rifaDependiente->estado !== 'completada') {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene la razón por la cual no se puede participar en la rifa.
     */
    private function getRazonBloqueo($rifa)
    {
        // Verificar estado
        if ($rifa->estado !== 'activa') {
            switch ($rifa->estado) {
                case 'borrador':
                    return 'Rifa en borrador - aún no está disponible';
                case 'programada':
                    return 'Rifa programada - aún no ha iniciado';
                case 'pausada':
                    return 'Rifa pausada temporalmente';
                case 'cancelada':
                    return 'Rifa cancelada';
                case 'completada':
                    return 'Rifa ya finalizada';
                default:
                    return 'Rifa no disponible';
            }
        }

        // Verificar fechas
        $ahora = now();
        if ($rifa->fecha_inicio && $ahora < $rifa->fecha_inicio) {
            return 'Rifa aún no ha iniciado - disponible desde ' . $rifa->fecha_inicio->format('d/m/Y H:i');
        }
        if ($rifa->fecha_fin && $ahora > $rifa->fecha_fin) {
            return 'Rifa ya finalizó el ' . $rifa->fecha_fin->format('d/m/Y H:i');
        }

        // Verificar dependencias
        if ($rifa->rifa_dependiente_id) {
            $rifaDependiente = \App\Models\Rifa::find($rifa->rifa_dependiente_id);
            if (!$rifaDependiente || $rifaDependiente->estado !== 'completada') {
                return 'Debes completar la rifa anterior primero';
            }
        }

        return 'Rifa disponible';
    }
}

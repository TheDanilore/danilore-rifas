<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rifa;
use App\Models\Premio;
use App\Models\Nivel;
use App\Models\ProgresoPremio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RifaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios.niveles']);

        // Filtros
        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('destacadas')) {
            $query->where('es_destacada', true);
        }

        // Ordenamiento
        if ($request->tipo === 'futura') {
            $query->orderBy('orden');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $rifas = $query->paginate($request->get('per_page', 15));

        // Enriquecer datos de cada rifa
        $rifas->getCollection()->transform(function ($rifa) {
            return $this->enrichRifaData($rifa);
        });

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $codigo): JsonResponse
    {
        $rifa = Rifa::with([
            'categoria',
            'premios' => function($query) {
                $query->orderBy('orden');
            },
            'premios.niveles' => function($query) {
                $query->orderBy('orden');
            },
            'premios.progreso'
        ])->where('codigo_unico', $codigo)->firstOrFail();

        // Calcular boletos disponibles
        $boletosOcupados = $rifa->boletos()
            ->where(function($query) {
                $query->where('estado', 'pagado')
                      ->orWhere(function($q) {
                          $q->where('estado', 'reservado')
                            ->where('fecha_expiracion_reserva', '>', now());
                      });
            })
            ->pluck('numero')
            ->toArray();

        // Generar números disponibles (1 al boletos_minimos)
        $numerosDisponibles = [];
        for ($i = 1; $i <= $rifa->boletos_minimos; $i++) {
            $numerosDisponibles[] = sprintf('%04d', $i);
        }

        $numerosLibres = array_diff($numerosDisponibles, $boletosOcupados);

        return response()->json([
            'success' => true,
            'data' => [
                'rifa' => $rifa,
                'numeros_disponibles' => array_values($numerosLibres),
                'numeros_ocupados' => $boletosOcupados,
                'total_disponibles' => count($numerosLibres)
            ]
        ]);
    }

    /**
     * Get rifas actuales (en venta)
     */
    public function actuales(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios.niveles'])
            ->actuales()
            ->enVenta();

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $rifas = $query->orderBy('es_destacada', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->paginate($request->get('per_page', 10));

        // Enriquecer datos de cada rifa
        $rifas->getCollection()->transform(function ($rifa) {
            return $this->enrichRifaData($rifa);
        });

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Get rifas futuras
     */
    public function futuras(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios.niveles', 'rifaRequerida'])
            ->futuras();

        $rifas = $query->paginate($request->get('per_page', 10));

        // Enriquecer datos de cada rifa
        $rifas->getCollection()->transform(function ($rifa) {
            return $this->enrichRifaData($rifa);
        });

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Get rifas destacadas
     */
    public function destacadas(): JsonResponse
    {
        $rifas = Rifa::with(['categoria', 'premios.niveles'])
            ->destacadas()
            ->enVenta()
            ->limit(6)
            ->get();

        // Enriquecer datos de cada rifa
        $rifas = $rifas->map(function ($rifa) {
            return $this->enrichRifaData($rifa);
        });

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Get progreso de premios de una rifa
     */
    public function progreso(string $codigo): JsonResponse
    {
        $rifa = Rifa::where('codigo_unico', $codigo)->firstOrFail();
        
        $premios = Premio::with(['niveles', 'progreso'])
            ->where('rifa_id', $rifa->id)
            ->orderBy('orden')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'rifa' => $rifa,
                'premios' => $premios
            ]
        ]);
    }

    /**
     * Display a listing of rifas for admin panel
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $query = Rifa::with(['categoria', 'premios']);

        // Filtros específicos para admin
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('codigo_unico', 'like', "%{$search}%")
                  ->orWhere('descripcion', 'like', "%{$search}%");
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'titulo', 'precio_boleto', 'boletos_vendidos', 'fecha_fin'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $rifas = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $rifas
        ]);
    }

    /**
     * Store a newly created rifa
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'codigo_unico' => 'required|string|max:20|unique:rifas',
            'descripcion' => 'nullable|string',
            'precio_boleto' => 'required|numeric|min:0.01',
            'boletos_minimos' => 'required|integer|min:1',
            'boletos_maximos' => 'nullable|integer|min:1',
            'max_boletos_por_persona' => 'nullable|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'fecha_sorteo' => 'nullable|date',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => 'required|in:actual,futura',
            'estado' => 'required|in:borrador,activa,pausada,finalizada,cancelada',
            'orden' => 'nullable|integer|min:1',
            'es_destacada' => 'nullable|boolean',
            'imagen_principal' => 'nullable|url',
            'terminos_condiciones' => 'nullable|string',
            'notas_admin' => 'nullable|string',
            'premios' => 'required|array|min:1',
            'premios.*.codigo' => 'required|string|max:10',
            'premios.*.titulo' => 'required|string|max:255',
            'premios.*.descripcion' => 'nullable|string',
            'premios.*.imagen_principal' => 'nullable|url',
            'premios.*.estado' => 'nullable|in:bloqueado,activo,completado',
            'premios.*.orden' => 'required|integer|min:1',
            'premios.*.notas_admin' => 'nullable|string',
            'premios.*.niveles' => 'required|array|min:1',
            'premios.*.niveles.*.codigo' => 'required|string|max:10',
            'premios.*.niveles.*.titulo' => 'required|string|max:255',
            'premios.*.niveles.*.descripcion' => 'nullable|string',
            'premios.*.niveles.*.tickets_necesarios' => 'required|integer|min:1',
            'premios.*.niveles.*.valor_aproximado' => 'nullable|numeric|min:0',
            'premios.*.niveles.*.imagen' => 'nullable|url',
            'premios.*.niveles.*.es_actual' => 'nullable|boolean',
            'premios.*.niveles.*.especificaciones' => 'nullable|string',
            'premios.*.niveles.*.orden' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Crear la rifa
            $rifa = Rifa::create([
                'titulo' => $request->titulo,
                'codigo_unico' => $request->codigo_unico,
                'descripcion' => $request->descripcion,
                'precio_boleto' => $request->precio_boleto,
                'boletos_minimos' => $request->boletos_minimos,
                'boletos_maximos' => $request->boletos_maximos,
                'max_boletos_por_persona' => $request->max_boletos_por_persona ?? 10,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'fecha_sorteo' => $request->fecha_sorteo,
                'categoria_id' => $request->categoria_id,
                'tipo' => $request->tipo ?? 'actual',
                'estado' => $request->estado,
                'orden' => $request->orden ?? 1,
                'es_destacada' => $request->es_destacada ?? false,
                'imagen_principal' => $request->imagen_principal,
                'terminos_condiciones' => $request->terminos_condiciones,
                'notas_admin' => $request->notas_admin
            ]);

            // Crear premios y niveles
            foreach ($request->premios as $premioData) {
                $premio = Premio::create([
                    'rifa_id' => $rifa->id,
                    'codigo' => $premioData['codigo'],
                    'titulo' => $premioData['titulo'],
                    'descripcion' => $premioData['descripcion'] ?? null,
                    'imagen_principal' => $premioData['imagen_principal'] ?? null,
                    'estado' => $premioData['estado'] ?? 'bloqueado',
                    'orden' => $premioData['orden'],
                    'notas_admin' => $premioData['notas_admin'] ?? null
                ]);

                // Crear niveles para este premio
                foreach ($premioData['niveles'] as $nivelData) {
                    Nivel::create([
                        'premio_id' => $premio->id,
                        'codigo' => $nivelData['codigo'],
                        'titulo' => $nivelData['titulo'],
                        'descripcion' => $nivelData['descripcion'] ?? null,
                        'tickets_necesarios' => $nivelData['tickets_necesarios'],
                        'valor_aproximado' => $nivelData['valor_aproximado'] ?? 0,
                        'imagen' => $nivelData['imagen'] ?? null,
                        'es_actual' => $nivelData['es_actual'] ?? false,
                        'especificaciones' => $nivelData['especificaciones'] ?? null,
                        'orden' => $nivelData['orden']
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $rifa->load(['categoria', 'premios.niveles']),
                'message' => 'Rifa creada exitosamente con premios y niveles'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la rifa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified rifa
     */
    public function update(Request $request, $id): JsonResponse
    {
        $rifa = Rifa::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'codigo_unico' => 'required|string|max:20|unique:rifas,codigo_unico,' . $id,
            'descripcion' => 'nullable|string',
            'precio_boleto' => 'required|numeric|min:0.01',
            'boletos_minimos' => 'required|integer|min:1',
            'max_boletos_por_persona' => 'nullable|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'fecha_sorteo' => 'nullable|date',
            'categoria_id' => 'nullable|exists:categorias,id',
            'tipo' => 'required|in:actual,futura',
            'estado' => 'required|in:borrador,activa,pausada,finalizada,cancelada',
            'orden' => 'nullable|integer|min:1',
            'es_destacada' => 'nullable|boolean',
            'imagen_principal' => 'nullable|url',
            'terminos_condiciones' => 'nullable|string',
            'notas_admin' => 'nullable|string',
            'premios' => 'sometimes|array|min:1',
            'premios.*.codigo' => 'required_with:premios|string|max:10',
            'premios.*.titulo' => 'required_with:premios|string|max:255',
            'premios.*.descripcion' => 'nullable|string',
            'premios.*.imagen_principal' => 'nullable|url',
            'premios.*.estado' => 'nullable|in:bloqueado,activo,completado',
            'premios.*.orden' => 'required_with:premios|integer|min:1',
            'premios.*.notas_admin' => 'nullable|string',
            'premios.*.niveles' => 'required_with:premios|array|min:1',
            'premios.*.niveles.*.codigo' => 'required_with:premios.*.niveles|string|max:10',
            'premios.*.niveles.*.titulo' => 'required_with:premios.*.niveles|string|max:255',
            'premios.*.niveles.*.descripcion' => 'nullable|string',
            'premios.*.niveles.*.tickets_necesarios' => 'required_with:premios.*.niveles|integer|min:1',
            'premios.*.niveles.*.valor_aproximado' => 'nullable|numeric|min:0',
            'premios.*.niveles.*.imagen' => 'nullable|url',
            'premios.*.niveles.*.es_actual' => 'nullable|boolean',
            'premios.*.niveles.*.especificaciones' => 'nullable|string',
            'premios.*.niveles.*.orden' => 'required_with:premios.*.niveles|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            // Actualizar datos básicos de la rifa
            $rifa->update([
                'titulo' => $request->titulo,
                'codigo_unico' => $request->codigo_unico,
                'descripcion' => $request->descripcion,
                'precio_boleto' => $request->precio_boleto,
                'boletos_minimos' => $request->boletos_minimos,
                'max_boletos_por_persona' => $request->max_boletos_por_persona ?? 10,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'fecha_sorteo' => $request->fecha_sorteo,
                'categoria_id' => $request->categoria_id,
                'tipo' => $request->tipo ?? 'actual',
                'estado' => $request->estado,
                'orden' => $request->orden ?? 1,
                'es_destacada' => $request->es_destacada ?? false,
                'imagen_principal' => $request->imagen_principal,
                'terminos_condiciones' => $request->terminos_condiciones,
                'notas_admin' => $request->notas_admin
            ]);

            // Si se envían premios, actualizar la estructura completa
            if ($request->has('premios')) {
                // Eliminar premios y niveles existentes
                foreach ($rifa->premios as $premio) {
                    $premio->niveles()->delete();
                    $premio->delete();
                }

                // Crear nuevos premios y niveles
                foreach ($request->premios as $premioData) {
                    $premio = Premio::create([
                        'rifa_id' => $rifa->id,
                        'codigo' => $premioData['codigo'],
                        'titulo' => $premioData['titulo'],
                        'descripcion' => $premioData['descripcion'] ?? null,
                        'imagen_principal' => $premioData['imagen_principal'] ?? null,
                        'estado' => $premioData['estado'] ?? 'bloqueado',
                        'orden' => $premioData['orden'],
                        'notas_admin' => $premioData['notas_admin'] ?? null
                    ]);

                    // Crear niveles para este premio
                    foreach ($premioData['niveles'] as $nivelData) {
                        Nivel::create([
                            'premio_id' => $premio->id,
                            'codigo' => $nivelData['codigo'],
                            'titulo' => $nivelData['titulo'],
                            'descripcion' => $nivelData['descripcion'] ?? null,
                            'tickets_necesarios' => $nivelData['tickets_necesarios'],
                            'valor_aproximado' => $nivelData['valor_aproximado'] ?? 0,
                            'imagen' => $nivelData['imagen'] ?? null,
                            'es_actual' => $nivelData['es_actual'] ?? false,
                            'especificaciones' => $nivelData['especificaciones'] ?? null,
                            'orden' => $nivelData['orden']
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $rifa->load(['categoria', 'premios.niveles']),
                'message' => 'Rifa actualizada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la rifa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified rifa
     */
    public function destroy($id): JsonResponse
    {
        $rifa = Rifa::findOrFail($id);
        
        // Verificar que no tenga ventas activas
        if ($rifa->boletos_vendidos > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una rifa con tickets vendidos'
            ], 422);
        }

        $rifa->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rifa eliminada exitosamente'
        ]);
    }

    /**
     * Change estado of a rifa
     */
    public function changeEstado(Request $request, $id): JsonResponse
    {
        $rifa = Rifa::findOrFail($id);

        $request->validate([
            'estado' => 'required|in:borrador,activa,pausada,finalizada,cancelada'
        ]);

        $rifa->update([
            'estado' => $request->estado
        ]);

        return response()->json([
            'success' => true,
            'data' => $rifa->load(['categoria', 'premios']),
            'message' => 'Estado de la rifa actualizado exitosamente'
        ]);
    }

    /**
     * Enriquecer datos de una rifa con información calculada de progreso
     */
    private function enrichRifaData($rifa)
    {
        // Mapear nombres de campos para compatibilidad con frontend
        $rifa->ticketsVendidos = $rifa->boletos_vendidos;
        $rifa->ticketsMinimos = $rifa->boletos_minimos;
        $rifa->fechaSorteo = $rifa->fecha_sorteo;
        $rifa->nombre = $rifa->titulo;
        $rifa->imagen = $rifa->imagen_principal;

        // Calcular datos de progreso para cada premio
        $premiosConProgreso = [];
        $todosLosNiveles = [];
        $nivelesCompletadosGeneral = 0;
        $totalNivelesGeneral = 0;

        foreach ($rifa->premios as $premio) {
            // Determinar estado del premio
            $premioAnteriorCompletado = true;
            if ($premio->premio_requerido_id) {
                $premioAnterior = $rifa->premios->where('id', $premio->premio_requerido_id)->first();
                $premioAnteriorCompletado = $premioAnterior ? $premioAnterior->estado === 'completado' : false;
            }

            $premio->desbloqueado = $premioAnteriorCompletado;
            $premio->completado = $premio->estado === 'completado';
            $premio->esta_activo = $premio->desbloqueado && !$premio->completado;

            // Determinar texto de estado
            if ($premio->completado) {
                $premio->estado_texto = 'Completado';
            } elseif ($premio->esta_activo) {
                $premio->estado_texto = 'En Progreso';
            } else {
                $premio->estado_texto = 'Bloqueado';
                $premio->premio_requerido = $premioAnterior ? $premioAnterior->titulo : 'Premio anterior';
            }

            // Procesar niveles del premio
            $nivelesConProgreso = [];
            foreach ($premio->niveles as $nivel) {
                $nivel->desbloqueado = $rifa->boletos_vendidos >= $nivel->tickets_necesarios;
                $nivel->es_actual = !$nivel->desbloqueado && $premio->esta_activo && 
                    ($premio->niveles->where('tickets_necesarios', '<', $nivel->tickets_necesarios)
                        ->where('desbloqueado', true)->count() == $premio->niveles->where('tickets_necesarios', '<', $nivel->tickets_necesarios)->count());
                
                $nivel->nombre = $nivel->titulo;
                $nivelesConProgreso[] = $nivel;

                // Agregar a todos los niveles para progreso general
                $todosLosNiveles[] = [
                    'id' => $nivel->id,
                    'titulo' => $nivel->titulo,
                    'tickets_necesarios' => $nivel->tickets_necesarios,
                    'completado' => $nivel->desbloqueado,
                    'premio_titulo' => $premio->titulo
                ];

                $totalNivelesGeneral++;
                if ($nivel->desbloqueado) {
                    $nivelesCompletadosGeneral++;
                }
            }

            $premio->niveles = collect($nivelesConProgreso);
            $premiosConProgreso[] = $premio;
        }

        $rifa->premios = collect($premiosConProgreso);

        // Calcular progreso general
        $porcentajeGeneral = $totalNivelesGeneral > 0 ? round(($nivelesCompletadosGeneral / $totalNivelesGeneral) * 100, 2) : 0;

        $rifa->progreso_general = [
            'niveles_completados' => $nivelesCompletadosGeneral,
            'total_niveles' => $totalNivelesGeneral,
            'porcentaje' => $porcentajeGeneral,
            'todos_los_niveles' => $todosLosNiveles
        ];

        // Para compatibilidad, mantener también estas propiedades en el nivel raíz
        $rifa->niveles_completados_general = $nivelesCompletadosGeneral;
        $rifa->total_niveles_general = $totalNivelesGeneral;
        $rifa->porcentaje_general = $rifa->progreso_general['porcentaje'];

        return $rifa;
    }
}

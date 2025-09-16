<?php

namespace App\Http\Controllers;

use App\Models\Favorito;
use App\Models\Rifa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FavoritoController extends Controller
{
    /**
     * Mostrar favoritos del usuario autenticado
     */
    public function index()
    {
        try {
            $favoritos = Favorito::where('user_id', Auth::id())
                               ->with(['rifa' => function($query) {
                                   $query->with(['categoria', 'premios', 'media'])
                                         ->withCount(['ventas', 'boletos'])
                                         ->addSelect(['estado_sorteo', 'fecha_sorteo']);
                               }])
                               ->orderBy('created_at', 'desc')
                               ->get();

            // Calcular información adicional para cada rifa favorita
            $favoritos->each(function($favorito) {
                if ($favorito->rifa) {
                    $rifa = $favorito->rifa;
                    $rifa->progreso = $rifa->ventas_count > 0 ? 
                        round(($rifa->ventas_count / $rifa->cantidad_boletos) * 100, 2) : 0;
                    $rifa->boletos_disponibles = $rifa->cantidad_boletos - $rifa->ventas_count;
                    $rifa->es_favorito = true;
                }
            });

            return response()->json([
                'success' => true,
                'data' => $favoritos,
                'total' => $favoritos->count(),
                'message' => 'Favoritos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener favoritos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Alternar favorito (agregar/quitar)
     */
    public function toggle(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rifa_id' => 'required|exists:rifas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $rifaId = $request->rifa_id;

            // Buscar si ya existe el favorito
            $favorito = Favorito::where('user_id', $userId)
                              ->where('rifa_id', $rifaId)
                              ->first();

            if ($favorito) {
                // Si existe, lo eliminamos
                $favorito->delete();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'es_favorito' => false,
                        'accion' => 'eliminado'
                    ],
                    'message' => 'Rifa eliminada de favoritos'
                ]);
            } else {
                // Si no existe, lo creamos
                $nuevoFavorito = Favorito::create([
                    'user_id' => $userId,
                    'rifa_id' => $rifaId
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'favorito' => $nuevoFavorito,
                        'es_favorito' => true,
                        'accion' => 'agregado'
                    ],
                    'message' => 'Rifa agregada a favoritos'
                ], 201);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar favorito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si una rifa es favorita del usuario
     */
    public function verificar($rifaId)
    {
        try {
            $esFavorito = Favorito::where('user_id', Auth::id())
                                ->where('rifa_id', $rifaId)
                                ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'es_favorito' => $esFavorito,
                    'rifa_id' => $rifaId
                ],
                'message' => 'Estado de favorito verificado'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar favorito: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener rifas con información de favoritos para el usuario
     */
    public function rifasConFavoritos()
    {
        try {
            $userId = Auth::id();
            
            $rifas = Rifa::with(['categoria', 'premios', 'media'])
                        ->withCount(['ventas', 'boletos'])
                        ->where('estado', 'activa')
                        ->get();

            // Obtener todos los favoritos del usuario de una vez
            $favoritosIds = Favorito::where('user_id', $userId)
                                  ->pluck('rifa_id')
                                  ->toArray();

            // Agregar información de favoritos a cada rifa
            $rifas->each(function($rifa) use ($favoritosIds) {
                $rifa->es_favorito = in_array($rifa->id, $favoritosIds);
                $rifa->progreso = $rifa->ventas_count > 0 ? 
                    round(($rifa->ventas_count / $rifa->cantidad_boletos) * 100, 2) : 0;
                $rifa->boletos_disponibles = $rifa->cantidad_boletos - $rifa->ventas_count;
            });

            return response()->json([
                'success' => true,
                'data' => $rifas,
                'total_favoritos' => count($favoritosIds),
                'message' => 'Rifas con favoritos obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener rifas con favoritos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar múltiples rifas a favoritos
     */
    public function agregarMultiples(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rifas_ids' => 'required|array|min:1',
                'rifas_ids.*' => 'exists:rifas,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $rifasIds = $request->rifas_ids;
            
            // Obtener los favoritos existentes para evitar duplicados
            $favoritosExistentes = Favorito::where('user_id', $userId)
                                         ->whereIn('rifa_id', $rifasIds)
                                         ->pluck('rifa_id')
                                         ->toArray();

            // Filtrar las rifas que no están en favoritos
            $nuevasRifas = array_diff($rifasIds, $favoritosExistentes);
            
            if (empty($nuevasRifas)) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'agregados' => 0,
                        'ya_existentes' => count($favoritosExistentes)
                    ],
                    'message' => 'Todas las rifas ya están en favoritos'
                ]);
            }

            // Crear los nuevos favoritos
            $favoritosData = array_map(function($rifaId) use ($userId) {
                return [
                    'user_id' => $userId,
                    'rifa_id' => $rifaId,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $nuevasRifas);

            Favorito::insert($favoritosData);

            return response()->json([
                'success' => true,
                'data' => [
                    'agregados' => count($nuevasRifas),
                    'ya_existentes' => count($favoritosExistentes),
                    'total' => count($rifasIds)
                ],
                'message' => count($nuevasRifas) . ' rifas agregadas a favoritos'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar favoritos múltiples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar múltiples favoritos
     */
    public function eliminarMultiples(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'favoritos_ids' => 'required|array|min:1',
                'favoritos_ids.*' => 'exists:favoritos,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = Auth::id();
            $favoritosIds = $request->favoritos_ids;

            // Verificar que los favoritos pertenecen al usuario
            $favoritos = Favorito::where('user_id', $userId)
                               ->whereIn('id', $favoritosIds)
                               ->get();

            if ($favoritos->count() !== count($favoritosIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos favoritos no pertenecen al usuario o no existen'
                ], 403);
            }

            $eliminados = Favorito::where('user_id', $userId)
                                ->whereIn('id', $favoritosIds)
                                ->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'eliminados' => $eliminados
                ],
                'message' => $eliminados . ' favoritos eliminados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar favoritos múltiples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar todos los favoritos del usuario
     */
    public function limpiar()
    {
        try {
            $eliminados = Favorito::where('user_id', Auth::id())->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'eliminados' => $eliminados
                ],
                'message' => 'Todos los favoritos han sido eliminados'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar favoritos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de favoritos del usuario
     */
    public function estadisticas()
    {
        try {
            $userId = Auth::id();
            
            $estadisticas = [
                'total_favoritos' => Favorito::where('user_id', $userId)->count(),
                'favoritos_activos' => Favorito::where('user_id', $userId)
                    ->whereHas('rifa', function($query) {
                        $query->where('estado', 'activa');
                    })->count(),
                'favoritos_finalizados' => Favorito::where('user_id', $userId)
                    ->whereHas('rifa', function($query) {
                        $query->where('estado', 'finalizada');
                    })->count(),
                'categorias_favoritas' => Favorito::where('user_id', $userId)
                    ->join('rifas', 'favoritos.rifa_id', '=', 'rifas.id')
                    ->join('categorias', 'rifas.categoria_id', '=', 'categorias.id')
                    ->selectRaw('categorias.nombre, COUNT(*) as total')
                    ->groupBy('categorias.id', 'categorias.nombre')
                    ->orderBy('total', 'desc')
                    ->get(),
                'favoritos_recientes' => Favorito::where('user_id', $userId)
                    ->with(['rifa' => function($query) {
                        $query->select('id', 'titulo', 'precio_boleto', 'imagen_principal');
                    }])
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas de favoritos obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Obtener todos los favoritos (administrador)
     */
    public function admin()
    {
        try {
            $favoritos = Favorito::with(['user', 'rifa'])
                               ->orderBy('created_at', 'desc')
                               ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $favoritos,
                'message' => 'Favoritos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener favoritos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas generales de favoritos (administrador)
     */
    public function estadisticasAdmin()
    {
        try {
            $estadisticas = [
                'total_favoritos' => Favorito::count(),
                'usuarios_con_favoritos' => Favorito::distinct('user_id')->count(),
                'promedio_favoritos_por_usuario' => round(
                    Favorito::count() / max(1, Favorito::distinct('user_id')->count()), 2
                ),
                'rifas_mas_favoritas' => Favorito::join('rifas', 'favoritos.rifa_id', '=', 'rifas.id')
                    ->selectRaw('rifas.titulo, rifas.id, COUNT(*) as total_favoritos')
                    ->groupBy('rifas.id', 'rifas.titulo')
                    ->orderBy('total_favoritos', 'desc')
                    ->take(10)
                    ->get(),
                'usuarios_mas_activos' => Favorito::join('users', 'favoritos.user_id', '=', 'users.id')
                    ->selectRaw('users.name, users.email, COUNT(*) as total_favoritos')
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('total_favoritos', 'desc')
                    ->take(10)
                    ->get(),
                'favoritos_por_mes' => Favorito::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
                'message' => 'Estadísticas administrativas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas administrativas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar favorito específico (administrador)
     */
    public function destroy($id)
    {
        try {
            $favorito = Favorito::find($id);

            if (!$favorito) {
                return response()->json([
                    'success' => false,
                    'message' => 'Favorito no encontrado'
                ], 404);
            }

            $favorito->delete();

            return response()->json([
                'success' => true,
                'message' => 'Favorito eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar favorito: ' . $e->getMessage()
            ], 500);
        }
    }
}
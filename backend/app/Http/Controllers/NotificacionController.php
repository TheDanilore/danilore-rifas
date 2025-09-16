<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NotificacionController extends Controller
{
    /**
     * Obtener notificaciones del usuario autenticado
     */
    public function index(Request $request)
    {
        try {
            $query = Notificacion::where('user_id', Auth::id());

            // Filtros
            if ($request->tipo) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->leidas !== null) {
                $leidas = filter_var($request->leidas, FILTER_VALIDATE_BOOLEAN);
                $query->where('leida', $leidas);
            }

            $notificaciones = $query->orderBy('created_at', 'desc')
                                  ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'message' => 'Notificaciones obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de notificaciones
     */
    public function resumen()
    {
        try {
            $userId = Auth::id();
            
            $resumen = [
                'total' => Notificacion::where('user_id', $userId)->count(),
                'no_leidas' => Notificacion::where('user_id', $userId)->where('leida', false)->count(),
                'por_tipo' => Notificacion::where('user_id', $userId)
                    ->selectRaw('tipo, COUNT(*) as total, SUM(CASE WHEN leida = 0 THEN 1 ELSE 0 END) as no_leidas')
                    ->groupBy('tipo')
                    ->get(),
                'recientes' => Notificacion::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get(),
                'ultimas_24h' => Notificacion::where('user_id', $userId)
                    ->where('created_at', '>=', now()->subDay())
                    ->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $resumen,
                'message' => 'Resumen obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarLeida($id)
    {
        try {
            /** @var \App\Models\Notificacion|null $notificacion */
            $notificacion = Notificacion::where('id', $id)
                                      ->where('user_id', Auth::id())
                                      ->first();

            if (!$notificacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificación no encontrada'
                ], 404);
            }

            $notificacion->update([
                'leida' => true,
                'leida_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $notificacion->fresh(),
                'message' => 'Notificación marcada como leída'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar notificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas()
    {
        try {
            $actualizadas = Notificacion::where('user_id', Auth::id())
                                      ->where('leida', false)
                                      ->update([
                                          'leida' => true,
                                          'leida_at' => now()
                                      ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'actualizadas' => $actualizadas
                ],
                'message' => $actualizadas . ' notificaciones marcadas como leídas'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar notificaciones específicas como leídas
     */
    public function marcarMultiplesLeidas(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:notificaciones,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $actualizadas = Notificacion::where('user_id', Auth::id())
                                      ->whereIn('id', $request->ids)
                                      ->where('leida', false)
                                      ->update([
                                          'leida' => true,
                                          'leida_at' => now()
                                      ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'actualizadas' => $actualizadas
                ],
                'message' => $actualizadas . ' notificaciones marcadas como leídas'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar notificación
     */
    public function destroy($id)
    {
        try {
            $notificacion = Notificacion::where('id', $id)
                                      ->where('user_id', Auth::id())
                                      ->first();

            if (!$notificacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notificación no encontrada'
                ], 404);
            }

            $notificacion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notificación eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar notificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar múltiples notificaciones
     */
    public function eliminarMultiples(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array|min:1',
                'ids.*' => 'integer|exists:notificaciones,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $eliminadas = Notificacion::where('user_id', Auth::id())
                                    ->whereIn('id', $request->ids)
                                    ->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'eliminadas' => $eliminadas
                ],
                'message' => $eliminadas . ' notificaciones eliminadas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar notificaciones antiguas
     */
    public function limpiarAntiguas(Request $request)
    {
        try {
            $dias = $request->input('dias', 30); // Por defecto 30 días
            
            $eliminadas = Notificacion::where('user_id', Auth::id())
                                    ->where('created_at', '<', now()->subDays($dias))
                                    ->delete();

            return response()->json([
                'success' => true,
                'data' => [
                    'eliminadas' => $eliminadas,
                    'dias' => $dias
                ],
                'message' => "Eliminadas $eliminadas notificaciones anteriores a $dias días"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configurar preferencias de notificaciones
     */
    public function configurarPreferencias(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email_ventas' => 'boolean',
                'email_sorteos' => 'boolean',
                'email_comentarios' => 'boolean',
                'email_promociones' => 'boolean',
                'push_ventas' => 'boolean',
                'push_sorteos' => 'boolean',
                'push_comentarios' => 'boolean',
                'push_promociones' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            /** @var \App\Models\User $user */
            $user = Auth::user();
            $preferencias = $user->preferencias_notificaciones ?? [];
            
            // Actualizar preferencias
            foreach ($request->all() as $key => $value) {
                if (str_starts_with($key, 'email_') || str_starts_with($key, 'push_')) {
                    $preferencias[$key] = $value;
                }
            }

            $user->update(['preferencias_notificaciones' => $preferencias]);

            return response()->json([
                'success' => true,
                'data' => $preferencias,
                'message' => 'Preferencias actualizadas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar preferencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener preferencias de notificaciones
     */
    public function obtenerPreferencias()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $preferencias = $user->preferencias_notificaciones ?? [
                'email_ventas' => true,
                'email_sorteos' => true,
                'email_comentarios' => true,
                'email_promociones' => false,
                'push_ventas' => true,
                'push_sorteos' => true,
                'push_comentarios' => false,
                'push_promociones' => false
            ];

            return response()->json([
                'success' => true,
                'data' => $preferencias,
                'message' => 'Preferencias obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener preferencias: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Crear notificación masiva (administrador)
     */
    public function enviarMasiva(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'titulo' => 'required|string|max:255',
                'mensaje' => 'required|string|max:1000',
                'tipo' => 'required|in:promocion,anuncio,mantenimiento,sorteo,general',
                'usuarios' => 'required|in:todos,activos,premium,custom',
                'usuarios_ids' => 'required_if:usuarios,custom|array',
                'usuarios_ids.*' => 'exists:users,id',
                'programada' => 'boolean',
                'fecha_envio' => 'required_if:programada,true|date|after:now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obtener usuarios destinatarios
            $usuariosIds = $this->obtenerUsuariosDestino($request);

            if (empty($usuariosIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron usuarios destinatarios'
                ], 400);
            }

            $datosNotificacion = [
                'titulo' => $request->titulo,
                'mensaje' => $request->mensaje,
                'tipo' => $request->tipo,
                'data' => $request->data ?? [],
                'created_at' => now(),
                'updated_at' => now()
            ];

            if ($request->programada && $request->fecha_envio) {
                // Guardar para envío programado (aquí implementarías la lógica de jobs)
                $this->programarEnvio($datosNotificacion, $usuariosIds, $request->fecha_envio);
                $mensaje = 'Notificación programada para ' . $request->fecha_envio;
            } else {
                // Enviar inmediatamente
                $this->enviarInmediato($datosNotificacion, $usuariosIds);
                $mensaje = 'Notificación enviada a ' . count($usuariosIds) . ' usuarios';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'usuarios_destinatarios' => count($usuariosIds),
                    'tipo' => $request->tipo,
                    'programada' => $request->programada ?? false
                ],
                'message' => $mensaje
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar notificación masiva: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de notificaciones (administrador)
     */
    public function estadisticasAdmin()
    {
        try {
            $estadisticas = [
                'total_notificaciones' => Notificacion::count(),
                'enviadas_hoy' => Notificacion::whereDate('created_at', today())->count(),
                'leidas_hoy' => Notificacion::whereDate('leida_at', today())->count(),
                'por_tipo' => Notificacion::selectRaw('tipo, COUNT(*) as total')
                    ->groupBy('tipo')
                    ->get(),
                'tasa_lectura' => [
                    'total' => Notificacion::count(),
                    'leidas' => Notificacion::where('leida', true)->count(),
                    'porcentaje' => Notificacion::count() > 0 ? 
                        round((Notificacion::where('leida', true)->count() / Notificacion::count()) * 100, 2) : 0
                ],
                'notificaciones_por_mes' => Notificacion::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get(),
                'usuarios_mas_notificados' => Notificacion::join('users', 'notificaciones.user_id', '=', 'users.id')
                    ->selectRaw('users.name, users.email, COUNT(*) as total_notificaciones')
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('total_notificaciones', 'desc')
                    ->take(10)
                    ->get(),
                'tiempo_promedio_lectura' => DB::select("
                    SELECT AVG(TIMESTAMPDIFF(MINUTE, created_at, leida_at)) as minutos_promedio 
                    FROM notificaciones 
                    WHERE leida = 1 AND leida_at IS NOT NULL
                ")[0]->minutos_promedio ?? 0
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
     * Obtener todas las notificaciones (administrador)
     */
    public function admin(Request $request)
    {
        try {
            $query = Notificacion::with('user:id,name,email');

            if ($request->tipo) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->leidas !== null) {
                $leidas = filter_var($request->leidas, FILTER_VALIDATE_BOOLEAN);
                $query->where('leida', $leidas);
            }

            if ($request->usuario_id) {
                $query->where('user_id', $request->usuario_id);
            }

            $notificaciones = $query->orderBy('created_at', 'desc')
                                  ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $notificaciones,
                'message' => 'Notificaciones obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS PRIVADOS
    // ===============================

    /**
     * Obtener IDs de usuarios según criterios
     */
    private function obtenerUsuariosDestino($request)
    {
        switch ($request->usuarios) {
            case 'todos':
                return User::pluck('id')->toArray();
                
            case 'activos':
                return User::where('email_verified_at', '!=', null)
                          ->where('created_at', '>=', now()->subDays(30))
                          ->pluck('id')->toArray();
                          
            case 'premium':
                return User::where('tipo_usuario', 'premium')
                          ->pluck('id')->toArray();
                          
            case 'custom':
                return $request->usuarios_ids;
                
            default:
                return [];
        }
    }

    /**
     * Enviar notificación inmediatamente
     */
    private function enviarInmediato($datosNotificacion, $usuariosIds)
    {
        $notificaciones = [];
        
        foreach ($usuariosIds as $userId) {
            $notificaciones[] = array_merge($datosNotificacion, ['user_id' => $userId]);
        }

        // Insertar en lotes para mejorar rendimiento
        $chunks = array_chunk($notificaciones, 1000);
        
        foreach ($chunks as $chunk) {
            Notificacion::insert($chunk);
        }
    }

    /**
     * Programar envío de notificación
     */
    private function programarEnvio($datosNotificacion, $usuariosIds, $fechaEnvio)
    {
        // Aquí implementarías la lógica para programar el envío
        // Por ejemplo, usar Laravel Jobs con delay
        // ProcessNotificationJob::dispatch($datosNotificacion, $usuariosIds)->delay($fechaEnvio);
        
        // Por ahora, simplemente guardamos la información para procesamiento posterior
        DB::table('notificaciones_programadas')->insert([
            'datos' => json_encode($datosNotificacion),
            'usuarios_ids' => json_encode($usuariosIds),
            'fecha_envio' => $fechaEnvio,
            'estado' => 'programada',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
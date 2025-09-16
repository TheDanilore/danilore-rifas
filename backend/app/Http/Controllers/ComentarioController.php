<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Rifa;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ComentarioController extends Controller
{
    /**
     * Mostrar comentarios de una rifa específica (público)
     */
    public function index($rifaId)
    {
        try {
            // Verificar que la rifa existe
            $rifa = Rifa::find($rifaId);
            if (!$rifa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rifa no encontrada'
                ], 404);
            }

            $comentarios = Comentario::where('rifa_id', $rifaId)
                                   ->where('estado', 'aprobado')
                                   ->whereNull('comentario_padre_id') // Solo comentarios principales
                                   ->with([
                                       'user:id,name,avatar',
                                       'respuestas' => function($query) {
                                           $query->where('estado', 'aprobado')
                                                 ->with('user:id,name,avatar')
                                                 ->orderBy('created_at', 'asc');
                                       }
                                   ])
                                   ->withCount(['respuestas' => function($query) {
                                       $query->where('estado', 'aprobado');
                                   }])
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $comentarios,
                'message' => 'Comentarios obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comentarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo comentario
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rifa_id' => 'required|exists:rifas,id',
                'contenido' => 'required|string|min:3|max:1000',
                'comentario_padre_id' => 'nullable|exists:comentarios,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar que la rifa permite comentarios
            $rifa = Rifa::find($request->rifa_id);
            if (!$rifa->permite_comentarios) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta rifa no permite comentarios'
                ], 403);
            }

            // Si es una respuesta, verificar que el comentario padre existe y es de la misma rifa
            if ($request->comentario_padre_id) {
                $comentarioPadre = Comentario::find($request->comentario_padre_id);
                if ($comentarioPadre->rifa_id !== $request->rifa_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El comentario padre no pertenece a esta rifa'
                    ], 400);
                }
            }

            // Verificar límite de comentarios por usuario por día
            $comentariosHoy = Comentario::where('user_id', Auth::id())
                                      ->whereDate('created_at', today())
                                      ->count();

            if ($comentariosHoy >= 10) { // Límite de 10 comentarios por día
                return response()->json([
                    'success' => false,
                    'message' => 'Has alcanzado el límite de comentarios por día'
                ], 429);
            }

            // Estado inicial del comentario (automático o pendiente según configuración)
            $estadoInicial = $this->determinarEstadoInicial();

            $comentario = Comentario::create([
                'user_id' => Auth::id(),
                'rifa_id' => $request->rifa_id,
                'comentario_padre_id' => $request->comentario_padre_id,
                'contenido' => $request->contenido,
                'estado' => $estadoInicial,
                'ip_address' => $request->ip()
            ]);

            // Cargar relaciones
            $comentario->load('user:id,name,avatar');

            // Crear notificación si es una respuesta
            if ($request->comentario_padre_id) {
                $this->crearNotificacionRespuesta($comentarioPadre, $comentario);
            }

            $mensaje = $estadoInicial === 'aprobado' ? 
                'Comentario publicado exitosamente' : 
                'Comentario enviado para moderación';

            return response()->json([
                'success' => true,
                'data' => $comentario,
                'message' => $mensaje
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Editar un comentario existente
     */
    public function update(Request $request, $id)
    {
        try {
            $comentario = Comentario::find($id);

            if (!$comentario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            // Verificar que el usuario es el propietario del comentario
            if ($comentario->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para editar este comentario'
                ], 403);
            }

            // Verificar que el comentario se puede editar (dentro de las 24 horas)
            if ($comentario->created_at->diffInHours(now()) > 24) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo puedes editar comentarios dentro de las primeras 24 horas'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'contenido' => 'required|string|min:3|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comentario->update([
                'contenido' => $request->contenido,
                'editado' => true,
                'editado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $comentario->fresh('user:id,name,avatar'),
                'message' => 'Comentario actualizado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un comentario
     */
    public function destroy($id)
    {
        try {
            $comentario = Comentario::find($id);

            if (!$comentario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            // Verificar que el usuario es el propietario del comentario
            if ($comentario->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar este comentario'
                ], 403);
            }

            // Si tiene respuestas, solo marcarlo como eliminado
            if ($comentario->respuestas()->exists()) {
                $comentario->update([
                    'contenido' => '[Comentario eliminado por el usuario]',
                    'estado' => 'eliminado'
                ]);
                
                $mensaje = 'Comentario marcado como eliminado';
            } else {
                // Si no tiene respuestas, eliminarlo completamente
                $comentario->delete();
                $mensaje = 'Comentario eliminado exitosamente';
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reportar un comentario
     */
    public function reportar(Request $request, $id)
    {
        try {
            $comentario = Comentario::find($id);

            if (!$comentario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'motivo' => 'required|in:spam,ofensivo,irrelevante,otro',
                'descripcion' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar si el usuario ya reportó este comentario
            $reporteExistente = DB::table('reportes_comentarios')
                              ->where('comentario_id', $id)
                              ->where('user_id', Auth::id())
                              ->exists();

            if ($reporteExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya has reportado este comentario'
                ], 400);
            }

            // Crear el reporte
            DB::table('reportes_comentarios')->insert([
                'comentario_id' => $id,
                'user_id' => Auth::id(),
                'motivo' => $request->motivo,
                'descripcion' => $request->descripcion,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Incrementar contador de reportes del comentario
            $comentario->increment('reportes');

            // Si tiene muchos reportes, marcarlo para revisión
            if ($comentario->reportes >= 3) {
                $comentario->update(['estado' => 'revision']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comentario reportado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reportar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Mostrar todos los comentarios para moderación
     */
    public function admin(Request $request)
    {
        try {
            $query = Comentario::with(['user', 'rifa:id,titulo'])
                             ->withCount('respuestas');

            // Filtros
            if ($request->estado) {
                $query->where('estado', $request->estado);
            }

            if ($request->rifa_id) {
                $query->where('rifa_id', $request->rifa_id);
            }

            if ($request->con_reportes) {
                $query->where('reportes', '>', 0);
            }

            $comentarios = $query->orderBy('created_at', 'desc')
                               ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $comentarios,
                'message' => 'Comentarios obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener comentarios: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aprobar comentario
     */
    public function aprobar($id)
    {
        try {
            $comentario = Comentario::find($id);

            if (!$comentario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            $comentario->update([
                'estado' => 'aprobado',
                'moderado_by' => Auth::id(),
                'moderado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $comentario->fresh(),
                'message' => 'Comentario aprobado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aprobar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar comentario
     */
    public function rechazar(Request $request, $id)
    {
        try {
            $comentario = Comentario::find($id);

            if (!$comentario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'motivo' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $comentario->update([
                'estado' => 'rechazado',
                'motivo_rechazo' => $request->motivo,
                'moderado_by' => Auth::id(),
                'moderado_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'data' => $comentario->fresh(),
                'message' => 'Comentario rechazado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar comentario (administrador)
     */
    public function eliminarAdmin($id)
    {
        try {
            $comentario = Comentario::find($id);

            if (!$comentario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comentario no encontrado'
                ], 404);
            }

            // Eliminar respuestas primero
            $comentario->respuestas()->delete();
            
            // Eliminar el comentario
            $comentario->delete();

            return response()->json([
                'success' => true,
                'message' => 'Comentario y sus respuestas eliminados exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar comentario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de comentarios
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_comentarios' => Comentario::count(),
                'pendientes_moderacion' => Comentario::where('estado', 'pendiente')->count(),
                'aprobados' => Comentario::where('estado', 'aprobado')->count(),
                'rechazados' => Comentario::where('estado', 'rechazado')->count(),
                'con_reportes' => Comentario::where('reportes', '>', 0)->count(),
                'comentarios_por_mes' => Comentario::selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get(),
                'usuarios_mas_activos' => Comentario::join('users', 'comentarios.user_id', '=', 'users.id')
                    ->selectRaw('users.name, users.email, COUNT(*) as total_comentarios')
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('total_comentarios', 'desc')
                    ->take(10)
                    ->get(),
                'rifas_mas_comentadas' => Comentario::join('rifas', 'comentarios.rifa_id', '=', 'rifas.id')
                    ->selectRaw('rifas.titulo, COUNT(*) as total_comentarios')
                    ->groupBy('rifas.id', 'rifas.titulo')
                    ->orderBy('total_comentarios', 'desc')
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

    // ===============================
    // MÉTODOS PRIVADOS
    // ===============================

    /**
     * Determinar estado inicial del comentario
     */
    private function determinarEstadoInicial()
    {
        // Aquí puedes implementar lógica más compleja
        // Por ejemplo, aprobar automáticamente usuarios verificados
        $user = Auth::user();
        
        if ($user->email_verified_at && $user->created_at->diffInDays(now()) > 30) {
            return 'aprobado';
        }
        
        return 'pendiente';
    }

    /**
     * Crear notificación de respuesta
     */
    private function crearNotificacionRespuesta($comentarioPadre, $comentarioRespuesta)
    {
        if ($comentarioPadre->user_id !== $comentarioRespuesta->user_id) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            Notificacion::create([
                'user_id' => $comentarioPadre->user_id,
                'titulo' => 'Nueva respuesta a tu comentario',
                'mensaje' => $user->name . ' respondió a tu comentario en "' . $comentarioPadre->rifa->titulo . '"',
                'tipo' => 'comentario_respuesta',
                'data' => [
                    'comentario_id' => $comentarioRespuesta->id,
                    'rifa_id' => $comentarioPadre->rifa_id,
                    'respuesta_de' => $user->name
                ]
            ]);
        }
    }
}
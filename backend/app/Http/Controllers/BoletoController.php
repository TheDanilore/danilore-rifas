<?php

namespace App\Http\Controllers;

use App\Models\Boleto;
use App\Models\Venta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BoletoController extends Controller
{
    /**
     * Obtener boletos del usuario autenticado
     */
    public function index(Request $request)
    {
        try {
            $query = Boleto::whereHas('venta', function($q) {
                $q->where('user_id', Auth::id());
            })->with([
                'venta.rifa.categoria', 
                'venta.rifa.premios',
                'venta.user',
                'sorteoGanador.premio'
            ]);

            // Filtros
            if ($request->estado) {
                if ($request->estado === 'ganador') {
                    $query->whereHas('sorteoGanador');
                } elseif ($request->estado === 'activo') {
                    $query->whereDoesntHave('sorteoGanador')
                          ->whereHas('venta.rifa', function($q) {
                              $q->where('estado', 'activa');
                          });
                } elseif ($request->estado === 'pendiente') {
                    $query->whereHas('venta.rifa', function($q) {
                        $q->where('estado', 'pendiente');
                    });
                }
            }

            if ($request->rifa_id) {
                $query->whereHas('venta', function($q) use ($request) {
                    $q->where('rifa_id', $request->rifa_id);
                });
            }

            if ($request->estado_rifa) {
                $query->whereHas('venta.rifa', function($q) use ($request) {
                    $q->where('estado', $request->estado_rifa);
                });
            }

            // Ordenamiento
            $sort = $request->sort ?? 'fecha_desc';
            switch ($sort) {
                case 'fecha_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'precio_desc':
                    $query->orderBy('precio', 'desc');
                    break;
                case 'precio_asc':
                    $query->orderBy('precio', 'asc');
                    break;
                case 'numero_desc':
                    $query->orderBy('numero', 'desc');
                    break;
                case 'numero_asc':
                    $query->orderBy('numero', 'asc');
                    break;
                default: // fecha_desc
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = $request->per_page ?? 12;
            $boletos = $query->paginate($perPage);

            // Formatear los boletos para el frontend
            $boletosFormatted = $boletos->getCollection()->map(function($boleto) {
                return [
                    'id' => $boleto->id,
                    'numero' => $boleto->numero,
                    'precio' => $boleto->precio,
                    'estado' => $boleto->sorteoGanador ? 'ganador' : 'activo',
                    'transferido' => $boleto->transferido,
                    'transferido_at' => $boleto->transferido_at,
                    'created_at' => $boleto->created_at,
                    'updated_at' => $boleto->updated_at,
                    'rifa' => [
                        'id' => $boleto->venta->rifa->id,
                        'nombre' => $boleto->venta->rifa->nombre,
                        'descripcion' => $boleto->venta->rifa->descripcion,
                        'imagen' => $boleto->venta->rifa->imagen_url,
                        'estado' => $boleto->venta->rifa->estado,
                        'fecha_sorteo' => $boleto->venta->rifa->fecha_sorteo,
                        'boletos_totales' => $boleto->venta->rifa->boletos_totales,
                        'boletos_vendidos' => $boleto->venta->rifa->boletos_vendidos,
                        'precio_boleto' => $boleto->venta->rifa->precio_boleto,
                        'categoria' => $boleto->venta->rifa->categoria ? [
                            'id' => $boleto->venta->rifa->categoria->id,
                            'nombre' => $boleto->venta->rifa->categoria->nombre
                        ] : null,
                        'premios' => $boleto->venta->rifa->premios->map(function($premio) {
                            return [
                                'id' => $premio->id,
                                'nombre' => $premio->nombre,
                                'descripcion' => $premio->descripcion,
                                'valor' => $premio->valor,
                                'posicion' => $premio->posicion
                            ];
                        })
                    ],
                    'premio_ganado' => $boleto->sorteoGanador ? [
                        'id' => $boleto->sorteoGanador->premio->id,
                        'nombre' => $boleto->sorteoGanador->premio->nombre,
                        'descripcion' => $boleto->sorteoGanador->premio->descripcion,
                        'valor' => $boleto->sorteoGanador->premio->valor
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'boletos' => $boletosFormatted,
                    'pagination' => [
                        'total' => $boletos->total(),
                        'current_page' => $boletos->currentPage(),
                        'per_page' => $boletos->perPage(),
                        'last_page' => $boletos->lastPage(),
                        'from' => $boletos->firstItem(),
                        'to' => $boletos->lastItem()
                    ]
                ],
                'message' => 'Boletos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener boletos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un boleto específico
     */
    public function show($id)
    {
        try {
            $boleto = Boleto::with([
                'venta.user',
                'venta.rifa.categoria',
                'venta.rifa.premios.nivel',
                'ganador.premio'
            ])->find($id);

            if (!$boleto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Boleto no encontrado'
                ], 404);
            }

            // Verificar permisos (solo el propietario o admin)
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($boleto->venta->user_id !== Auth::id() && !$user->hasRole(['admin', 'super_admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver este boleto'
                ], 403);
            }

            // Información adicional
            $boleto->es_ganador = $boleto->ganador !== null;
            $boleto->premio_ganado = $boleto->ganador ? $boleto->ganador->premio : null;
            $boleto->estado_rifa = $boleto->venta->rifa->estado;

            return response()->json([
                'success' => true,
                'data' => $boleto,
                'message' => 'Boleto obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener boleto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transferir boleto a otro usuario
     */
    public function transferir(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'destinatario_email' => 'required|email|exists:users,email',
                'motivo' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $boleto = Boleto::with('venta.rifa')->find($id);

            if (!$boleto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Boleto no encontrado'
                ], 404);
            }

            // Verificar que el usuario es propietario
            if ($boleto->venta->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para transferir este boleto'
                ], 403);
            }

            // Verificar que la rifa permite transferencias
            if (!$boleto->venta->rifa->permite_transferencias) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta rifa no permite transferencias de boletos'
                ], 400);
            }

            // Verificar que la rifa no esté finalizada
            if (in_array($boleto->venta->rifa->estado, ['finalizada', 'cancelada'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden transferir boletos de rifas finalizadas'
                ], 400);
            }

            $destinatario = User::where('email', $request->destinatario_email)->first();

            // Verificar que no se transfiera a sí mismo
            if ($destinatario->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes transferir un boleto a ti mismo'
                ], 400);
            }

            DB::transaction(function() use ($boleto, $destinatario, $request) {
                // Crear nueva venta para el destinatario
                $nuevaVenta = Venta::create([
                    'user_id' => $destinatario->id,
                    'rifa_id' => $boleto->venta->rifa_id,
                    'cantidad_boletos' => 1,
                    'precio_unitario' => 0, // Transferencia gratuita
                    'total' => 0,
                    'metodo_pago' => 'transferencia',
                    'estado' => 'completada',
                    'transferido_desde' => $boleto->venta_id,
                    'transferido_por' => Auth::id()
                ]);

                // Actualizar el boleto
                $boleto->update([
                    'venta_id' => $nuevaVenta->id,
                    'transferido' => true,
                    'transferido_at' => now(),
                    'transferido_desde' => $boleto->venta_id,
                    'transferido_por' => Auth::id()
                ]);

                // Registrar en historial de transferencias
                DB::table('transferencias_boletos')->insert([
                    'boleto_id' => $boleto->id,
                    'from_user_id' => Auth::id(),
                    'to_user_id' => $destinatario->id,
                    'venta_original_id' => $boleto->venta_id,
                    'venta_nueva_id' => $nuevaVenta->id,
                    'motivo' => $request->motivo,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

            return response()->json([
                'success' => true,
                'data' => $boleto->fresh(['venta.user']),
                'message' => "Boleto transferido exitosamente a {$destinatario->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al transferir boleto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de transferencias de un boleto
     */
    public function historialTransferencias($id)
    {
        try {
            $boleto = Boleto::find($id);

            if (!$boleto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Boleto no encontrado'
                ], 404);
            }

            $historial = DB::table('transferencias_boletos')
                          ->join('users as from_user', 'transferencias_boletos.from_user_id', '=', 'from_user.id')
                          ->join('users as to_user', 'transferencias_boletos.to_user_id', '=', 'to_user.id')
                          ->where('boleto_id', $id)
                          ->select([
                              'transferencias_boletos.*',
                              'from_user.name as from_user_name',
                              'from_user.email as from_user_email',
                              'to_user.name as to_user_name',
                              'to_user.email as to_user_email'
                          ])
                          ->orderBy('transferencias_boletos.created_at', 'desc')
                          ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'boleto' => $boleto,
                    'historial' => $historial
                ],
                'message' => 'Historial obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar estado de boletos para una rifa
     */
    public function verificarEstado($rifaId)
    {
        try {
            $boletos = Boleto::whereHas('venta', function($q) use ($rifaId) {
                $q->where('rifa_id', $rifaId)
                  ->where('user_id', Auth::id());
            })->with(['ganador.premio'])->get();

            $resultado = [
                'total_boletos' => $boletos->count(),
                'boletos_ganadores' => $boletos->where('ganador', '!=', null)->count(),
                'premios_ganados' => $boletos->where('ganador', '!=', null)->map(function($boleto) {
                    return [
                        'boleto_numero' => $boleto->numero,
                        'premio' => $boleto->ganador->premio->nombre ?? 'Sin premio',
                        'nivel' => $boleto->ganador->premio->nivel->nombre ?? 'Sin nivel'
                    ];
                })->values(),
                'numeros_boletos' => $boletos->pluck('numero')->sort()->values()
            ];

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Estado verificado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar estado: ' . $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // MÉTODOS ADMINISTRATIVOS
    // ===============================

    /**
     * Obtener todos los boletos (administrador)
     */
    public function admin(Request $request)
    {
        try {
            $query = Boleto::with(['venta.user', 'venta.rifa']);

            if ($request->rifa_id) {
                $query->whereHas('venta', function($q) use ($request) {
                    $q->where('rifa_id', $request->rifa_id);
                });
            }

            if ($request->user_id) {
                $query->whereHas('venta', function($q) use ($request) {
                    $q->where('user_id', $request->user_id);
                });
            }

            if ($request->transferido) {
                $transferido = filter_var($request->transferido, FILTER_VALIDATE_BOOLEAN);
                $query->where('transferido', $transferido);
            }

            $boletos = $query->orderBy('created_at', 'desc')
                           ->paginate(100);

            return response()->json([
                'success' => true,
                'data' => $boletos,
                'message' => 'Boletos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener boletos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forzar transferencia de boleto (administrador)
     */
    public function forzarTransferencia(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'destinatario_id' => 'required|exists:users,id',
                'motivo' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $boleto = Boleto::with('venta.rifa')->find($id);

            if (!$boleto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Boleto no encontrado'
                ], 404);
            }

            $destinatario = User::find($request->destinatario_id);

            DB::transaction(function() use ($boleto, $destinatario, $request) {
                $ventaOriginal = $boleto->venta;
                
                // Crear nueva venta para el destinatario
                $nuevaVenta = Venta::create([
                    'user_id' => $destinatario->id,
                    'rifa_id' => $ventaOriginal->rifa_id,
                    'cantidad_boletos' => 1,
                    'precio_unitario' => 0,
                    'total' => 0,
                    'metodo_pago' => 'transferencia_admin',
                    'estado' => 'completada',
                    'transferido_desde' => $ventaOriginal->id,
                    'transferido_por' => Auth::id()
                ]);

                // Actualizar el boleto
                $boleto->update([
                    'venta_id' => $nuevaVenta->id,
                    'transferido' => true,
                    'transferido_at' => now(),
                    'transferido_desde' => $ventaOriginal->id,
                    'transferido_por' => Auth::id()
                ]);

                // Registrar en historial
                DB::table('transferencias_boletos')->insert([
                    'boleto_id' => $boleto->id,
                    'from_user_id' => $ventaOriginal->user_id,
                    'to_user_id' => $destinatario->id,
                    'venta_original_id' => $ventaOriginal->id,
                    'venta_nueva_id' => $nuevaVenta->id,
                    'motivo' => $request->motivo,
                    'transferencia_admin' => true,
                    'admin_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });

            return response()->json([
                'success' => true,
                'data' => $boleto->fresh(['venta.user']),
                'message' => 'Transferencia administrativa completada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al forzar transferencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas de boletos
     */
    public function estadisticas()
    {
        try {
            $estadisticas = [
                'total_boletos' => Boleto::count(),
                'boletos_transferidos' => Boleto::where('transferido', true)->count(),
                'boletos_ganadores' => Boleto::whereHas('ganador')->count(),
                'transferencias_por_mes' => DB::table('transferencias_boletos')
                    ->selectRaw('YEAR(created_at) as año, MONTH(created_at) as mes, COUNT(*) as total')
                    ->groupBy('año', 'mes')
                    ->orderBy('año', 'desc')
                    ->orderBy('mes', 'desc')
                    ->take(12)
                    ->get(),
                'usuarios_que_mas_transfieren' => DB::table('transferencias_boletos')
                    ->join('users', 'transferencias_boletos.from_user_id', '=', 'users.id')
                    ->selectRaw('users.name, users.email, COUNT(*) as total_transferencias')
                    ->groupBy('users.id', 'users.name', 'users.email')
                    ->orderBy('total_transferencias', 'desc')
                    ->take(10)
                    ->get(),
                'rifas_con_mas_transferencias' => DB::table('transferencias_boletos')
                    ->join('boletos', 'transferencias_boletos.boleto_id', '=', 'boletos.id')
                    ->join('ventas', 'boletos.venta_id', '=', 'ventas.id')
                    ->join('rifas', 'ventas.rifa_id', '=', 'rifas.id')
                    ->selectRaw('rifas.titulo, COUNT(*) as total_transferencias')
                    ->groupBy('rifas.id', 'rifas.titulo')
                    ->orderBy('total_transferencias', 'desc')
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
}
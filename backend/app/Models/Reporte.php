<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reportes';

    protected $fillable = [
        'tipo_reporte',
        'periodo_inicio',
        'periodo_fin',
        'parametros',
        'datos_calculados',
        'estado',
        'progreso',
        'fecha_generacion',
        'generado_por',
        'archivo_url',
        'tamaño_archivo',
        'formato',
        'tiempo_procesamiento',
        'filas_procesadas',
        'errores',
        'hash_datos',
        'version_esquema',
        'programado',
        'frecuencia',
        'proxima_ejecucion',
        'notificar_completado',
        'destinatarios'
    ];

    protected $casts = [
        'periodo_inicio' => 'datetime',
        'periodo_fin' => 'datetime',
        'parametros' => 'json',
        'datos_calculados' => 'json',
        'progreso' => 'integer',
        'fecha_generacion' => 'datetime',
        'tamaño_archivo' => 'integer',
        'tiempo_procesamiento' => 'decimal:3',
        'filas_procesadas' => 'integer',
        'errores' => 'json',
        'programado' => 'boolean',
        'proxima_ejecucion' => 'datetime',
        'notificar_completado' => 'boolean',
        'destinatarios' => 'json'
    ];

    // Relaciones
    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }

    // Scopes
    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo_reporte', $tipo);
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeProcesando($query)
    {
        return $query->where('estado', 'procesando');
    }

    public function scopeProgramados($query)
    {
        return $query->where('programado', true);
    }

    public function scopeParaEjecutar($query)
    {
        return $query->where('programado', true)
                    ->where('proxima_ejecucion', '<=', now())
                    ->where('estado', 'pendiente');
    }

    public function scopeEnPeriodo($query, $inicio, $fin)
    {
        return $query->whereBetween('periodo_inicio', [$inicio, $fin])
                    ->orWhereBetween('periodo_fin', [$inicio, $fin]);
    }

    // Métodos auxiliares
    public function generar($usuarioGenerador = null)
    {
        if ($this->estado !== 'pendiente') {
            throw new \Exception('El reporte ya ha sido generado o está en proceso');
        }

        $inicio = microtime(true);

        try {
            $this->update([
                'estado' => 'procesando',
                'progreso' => 0,
                'generado_por' => $usuarioGenerador,
                'fecha_generacion' => now()
            ]);

            $datos = $this->calcularDatos();
            $archivo = $this->generarArchivo($datos);
            
            $tiempoProcesamiento = microtime(true) - $inicio;

            $this->update([
                'estado' => 'completado',
                'progreso' => 100,
                'datos_calculados' => $datos,
                'archivo_url' => $archivo['url'],
                'tamaño_archivo' => $archivo['tamaño'],
                'tiempo_procesamiento' => $tiempoProcesamiento,
                'filas_procesadas' => count($datos['detalle'] ?? []),
                'hash_datos' => hash('sha256', json_encode($datos))
            ]);

            // Programar próxima ejecución si es recurrente
            if ($this->programado && $this->frecuencia) {
                $this->programarProximaEjecucion();
            }

            return $this;

        } catch (\Exception $e) {
            $this->update([
                'estado' => 'error',
                'errores' => ['mensaje' => $e->getMessage(), 'fecha' => now()],
                'tiempo_procesamiento' => microtime(true) - $inicio
            ]);
            
            throw $e;
        }
    }

    private function calcularDatos()
    {
        switch ($this->tipo_reporte) {
            case 'ventas_periodo':
                return $this->calcularVentasPeriodo();
            
            case 'rifas_rendimiento':
                return $this->calcularRendimientoRifas();
            
            case 'usuarios_actividad':
                return $this->calcularActividadUsuarios();
            
            case 'financiero':
                return $this->calcularReporteFinanciero();
            
            case 'inventario_boletos':
                return $this->calcularInventarioBoletos();
            
            case 'comisiones':
                return $this->calcularComisiones();
            
            default:
                throw new \Exception('Tipo de reporte no reconocido');
        }
    }

    private function calcularVentasPeriodo()
    {
        $ventas = Venta::whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin])
                      ->where('estado', 'completada')
                      ->with(['rifa', 'user'])
                      ->get();

        $totalVentas = $ventas->sum('monto_total');
        $cantidadVentas = $ventas->count();
        $boletosVendidos = $ventas->sum('cantidad_boletos');

        return [
            'resumen' => [
                'total_ventas' => $totalVentas,
                'cantidad_ventas' => $cantidadVentas,
                'boletos_vendidos' => $boletosVendidos,
                'ticket_promedio' => $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0,
                'precio_promedio_boleto' => $boletosVendidos > 0 ? $totalVentas / $boletosVendidos : 0
            ],
            'por_dia' => $ventas->groupBy(function($venta) {
                return $venta->created_at->format('Y-m-d');
            })->map(function($ventasDia) {
                return [
                    'cantidad' => $ventasDia->count(),
                    'monto' => $ventasDia->sum('monto_total'),
                    'boletos' => $ventasDia->sum('cantidad_boletos')
                ];
            }),
            'por_rifa' => $ventas->groupBy('rifa_id')->map(function($ventasRifa) {
                $rifa = $ventasRifa->first()->rifa;
                return [
                    'rifa_titulo' => $rifa->titulo,
                    'cantidad' => $ventasRifa->count(),
                    'monto' => $ventasRifa->sum('monto_total'),
                    'boletos' => $ventasRifa->sum('cantidad_boletos')
                ];
            }),
            'detalle' => $ventas->map(function($venta) {
                return [
                    'id' => $venta->id,
                    'fecha' => $venta->created_at->format('Y-m-d H:i:s'),
                    'rifa' => $venta->rifa->titulo,
                    'usuario' => $venta->user->email,
                    'cantidad_boletos' => $venta->cantidad_boletos,
                    'monto_total' => $venta->monto_total,
                    'metodo_pago' => $venta->metodo_pago
                ];
            })
        ];
    }

    private function calcularRendimientoRifas()
    {
        $rifas = Rifa::whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin])
                    ->with(['ventas', 'boletos'])
                    ->get();

        return [
            'resumen' => [
                'total_rifas' => $rifas->count(),
                'rifas_activas' => $rifas->where('estado', 'activa')->count(),
                'rifas_completadas' => $rifas->where('estado', 'finalizada')->count(),
                'ingresos_totales' => $rifas->sum(function($rifa) {
                    return $rifa->ventas->sum('monto_total');
                })
            ],
            'detalle' => $rifas->map(function($rifa) {
                $ventas = $rifa->ventas->where('estado', 'completada');
                $boletosVendidos = $rifa->boletos->where('estado', 'vendido')->count();
                $boletosTotal = $rifa->total_boletos;
                
                return [
                    'id' => $rifa->id,
                    'titulo' => $rifa->titulo,
                    'estado' => $rifa->estado,
                    'precio_boleto' => $rifa->precio_boleto,
                    'total_boletos' => $boletosTotal,
                    'boletos_vendidos' => $boletosVendidos,
                    'porcentaje_vendido' => $boletosTotal > 0 ? ($boletosVendidos / $boletosTotal) * 100 : 0,
                    'ingresos' => $ventas->sum('monto_total'),
                    'cantidad_ventas' => $ventas->count(),
                    'fecha_creacion' => $rifa->created_at->format('Y-m-d'),
                    'fecha_sorteo' => $rifa->fecha_sorteo ? $rifa->fecha_sorteo->format('Y-m-d') : null
                ];
            })
        ];
    }

    private function calcularActividadUsuarios()
    {
        $usuarios = User::whereHas('ventas', function($query) {
                         $query->whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin]);
                     })
                     ->with(['ventas' => function($query) {
                         $query->whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin]);
                     }])
                     ->get();

        return [
            'resumen' => [
                'usuarios_activos' => $usuarios->count(),
                'nuevos_usuarios' => User::whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin])->count(),
                'usuarios_recurrentes' => $usuarios->filter(function($user) {
                    return $user->ventas->count() > 1;
                })->count()
            ],
            'detalle' => $usuarios->map(function($usuario) {
                $ventas = $usuario->ventas;
                return [
                    'id' => $usuario->id,
                    'email' => $usuario->email,
                    'nombre' => $usuario->nombre_completo,
                    'fecha_registro' => $usuario->created_at->format('Y-m-d'),
                    'cantidad_compras' => $ventas->count(),
                    'monto_gastado' => $ventas->sum('monto_total'),
                    'boletos_comprados' => $ventas->sum('cantidad_boletos'),
                    'primera_compra' => $ventas->min('created_at'),
                    'ultima_compra' => $ventas->max('created_at')
                ];
            })
        ];
    }

    private function calcularReporteFinanciero()
    {
        $ventas = Venta::whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin])
                      ->where('estado', 'completada')
                      ->get();

        $ingresosBrutos = $ventas->sum('monto_total');
        $descuentos = $ventas->sum('descuento_aplicado');
        $comisiones = $ventas->sum('comision_plataforma');

        return [
            'resumen' => [
                'ingresos_brutos' => $ingresosBrutos,
                'descuentos_aplicados' => $descuentos,
                'comisiones_plataforma' => $comisiones,
                'ingresos_netos' => $ingresosBrutos - $comisiones,
                'cantidad_transacciones' => $ventas->count()
            ],
            'por_metodo_pago' => $ventas->groupBy('metodo_pago')->map(function($ventasMetodo) {
                return [
                    'cantidad' => $ventasMetodo->count(),
                    'monto' => $ventasMetodo->sum('monto_total'),
                    'comision' => $ventasMetodo->sum('comision_plataforma')
                ];
            }),
            'gastos_operativos' => [
                'hosting' => $this->parametros['gastos_hosting'] ?? 0,
                'marketing' => $this->parametros['gastos_marketing'] ?? 0,
                'soporte' => $this->parametros['gastos_soporte'] ?? 0
            ]
        ];
    }

    private function calcularInventarioBoletos()
    {
        $rifas = Rifa::with(['boletos'])->get();

        return [
            'resumen' => [
                'total_boletos' => $rifas->sum('total_boletos'),
                'boletos_vendidos' => Boleto::where('estado', 'vendido')->count(),
                'boletos_disponibles' => Boleto::where('estado', 'disponible')->count(),
                'boletos_reservados' => Boleto::where('estado', 'reservado')->count()
            ],
            'por_rifa' => $rifas->map(function($rifa) {
                $boletos = $rifa->boletos;
                return [
                    'rifa_id' => $rifa->id,
                    'rifa_titulo' => $rifa->titulo,
                    'total' => $boletos->count(),
                    'vendidos' => $boletos->where('estado', 'vendido')->count(),
                    'disponibles' => $boletos->where('estado', 'disponible')->count(),
                    'reservados' => $boletos->where('estado', 'reservado')->count(),
                    'porcentaje_vendido' => $boletos->count() > 0 ? 
                        ($boletos->where('estado', 'vendido')->count() / $boletos->count()) * 100 : 0
                ];
            })
        ];
    }

    private function calcularComisiones()
    {
        $ventas = Venta::whereBetween('created_at', [$this->periodo_inicio, $this->periodo_fin])
                      ->where('estado', 'completada')
                      ->get();

        return [
            'resumen' => [
                'total_comisiones' => $ventas->sum('comision_plataforma'),
                'promedio_comision' => $ventas->avg('comision_plataforma'),
                'total_transacciones' => $ventas->count()
            ],
            'por_metodo_pago' => $ventas->groupBy('metodo_pago')->map(function($ventasMetodo) {
                return [
                    'cantidad' => $ventasMetodo->count(),
                    'comision_total' => $ventasMetodo->sum('comision_plataforma'),
                    'comision_promedio' => $ventasMetodo->avg('comision_plataforma')
                ];
            }),
            'detalle' => $ventas->map(function($venta) {
                return [
                    'venta_id' => $venta->id,
                    'fecha' => $venta->created_at->format('Y-m-d H:i:s'),
                    'monto_venta' => $venta->monto_total,
                    'comision' => $venta->comision_plataforma,
                    'porcentaje_comision' => $venta->monto_total > 0 ? 
                        ($venta->comision_plataforma / $venta->monto_total) * 100 : 0,
                    'metodo_pago' => $venta->metodo_pago
                ];
            })
        ];
    }

    private function generarArchivo($datos)
    {
        $nombreArchivo = $this->tipo_reporte . '_' . $this->periodo_inicio->format('Y-m-d') . 
                        '_al_' . $this->periodo_fin->format('Y-m-d') . 
                        '_' . time() . '.' . $this->formato;
        
        $rutaArchivo = storage_path('app/reportes/' . $nombreArchivo);
        
        if (!file_exists(dirname($rutaArchivo))) {
            mkdir(dirname($rutaArchivo), 0755, true);
        }

        switch ($this->formato) {
            case 'json':
                file_put_contents($rutaArchivo, json_encode($datos, JSON_PRETTY_PRINT));
                break;
            
            case 'csv':
                $this->generarCSV($rutaArchivo, $datos);
                break;
            
            case 'excel':
                $this->generarExcel($rutaArchivo, $datos);
                break;
            
            default:
                throw new \Exception('Formato de archivo no soportado');
        }

        return [
            'url' => 'storage/reportes/' . $nombreArchivo,
            'tamaño' => filesize($rutaArchivo)
        ];
    }

    private function generarCSV($rutaArchivo, $datos)
    {
        $archivo = fopen($rutaArchivo, 'w');
        
        if (isset($datos['detalle']) && is_array($datos['detalle'])) {
            // Escribir encabezados
            if (!empty($datos['detalle'])) {
                fputcsv($archivo, array_keys($datos['detalle'][0]));
                
                // Escribir datos
                foreach ($datos['detalle'] as $fila) {
                    fputcsv($archivo, $fila);
                }
            }
        }
        
        fclose($archivo);
    }

    private function generarExcel($rutaArchivo, $datos)
    {
        // Aquí se integraría con PhpSpreadsheet
        // Por ahora, generar como CSV
        $this->generarCSV($rutaArchivo, $datos);
    }

    private function programarProximaEjecucion()
    {
        $proximaFecha = match($this->frecuencia) {
            'diario' => now()->addDay(),
            'semanal' => now()->addWeek(),
            'mensual' => now()->addMonth(),
            'trimestral' => now()->addMonths(3),
            'anual' => now()->addYear(),
            default => null
        };

        if ($proximaFecha) {
            // Crear nuevo reporte programado
            self::create([
                'tipo_reporte' => $this->tipo_reporte,
                'periodo_inicio' => $this->periodo_fin->addSecond(),
                'periodo_fin' => $proximaFecha,
                'parametros' => $this->parametros,
                'formato' => $this->formato,
                'programado' => true,
                'frecuencia' => $this->frecuencia,
                'proxima_ejecucion' => $proximaFecha,
                'notificar_completado' => $this->notificar_completado,
                'destinatarios' => $this->destinatarios,
                'estado' => 'pendiente'
            ]);
        }
    }

    // Getters
    public function getEstaCompletadoAttribute()
    {
        return $this->estado === 'completado';
    }

    public function getTieneArchivoAttribute()
    {
        return !empty($this->archivo_url) && file_exists(storage_path('app/' . $this->archivo_url));
    }

    public function getTamañoArchivoFormateadoAttribute()
    {
        if (!$this->tamaño_archivo) return '0 B';
        
        $bytes = $this->tamaño_archivo;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Métodos estáticos
    public static function ejecutarReportesProgramados()
    {
        $reportes = self::paraEjecutar()->get();
        $resultados = [];

        foreach ($reportes as $reporte) {
            try {
                $reporte->generar();
                $resultados[] = ['reporte_id' => $reporte->id, 'estado' => 'completado'];
            } catch (\Exception $e) {
                $resultados[] = ['reporte_id' => $reporte->id, 'estado' => 'error', 'mensaje' => $e->getMessage()];
            }
        }

        return $resultados;
    }
}
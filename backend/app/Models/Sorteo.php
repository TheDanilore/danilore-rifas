<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Sorteo extends Model
{
    use HasFactory;

    protected $table = 'sorteos';

    protected $fillable = [
        'rifa_id',
        'codigo_sorteo',
        'fecha_programada',
        'fecha_realizada',
        'estado',
        'tipo',
        'descripcion',
        'metodo_sorteo',
        'configuracion_sorteo',
        'semilla_aleatoriedad',
        'publico',
        'url_transmision',
        'resultados',
        'total_participantes',
        'total_boletos_participantes',
        'hash_verificacion',
        'evidencia_sorteo',
        'archivos_evidencia',
        'verificado',
        'verificado_por',
        'fecha_verificacion',
        'realizado_por',
        'observaciones',
        'log_acciones',
        // Nuevos campos mejorados
        'transmision_en_vivo',
        'plataforma_transmision',
        'seed_aleatorio',
        'algoritmo_usado',
        'intentos_sorteo',
        'motivo_repeticion',
        'es_prueba',
        'duracion_segundos',
        'espectadores_en_vivo',
        'max_espectadores',
        'log_eventos',
        'estadisticas_transmision',
        'zona_horaria',
        'fecha_inicio_transmision',
        'fecha_fin_transmision',
        'autorizados_sorteo',
        'certificado',
        'entidad_certificadora',
        'numero_certificacion',
        'comentarios_publicos',
        'notas_internas'
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_realizada' => 'datetime',
        'fecha_verificacion' => 'datetime',
        'configuracion_sorteo' => 'json',
        'publico' => 'boolean',
        'resultados' => 'json',
        'total_participantes' => 'integer',
        'total_boletos_participantes' => 'integer',
        'archivos_evidencia' => 'json',
        'verificado' => 'boolean',
        'log_acciones' => 'json',
        // Nuevos casts
        'transmision_en_vivo' => 'boolean',
        'intentos_sorteo' => 'integer',
        'es_prueba' => 'boolean',
        'duracion_segundos' => 'integer',
        'espectadores_en_vivo' => 'integer',
        'max_espectadores' => 'integer',
        'log_eventos' => 'json',
        'estadisticas_transmision' => 'json',
        'fecha_inicio_transmision' => 'datetime',
        'fecha_fin_transmision' => 'datetime',
        'autorizados_sorteo' => 'json',
        'certificado' => 'boolean'
    ];

    // Relaciones
    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    public function realizadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'realizado_por');
    }

    // Scopes
    public function scopeProgramados($query)
    {
        return $query->where('estado', 'programado');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopeValidados($query)
    {
        return $query->where('estado', 'validado');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'programado')
                    ->where('fecha_programada', '<=', now());
    }

    public function scopeAutomaticos($query)
    {
        return $query->where('es_automatico', true);
    }

    public function scopeManuales($query)
    {
        return $query->where('es_automatico', false);
    }

    public function scopePorMetodo($query, $metodo)
    {
        return $query->where('metodo_sorteo', $metodo);
    }

    // Métodos auxiliares
    public function ejecutarSorteo($usuarioEjecutor = null)
    {
        if ($this->estado !== 'programado') {
            throw new \Exception('El sorteo ya ha sido ejecutado o cancelado');
        }

        $inicio = microtime(true);

        try {
            // Obtener boletos participantes
            $boletos = $this->obtenerBoletosParticipantes();
            
            if ($boletos->isEmpty()) {
                throw new \Exception('No hay boletos participantes para el sorteo');
            }

            // Generar seed aleatorio si no existe
            if (!$this->seed_aleatorio) {
                $this->seed_aleatorio = $this->generarSeedAleatorio();
                $this->save();
            }

            // Ejecutar el sorteo según el método
            $resultado = $this->ejecutarMetodoSorteo($boletos);

            // Guardar resultado
            $tiempoEjecucion = microtime(true) - $inicio;
            
            $this->update([
                'fecha_ejecutado' => now(),
                'estado' => 'ejecutado',
                'resultado' => $resultado,
                'boletos_participantes' => $boletos->pluck('id')->toArray(),
                'boleto_ganador_id' => $resultado['boleto_ganador_id'],
                'numero_ganador' => $resultado['numero_ganador'],
                'ejecutado_por' => $usuarioEjecutor,
                'hash_verificacion' => $this->generarHashVerificacion($resultado),
                'tiempo_ejecucion' => $tiempoEjecucion
            ]);

            // Actualizar boleto ganador
            if ($this->boletoGanador) {
                $this->boletoGanador->update([
                    'es_ganador' => true,
                    'premio_ganado' => $this->premio_id,
                    'fecha_premio' => now(),
                    'estado' => 'ganador'
                ]);
            }

            return $this;

        } catch (\Exception $e) {
            $this->update([
                'estado' => 'error',
                'observaciones' => 'Error en ejecución: ' . $e->getMessage(),
                'tiempo_ejecucion' => microtime(true) - $inicio
            ]);
            
            throw $e;
        }
    }

    private function obtenerBoletosParticipantes()
    {
        return Boleto::where('rifa_id', $this->rifa_id)
                    ->where('estado', 'vendido')
                    ->where('es_ganador', false)
                    ->get();
    }

    private function ejecutarMetodoSorteo($boletos)
    {
        switch ($this->metodo_sorteo) {
            case 'aleatorio_simple':
                return $this->sorteoAleatorioSimple($boletos);
            
            case 'loteria_nacional':
                return $this->sorteoLoteriaNacional($boletos);
            
            case 'hash_blockchain':
                return $this->sorteoHashBlockchain($boletos);
            
            default:
                throw new \Exception('Método de sorteo no reconocido');
        }
    }

    private function sorteoAleatorioSimple($boletos)
    {
        mt_srand($this->seed_aleatorio);
        $indiceGanador = mt_rand(0, $boletos->count() - 1);
        $boletoGanador = $boletos[$indiceGanador];

        return [
            'metodo' => 'aleatorio_simple',
            'boleto_ganador_id' => $boletoGanador->id,
            'numero_ganador' => $boletoGanador->numero,
            'indice_seleccionado' => $indiceGanador,
            'total_participantes' => $boletos->count(),
            'seed_usado' => $this->seed_aleatorio,
            'timestamp' => now()->timestamp
        ];
    }

    private function sorteoLoteriaNacional($boletos)
    {
        // Simulación usando último dígito de lotería nacional del día
        $fechaSorteo = $this->fecha_programada->format('Y-m-d');
        $numeroLoteria = $this->obtenerNumeroLoteriaNacional($fechaSorteo);
        
        if (!$numeroLoteria) {
            // Fallback a método aleatorio
            return $this->sorteoAleatorioSimple($boletos);
        }

        $ultimoDigito = $numeroLoteria % 10;
        $indiceGanador = $ultimoDigito % $boletos->count();
        $boletoGanador = $boletos[$indiceGanador];

        return [
            'metodo' => 'loteria_nacional',
            'boleto_ganador_id' => $boletoGanador->id,
            'numero_ganador' => $boletoGanador->numero,
            'numero_loteria_nacional' => $numeroLoteria,
            'ultimo_digito' => $ultimoDigito,
            'indice_seleccionado' => $indiceGanador,
            'total_participantes' => $boletos->count(),
            'fecha_sorteo' => $fechaSorteo
        ];
    }

    private function sorteoHashBlockchain($boletos)
    {
        // Usar hash de bloque de blockchain como fuente de aleatoriedad
        $hashBlock = $this->obtenerHashBlockchainReciente();
        $numeroDesdeHash = hexdec(substr($hashBlock, -8));
        
        $indiceGanador = $numeroDesdeHash % $boletos->count();
        $boletoGanador = $boletos[$indiceGanador];

        return [
            'metodo' => 'hash_blockchain',
            'boleto_ganador_id' => $boletoGanador->id,
            'numero_ganador' => $boletoGanador->numero,
            'hash_blockchain' => $hashBlock,
            'numero_extraido' => $numeroDesdeHash,
            'indice_seleccionado' => $indiceGanador,
            'total_participantes' => $boletos->count(),
            'timestamp' => now()->timestamp
        ];
    }

    private function generarSeedAleatorio()
    {
        return random_int(1000000, 9999999) + time();
    }

    private function generarHashVerificacion($resultado)
    {
        $data = json_encode($resultado) . $this->seed_aleatorio . $this->id;
        return hash('sha256', $data);
    }

    private function obtenerNumeroLoteriaNacional($fecha)
    {
        // Aquí se integraría con API de lotería nacional
        // Por ahora devuelve un número simulado basado en la fecha
        return crc32($fecha) % 100000;
    }

    private function obtenerHashBlockchainReciente()
    {
        // Aquí se integraría con API de blockchain (Bitcoin, Ethereum, etc.)
        // Por ahora devuelve un hash simulado
        return hash('sha256', time() . mt_rand());
    }

    public function validar($usuarioValidador = null, $observaciones = null)
    {
        if ($this->estado !== 'ejecutado') {
            throw new \Exception('El sorteo debe estar ejecutado para ser validado');
        }

        $this->update([
            'estado' => 'validado',
            'validado_por' => $usuarioValidador,
            'fecha_validacion' => now(),
            'observaciones' => $observaciones
        ]);

        return $this;
    }

    public function cancelar($motivo = null)
    {
        $this->update([
            'estado' => 'cancelado',
            'observaciones' => $motivo
        ]);

        return $this;
    }

    public function reprogramar($nuevaFecha, $motivo = null)
    {
        $this->update([
            'fecha_programada' => $nuevaFecha,
            'estado' => 'programado',
            'observaciones' => $motivo,
            'intento_numero' => $this->intento_numero + 1
        ]);

        return $this;
    }

    // Getters
    public function getEstaEjecutadoAttribute()
    {
        return in_array($this->estado, ['ejecutado', 'validado']);
    }

    public function getEstaValidadoAttribute()
    {
        return $this->estado === 'validado';
    }

    public function getPuedeEjecutarseAttribute()
    {
        return $this->estado === 'programado' && 
               $this->fecha_programada <= now();
    }

    public function getTiempoRestanteAttribute()
    {
        if ($this->esta_ejecutado) {
            return null;
        }

        return $this->fecha_programada->diffForHumans();
    }

    // Métodos estáticos
    public static function programarSorteoAutomatico($rifaId, $premioId, $fechaProgramada)
    {
        return self::create([
            'rifa_id' => $rifaId,
            'premio_id' => $premioId,
            'fecha_programada' => $fechaProgramada,
            'metodo_sorteo' => 'aleatorio_simple',
            'estado' => 'programado',
            'es_automatico' => true,
            'intento_numero' => 1
        ]);
    }

    public static function ejecutarSorteosPendientes()
    {
        $sorteosPendientes = self::pendientes()->automaticos()->get();
        $resultados = [];

        foreach ($sorteosPendientes as $sorteo) {
            try {
                $sorteo->ejecutarSorteo();
                $resultados[] = ['sorteo_id' => $sorteo->id, 'estado' => 'ejecutado'];
            } catch (\Exception $e) {
                $resultados[] = ['sorteo_id' => $sorteo->id, 'estado' => 'error', 'mensaje' => $e->getMessage()];
            }
        }

        return $resultados;
    }
}
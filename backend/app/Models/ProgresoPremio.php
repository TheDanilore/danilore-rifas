<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProgresoPremio extends Model
{
    use HasFactory;

    protected $table = 'progreso_premios';

    protected $fillable = [
        'rifa_id',
        'premio_id',
        'nivel_id',
        'tickets_actuales',
        'tickets_objetivo',
        'porcentaje_completado',
        'objetivo_alcanzado',
        'fecha_alcanzado',
        'ultimo_ticket',
        'tickets_hoy',
        'tickets_esta_semana',
        'velocidad_venta',
        'fecha_estimada_completacion',
        'historial_diario',
        'observaciones',
        'es_publico',
        'orden_visualizacion',
        'color_progreso',
        'mensaje_personalizado',
        'hitos',
        'fecha_ultimo_calculo',
        'estadisticas',
        'tendencia_ventas',
        'velocidad_ventas',
        'proyeccion_finalizacion',
        'tendencia_alcista',
        'porcentaje_notificacion',
        'notificacion_enviada'
    ];

    protected $casts = [
        'tickets_actuales' => 'integer',
        'tickets_objetivo' => 'integer',
        'tickets_restantes' => 'integer',
        'tickets_hoy' => 'integer',
        'tickets_esta_semana' => 'integer',
        'porcentaje_completado' => 'decimal:2',
        'velocidad_venta' => 'decimal:2',
        'objetivo_alcanzado' => 'boolean',
        'fecha_alcanzado' => 'datetime',
        'ultimo_ticket' => 'datetime',
        'fecha_estimada_completacion' => 'datetime',
        'historial_diario' => 'json',
        'es_publico' => 'boolean',
        'orden_visualizacion' => 'integer',
        'hitos' => 'json',
        'fecha_ultimo_calculo' => 'datetime',
        'estadisticas' => 'json',
        'tendencia_ventas' => 'decimal:2',
        'velocidad_ventas' => 'decimal:2',
        'proyeccion_finalizacion' => 'datetime',
        'tendencia_alcista' => 'boolean',
        'porcentaje_notificacion' => 'decimal:2',
        'notificacion_enviada' => 'boolean'
    ];

    // Relaciones
    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function premio(): BelongsTo
    {
        return $this->belongsTo(Premio::class);
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class);
    }

    // Scopes
    public function scopeAlcanzados($query)
    {
        return $query->where('objetivo_alcanzado', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('objetivo_alcanzado', false);
    }

    // Métodos auxiliares
    public function actualizarProgreso($ticketsVendidos)
    {
        $ticketsAnteriores = $this->tickets_actuales;
        $this->tickets_actuales = $ticketsVendidos;
        $this->porcentaje_completado = $this->calcularPorcentaje();
        $this->ultimo_ticket = now();

        // Actualizar estadísticas diarias
        $this->actualizarEstadisticasDiarias($ticketsVendidos - $ticketsAnteriores);
        
        // Calcular velocidad de venta
        $this->calcularVelocidadVenta();

        // Verificar si se alcanzó el objetivo
        if (!$this->objetivo_alcanzado && $this->tickets_actuales >= $this->tickets_objetivo) {
            $this->objetivo_alcanzado = true;
            $this->fecha_alcanzado = now();
        }

        $this->save();

        return $this;
    }

    public function calcularPorcentaje()
    {
        if ($this->tickets_objetivo == 0) return 0;
        return round(min(($this->tickets_actuales / $this->tickets_objetivo) * 100, 100), 2);
    }

    public function getTicketsRestantesAttribute()
    {
        return max(0, $this->tickets_objetivo - $this->tickets_actuales);
    }

    protected function actualizarEstadisticasDiarias($ticketsNuevos)
    {
        $hoy = now()->format('Y-m-d');
        $historial = $this->historial_diario ?? [];
        
        if (!isset($historial[$hoy])) {
            $historial[$hoy] = 0;
        }
        
        $historial[$hoy] += $ticketsNuevos;
        $this->historial_diario = $historial;

        // Actualizar tickets de hoy
        $this->tickets_hoy = $historial[$hoy];

        // Actualizar tickets de esta semana
        $inicioSemana = now()->startOfWeek();
        $ticketsSemana = 0;
        
        foreach ($historial as $fecha => $tickets) {
            if (Carbon::parse($fecha)->gte($inicioSemana)) {
                $ticketsSemana += $tickets;
            }
        }
        
        $this->tickets_esta_semana = $ticketsSemana;
    }

    protected function calcularVelocidadVenta()
    {
        $historial = $this->historial_diario ?? [];
        
        if (count($historial) === 0) {
            $this->velocidad_venta = 0;
            return;
        }

        // Calcular promedio de los últimos 7 días
        $ultimos7Dias = array_slice($historial, -7, 7, true);
        $totalTickets = array_sum($ultimos7Dias);
        $diasConVentas = count(array_filter($ultimos7Dias));
        
        $this->velocidad_venta = $diasConVentas > 0 ? $totalTickets / $diasConVentas : 0;

        // Calcular fecha estimada de completación
        if ($this->velocidad_venta > 0 && $this->tickets_restantes > 0) {
            $diasEstimados = ceil($this->tickets_restantes / $this->velocidad_venta);
            $this->fecha_estimada_completacion = now()->addDays($diasEstimados);
        }
    }

    public function getEstadisticasVentaAttribute()
    {
        return [
            'tickets_actuales' => $this->tickets_actuales,
            'tickets_objetivo' => $this->tickets_objetivo,
            'tickets_restantes' => $this->tickets_restantes,
            'porcentaje_completado' => $this->porcentaje_completado,
            'tickets_hoy' => $this->tickets_hoy,
            'tickets_esta_semana' => $this->tickets_esta_semana,
            'velocidad_venta' => $this->velocidad_venta,
            'fecha_estimada_completacion' => $this->fecha_estimada_completacion,
            'objetivo_alcanzado' => $this->objetivo_alcanzado,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nivel extends Model
{
    use HasFactory;

    protected $table = 'niveles';

    protected $fillable = [
        'premio_id',
        'codigo',
        'titulo',
        'descripcion',
        'tickets_necesarios',
        'tickets_acumulados',
        'valor_aproximado',
        'media_gallery',
        'imagen',
        'orden',
        'estado',
        'desbloqueado',
        'es_nivel_final',
        'fecha_desbloqueo',
        'fecha_completado',
        'progreso_actual',
        'porcentaje_progreso',
        'especificaciones',
        'contenido_adicional',
        'mensaje_desbloqueo'
    ];

    protected $casts = [
        'tickets_necesarios' => 'integer',
        'tickets_acumulados' => 'integer',
        'progreso_actual' => 'integer',
        'valor_aproximado' => 'decimal:2',
        'orden' => 'integer',
        'porcentaje_progreso' => 'decimal:2',
        'desbloqueado' => 'boolean',
        'es_nivel_final' => 'boolean',
        'fecha_desbloqueo' => 'datetime',
        'fecha_completado' => 'datetime',
        'especificaciones' => 'json',
        'media_gallery' => 'json',
        'contenido_adicional' => 'json'
    ];

    // Relaciones
    public function premio(): BelongsTo
    {
        return $this->belongsTo(Premio::class);
    }

    public function progreso(): HasMany
    {
        return $this->hasMany(ProgresoPremio::class);
    }

    // Scopes
    public function scopeDesbloqueados($query)
    {
        return $query->where('desbloqueado', true);
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopeNivelesFinales($query)
    {
        return $query->where('es_nivel_final', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    // Métodos auxiliares
    public function actualizarProgreso($ticketsVendidos)
    {
        $ticketsParaEsteNivel = max(0, $ticketsVendidos - $this->tickets_acumulados);
        $progreso = min($ticketsParaEsteNivel, $this->tickets_necesarios);
        $porcentaje = $this->tickets_necesarios > 0 ? ($progreso / $this->tickets_necesarios) * 100 : 0;

        $this->update([
            'progreso_actual' => $progreso,
            'porcentaje_progreso' => round($porcentaje, 2)
        ]);

        // Verificar si el nivel está completado
        if ($progreso >= $this->tickets_necesarios && $this->estado !== 'completado') {
            $this->completar();
        }

        return $this;
    }

    public function completar()
    {
        $this->update([
            'estado' => 'completado',
            'fecha_completado' => now(),
            'porcentaje_progreso' => 100
        ]);

        // Desbloquear siguiente nivel
        $siguienteNivel = Nivel::where('premio_id', $this->premio_id)
            ->where('orden', $this->orden + 1)
            ->first();

        if ($siguienteNivel) {
            $siguienteNivel->update([
                'desbloqueado' => true,
                'estado' => 'activo',
                'fecha_desbloqueo' => now()
            ]);
        } else {
            // Es el último nivel, completar el premio
            $this->premio->actualizarProgreso();
        }

        return $this;
    }

    public function getTicketsRestantesAttribute()
    {
        return max(0, $this->tickets_necesarios - $this->progreso_actual);
    }

    public function getEstaCompletadoAttribute()
    {
        return $this->estado === 'completado';
    }

    public function getPuedeDesbloquearseAttribute()
    {
        // El primer nivel se puede desbloquear cuando el premio se desbloquea
        if ($this->orden === 1) {
            return $this->premio->desbloqueado;
        }

        // Los demás niveles se desbloquean cuando el anterior está completado
        $nivelAnterior = Nivel::where('premio_id', $this->premio_id)
            ->where('orden', $this->orden - 1)
            ->first();

        return $nivelAnterior && $nivelAnterior->estado === 'completado';
    }
}

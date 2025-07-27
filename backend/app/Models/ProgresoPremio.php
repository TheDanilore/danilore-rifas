<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgresoPremio extends Model
{
    use HasFactory;

    protected $table = 'progreso_premios';

    protected $fillable = [
        'premio_id',
        'nivel_id',
        'tickets_actuales',
        'tickets_objetivo',
        'porcentaje_completado',
        'objetivo_alcanzado',
        'fecha_alcanzado',
        'ultimo_ticket',
        'tickets_restantes'
    ];

    protected $casts = [
        'tickets_actuales' => 'integer',
        'tickets_objetivo' => 'integer',
        'porcentaje_completado' => 'decimal:2',
        'objetivo_alcanzado' => 'boolean',
        'fecha_alcanzado' => 'datetime',
        'ultimo_ticket' => 'datetime'
    ];

    // Relaciones
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
        $this->tickets_actuales = $ticketsVendidos;
        $this->porcentaje_completado = $this->calcularPorcentaje();
        $this->ultimo_ticket = now();

        if (!$this->objetivo_alcanzado && $this->tickets_actuales >= $this->tickets_objetivo) {
            $this->objetivo_alcanzado = true;
            $this->fecha_alcanzado = now();
        }

        $this->save();
    }

    public function calcularPorcentaje()
    {
        if ($this->tickets_objetivo == 0) return 0;
        return round(($this->tickets_actuales / $this->tickets_objetivo) * 100, 2);
    }

    public function getTicketsRestantesAttribute()
    {
        return max(0, $this->tickets_objetivo - $this->tickets_actuales);
    }
}

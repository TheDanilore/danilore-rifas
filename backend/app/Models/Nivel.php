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
        'valor_aproximado',
        'imagen',
        'media_gallery',
        'orden',
        'desbloqueado',
        'es_actual',
        'fecha_desbloqueo',
        'especificaciones'
    ];

    protected $casts = [
        'tickets_necesarios' => 'integer',
        'valor_aproximado' => 'decimal:2',
        'orden' => 'integer',
        'desbloqueado' => 'boolean',
        'es_actual' => 'boolean',
        'fecha_desbloqueo' => 'datetime',
        'especificaciones' => 'json',
        'media_gallery' => 'json'
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

    public function scopeActuales($query)
    {
        return $query->where('es_actual', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    // Métodos auxiliares
    public function getProgresoActualAttribute()
    {
        $progreso = $this->progreso()->where('nivel_id', $this->id)->first();
        return $progreso ? $progreso->tickets_actuales : 0;
    }

    public function getPorcentajeAttribute()
    {
        if ($this->tickets_necesarios == 0) return 0;
        return round(($this->progreso_actual / $this->tickets_necesarios) * 100, 2);
    }

    public function getTicketsRestantesAttribute()
    {
        return max(0, $this->tickets_necesarios - $this->progreso_actual);
    }
}

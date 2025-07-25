<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Premio extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'codigo',
        'titulo',
        'descripcion',
        'imagen_principal',
        'media_gallery',
        'orden',
        'premio_requerido_id',
        'estado',
        'desbloqueado',
        'fecha_desbloqueo',
        'fecha_completado',
        'notas_admin'
    ];

    protected $casts = [
        'media_gallery' => 'json',
        'orden' => 'integer',
        'desbloqueado' => 'boolean',
        'fecha_desbloqueo' => 'datetime',
        'fecha_completado' => 'datetime'
    ];

    // Relaciones
    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function niveles(): HasMany
    {
        return $this->hasMany(Nivel::class)->orderBy('orden');
    }

    public function premioRequerido(): BelongsTo
    {
        return $this->belongsTo(Premio::class, 'premio_requerido_id');
    }

    public function premiosDependientes(): HasMany
    {
        return $this->hasMany(Premio::class, 'premio_requerido_id');
    }

    public function progreso(): HasOne
    {
        return $this->hasOne(ProgresoPremio::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeDesbloqueados($query)
    {
        return $query->where('desbloqueado', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    // Métodos auxiliares
    public function getNivelActualAttribute()
    {
        return $this->niveles()->where('es_actual', true)->first();
    }

    public function getPorcentajeCompletadoAttribute()
    {
        $progreso = $this->progreso;
        return $progreso ? $progreso->porcentaje_completado : 0;
    }

    public function puedeDesbloquearse()
    {
        if ($this->premio_requerido_id) {
            $premioRequerido = $this->premioRequerido;
            return $premioRequerido && $premioRequerido->estado === 'completado';
        }
        return true;
    }
}

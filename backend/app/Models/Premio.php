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

    /**
     * Obtener todas las imágenes del premio (principal + galería)
     */
    public function getTodasLasImagenesAttribute()
    {
        $imagenes = [];
        
        if ($this->imagen_principal) {
            $imagenes[] = [
                'tipo' => 'principal',
                'url' => $this->imagen_principal,
                'alt' => $this->titulo
            ];
        }
        
        if ($this->media_gallery && is_array($this->media_gallery)) {
            foreach ($this->media_gallery as $index => $media) {
                if (is_string($media)) {
                    $imagenes[] = [
                        'tipo' => 'galeria',
                        'url' => $media,
                        'alt' => $this->titulo . ' - Imagen ' . ($index + 1)
                    ];
                } elseif (is_array($media) && isset($media['url'])) {
                    $imagenes[] = [
                        'tipo' => $media['tipo'] ?? 'galeria',
                        'url' => $media['url'],
                        'alt' => $media['alt'] ?? $this->titulo . ' - Imagen ' . ($index + 1)
                    ];
                }
            }
        }
        
        return $imagenes;
    }

    /**
     * Agregar imagen a la galería
     */
    public function agregarImagenGaleria($url, $tipo = 'imagen', $alt = null)
    {
        $gallery = $this->media_gallery ?? [];
        
        $gallery[] = [
            'url' => $url,
            'tipo' => $tipo, // imagen, video, etc.
            'alt' => $alt ?? $this->titulo,
            'orden' => count($gallery) + 1
        ];
        
        $this->media_gallery = $gallery;
        $this->save();
        
        return $this;
    }

    /**
     * Eliminar imagen de la galería por índice
     */
    public function eliminarImagenGaleria($indice)
    {
        $gallery = $this->media_gallery ?? [];
        
        if (isset($gallery[$indice])) {
            unset($gallery[$indice]);
            $this->media_gallery = array_values($gallery); // Reindexar
            $this->save();
        }
        
        return $this;
    }
}

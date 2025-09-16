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
        'valor_estimado',
        'imagen_principal',
        'media_gallery',
        'orden',
        'tickets_minimos_desbloqueo',
        'premio_requerido_id',
        'estado',
        'desbloqueado',
        'fecha_desbloqueo',
        'fecha_completado',
        'total_niveles',
        'niveles_completados',
        'porcentaje_completado',
        'es_premio_final',
        'condiciones_especiales',
        'notas_admin'
    ];

    protected $casts = [
        'media_gallery' => 'json',
        'orden' => 'integer',
        'tickets_minimos_desbloqueo' => 'integer',
        'total_niveles' => 'integer',
        'niveles_completados' => 'integer',
        'porcentaje_completado' => 'decimal:2',
        'valor_estimado' => 'decimal:2',
        'desbloqueado' => 'boolean',
        'es_premio_final' => 'boolean',
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

    public function scopeCompletados($query)
    {
        return $query->where('estado', 'completado');
    }

    public function scopePremiosFinales($query)
    {
        return $query->where('es_premio_final', true);
    }

    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }

    // Métodos auxiliares
    public function getNivelActualAttribute()
    {
        return $this->niveles()->where('estado', 'activo')->first() ??
               $this->niveles()->where('desbloqueado', true)->orderBy('orden', 'desc')->first();
    }

    public function puedeDesbloquearse()
    {
        // Verificar si se alcanzaron los tickets mínimos
        if ($this->rifa->boletos_vendidos < $this->tickets_minimos_desbloqueo) {
            return false;
        }

        // Verificar si existe un premio requerido y está completado
        if ($this->premio_requerido_id) {
            $premioRequerido = $this->premioRequerido;
            return $premioRequerido && $premioRequerido->estado === 'completado';
        }
        
        return true;
    }

    public function desbloquear()
    {
        if (!$this->puedeDesbloquearse()) {
            throw new \Exception('Este premio no puede ser desbloqueado aún');
        }

        $this->update([
            'desbloqueado' => true,
            'estado' => 'activo',
            'fecha_desbloqueo' => now()
        ]);

        // Desbloquear el primer nivel
        $primerNivel = $this->niveles()->orderBy('orden')->first();
        if ($primerNivel) {
            $primerNivel->update([
                'desbloqueado' => true,
                'estado' => 'activo',
                'fecha_desbloqueo' => now()
            ]);
        }

        return $this;
    }

    public function actualizarProgreso()
    {
        $totalNiveles = $this->niveles()->count();
        $nivelesCompletados = $this->niveles()->where('estado', 'completado')->count();
        
        $porcentaje = $totalNiveles > 0 ? ($nivelesCompletados / $totalNiveles) * 100 : 0;

        $this->update([
            'total_niveles' => $totalNiveles,
            'niveles_completados' => $nivelesCompletados,
            'porcentaje_completado' => round($porcentaje, 2)
        ]);

        // Verificar si el premio está completado
        if ($nivelesCompletados === $totalNiveles && $totalNiveles > 0) {
            $this->completar();
        }

        return $this;
    }

    public function completar()
    {
        $this->update([
            'estado' => 'completado',
            'fecha_completado' => now()
        ]);

        // Desbloquear siguiente premio si existe
        $siguientePremio = Premio::where('rifa_id', $this->rifa_id)
                                 ->where('premio_requerido_id', $this->id)
                                 ->first();

        if ($siguientePremio && $siguientePremio->puedeDesbloquearse()) {
            $siguientePremio->desbloquear();
        }

        return $this;
    }

    public function getValorTotalAttribute()
    {
        return $this->niveles()->sum('valor_aproximado') ?: $this->valor_estimado;
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rifa extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'descripcion_corta',
        'precio_boleto',
        'boletos_minimos',
        'boletos_maximos',
        'boletos_vendidos',
        'boletos_reservados',
        // Medios y presentación
        'imagen_principal',
        'imagenes_adicionales',
        'media_gallery',
        'video_presentacion',
        'colores_tema',
        // Fechas y tiempos
        'fecha_inicio',
        'fecha_fin',
        'fecha_sorteo',
        'tiempo_reserva_minutos',
        // Estados y configuración
        'estado',
        'tipo',
        'modalidad',
        'categoria_id',
        'codigo_unico',
        'slug',
        // Configuraciones especiales
        'es_destacada',
        'es_premium',
        'permite_multiples_boletos',
        'max_boletos_por_persona',
        'max_boletos_por_transaccion',
        'requiere_verificacion_identidad',
        'permite_transferencia_boletos',
        // Términos y condiciones
        'terminos_condiciones',
        'bases_legales',
        'informacion_sorteo',
        // Sistema progresivo
        'total_premios',
        'premios_desbloqueados',
        'orden',
        'rifa_requerida_id',
        'porcentaje_completado_general',
        // Estadísticas y métricas
        'vistas',
        'favoritos',
        'compartidas',
        'rating_promedio',
        'total_comentarios',
        // Configuración financiera
        'comision_plataforma',
        'total_recaudado',
        'total_comisiones',
        // Administración
        'creado_por',
        'notas_admin',
        'configuracion_avanzada',
        'visible_publico',
        'fecha_publicacion',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_sorteo' => 'datetime',
        'fecha_publicacion' => 'datetime',
        'precio_boleto' => 'decimal:2',
        'imagenes_adicionales' => 'json',
        'media_gallery' => 'json',
        'colores_tema' => 'json',
        'configuracion_avanzada' => 'json',
        'es_destacada' => 'boolean',
        'es_premium' => 'boolean',
        'permite_multiples_boletos' => 'boolean',
        'permite_transferencia_boletos' => 'boolean',
        'requiere_verificacion_identidad' => 'boolean',
        'visible_publico' => 'boolean',
        'boletos_vendidos' => 'integer',
        'boletos_reservados' => 'integer',
        'boletos_minimos' => 'integer',
        'boletos_maximos' => 'integer',
        'max_boletos_por_persona' => 'integer',
        'max_boletos_por_transaccion' => 'integer',
        'tiempo_reserva_minutos' => 'integer',
        'total_premios' => 'integer',
        'premios_desbloqueados' => 'integer',
        'orden' => 'integer',
        'vistas' => 'integer',
        'favoritos' => 'integer',
        'compartidas' => 'integer',
        'total_comentarios' => 'integer',
        'rating_promedio' => 'decimal:2',
        'porcentaje_completado_general' => 'decimal:2',
        'comision_plataforma' => 'decimal:2',
        'total_recaudado' => 'decimal:2',
        'total_comisiones' => 'decimal:2',
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function premios(): HasMany
    {
        return $this->hasMany(Premio::class)->orderBy('orden');
    }

    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function sorteos(): HasMany
    {
        return $this->hasMany(Sorteo::class);
    }

    public function rifaRequerida(): BelongsTo
    {
        return $this->belongsTo(Rifa::class, 'rifa_requerida_id');
    }

    public function rifasDependientes(): HasMany
    {
        return $this->hasMany(Rifa::class, 'rifa_requerida_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Scopes
    public function scopeActuales($query)
    {
        return $query->where('tipo', 'actual');
    }

    public function scopeFuturas($query)
    {
        return $query->where('tipo', 'futura')->orderBy('orden');
    }

    public function scopeEnVenta($query)
    {
        return $query->where('estado', 'activa')
                    ->where('visible_publico', true);
    }

    public function scopeDestacadas($query)
    {
        return $query->where('es_destacada', true);
    }

    public function scopePremium($query)
    {
        return $query->where('es_premium', true);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    public function scopePorModalidad($query, $modalidad)
    {
        return $query->where('modalidad', $modalidad);
    }

    public function scopeVisiblesPublico($query)
    {
        return $query->where('visible_publico', true);
    }

    public function scopePublicadas($query)
    {
        return $query->whereNotNull('fecha_publicacion')
                    ->where('fecha_publicacion', '<=', now());
    }

    // Métodos auxiliares
    public function getPorcentajeVendidoAttribute()
    {
        if ($this->boletos_minimos == 0) return 0;
        return round(($this->boletos_vendidos / $this->boletos_minimos) * 100, 2);
    }

    public function getBoletosDisponiblesAttribute()
    {
        $total = $this->boletos_maximos ?? 999999;
        return max(0, $total - $this->boletos_vendidos - $this->boletos_reservados);
    }

    public function getEstaConfirmadaAttribute()
    {
        return $this->boletos_vendidos >= $this->boletos_minimos;
    }

    public function getPuedeEjecutarseAttribute()
    {
        if ($this->rifa_requerida_id) {
            $rifaRequerida = $this->rifaRequerida;
            return $rifaRequerida && $rifaRequerida->estado === 'finalizada';
        }
        return true;
    }

    public function getUrlSlugAttribute()
    {
        return route('rifas.show', $this->slug);
    }

    public function getTiempoRestanteAttribute()
    {
        if ($this->fecha_fin) {
            return now()->diffInDays($this->fecha_fin, false);
        }
        return null;
    }

    public function getEstaActivaAttribute()
    {
        return $this->estado === 'activa' && 
               $this->visible_publico && 
               ($this->fecha_inicio <= now()) &&
               ($this->fecha_fin >= now());
    }

    public function incrementarVistas()
    {
        $this->increment('vistas');
    }

    public function incrementarCompartidas()
    {
        $this->increment('compartidas');
    }

    public function actualizarRating($nuevoRating)
    {
        // Recalcular rating promedio basado en comentarios
        $comentarios = $this->comentarios()->whereNotNull('rating');
        $promedioActual = $comentarios->avg('rating');
        
        $this->update([
            'rating_promedio' => round($promedioActual, 2)
        ]);
    }

    public function actualizarContadorFavoritos()
    {
        $totalFavoritos = $this->favoritos()->count();
        $this->update(['favoritos' => $totalFavoritos]);
    }

    public function actualizarContadorComentarios()
    {
        $totalComentarios = $this->comentarios()->where('activo', true)->count();
        $this->update(['total_comentarios' => $totalComentarios]);
    }

    /**
     * Actualizar progreso de premios cuando cambian los boletos vendidos
     */
    public function actualizarProgresosPremios()
    {
        foreach ($this->premios as $premio) {
            // Actualizar progreso para cada nivel del premio
            foreach ($premio->niveles as $nivel) {
                $progresoNivel = \App\Models\ProgresoPremio::where('premio_id', $premio->id)
                                                          ->where('nivel_id', $nivel->id)
                                                          ->first();
                
                if ($progresoNivel) {
                    $progresoNivel->actualizarProgreso($this->boletos_vendidos);
                    
                    // Actualizar estado del nivel si se completa
                    if ($progresoNivel->objetivo_alcanzado && !$nivel->desbloqueado) {
                        $nivel->update([
                            'desbloqueado' => true,
                            'fecha_desbloqueo' => now()
                        ]);
                    }
                }
            }
            
            // Actualizar progreso total del premio
            $progresoTotal = \App\Models\ProgresoPremio::where('premio_id', $premio->id)
                                                      ->whereNull('nivel_id')
                                                      ->first();
            
            if ($progresoTotal) {
                $progresoTotal->actualizarProgreso($this->boletos_vendidos);
                
                // Verificar si todos los niveles están completados
                $todosCompletados = $premio->niveles->every(function($nivel) {
                    return \App\Models\ProgresoPremio::where('premio_id', $nivel->premio_id)
                                                     ->where('nivel_id', $nivel->id)
                                                     ->where('objetivo_alcanzado', true)
                                                     ->exists();
                });
                
                if ($todosCompletados && $premio->estado !== 'completado') {
                    $premio->update([
                        'estado' => 'completado',
                        'fecha_completado' => now()
                    ]);
                    
                    // Desbloquear siguiente premio si existe
                    $siguientePremio = $this->premios()->where('premio_requerido_id', $premio->id)->first();
                    if ($siguientePremio) {
                        $siguientePremio->update([
                            'estado' => 'activo',
                            'desbloqueado' => true,
                            'fecha_desbloqueo' => now()
                        ]);
                    }
                }
            }
        }
    }

    // Generar código único
    public static function generarCodigoUnico()
    {
        do {
            $codigo = 'RF' . strtoupper(substr(md5(time() . rand()), 0, 8));
        } while (self::where('codigo_unico', $codigo)->exists());
        
        return $codigo;
    }
}

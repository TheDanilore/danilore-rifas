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
        'fecha_limite_pago',
        'fecha_sorteo',
        'fecha_ultimo_sorteo',
        'tiempo_reserva_minutos',
        'dias_entrega_premio',
        // Estados y configuración
        'estado',
        'tipo',
        'modalidad',
        'tipo_sorteo',
        'metodo_pago_preferido',
        'categoria_id',
        'codigo_unico',
        'slug',
        // Configuraciones especiales
        'es_destacada',
        'es_premium',
        'es_patrocinada',
        'permite_multiples_boletos',
        'permite_seleccion_numeros',
        'permite_compra_grupos',
        'max_boletos_por_persona',
        'max_boletos_por_transaccion',
        'min_participantes',
        'requiere_verificacion_identidad',
        'permite_transferencia_boletos',
        'auto_sorteo_al_completar',
        // Términos y condiciones
        'terminos_condiciones',
        'bases_legales',
        'informacion_sorteo',
        // Sistema progresivo
        'total_premios',
        'premios_desbloqueados',
        'premios_entregados',
        'orden',
        'rifa_requerida_id',
        'porcentaje_completado_general',
        'porcentaje_minimo_activacion',
        'sistema_progresivo_activo',
        'reglas_progresion',
        // Estadísticas y métricas
        'vistas',
        'vistas_unicas',
        'favoritos',
        'compartidas',
        'clics_compartir',
        'rating_promedio',
        'total_comentarios',
        'total_participantes_unicos',
        'tasa_conversion',
        // Configuración financiera
        'comision_plataforma',
        'total_recaudado',
        'total_comisiones',
        'total_neto_organizador',
        'precio_minimo_garantizado',
        'bono_participacion',
        'estructura_precios',
        // Administración y control
        'creado_por',
        'organizador_id',
        'notas_admin',
        'notas_organizador',
        'configuracion_avanzada',
        'visible_publico',
        'destacar_en_inicio',
        'enviar_notificaciones',
        'fecha_publicacion',
        'fecha_moderacion',
        'moderado_por',
        'razon_suspension',
        'configuracion_seo',
        // Nuevos campos mejorados
        'limite_por_usuario',
        'codigo_promocional',
        'descuento_promocional',
        'codigo_valido_hasta',
        'codigo_max_usos',
        'codigo_usos_actuales',
        'configuracion_notificaciones',
        'etiquetas',
        'nivel_dificultad',
        'edad_minima',
        'paises_permitidos',
        'paises_restringidos',
        'configuracion_pagos',
        'tiempo_reserva_tickets',
        'max_tickets_por_compra',
        'mostrar_estadisticas',
        'permitir_comentarios',
        'moderar_comentarios',
        'video_promocional',
        'redes_sociales',
        'terminos_especificos',
        'politica_devoluciones',
        'meta_titulo',
        'meta_descripcion',
        'meta_keywords'
    ];

    protected $casts = [
        // Fechas
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'fecha_limite_pago' => 'datetime',
        'fecha_sorteo' => 'datetime',
        'fecha_ultimo_sorteo' => 'datetime',
        'fecha_publicacion' => 'datetime',
        'fecha_moderacion' => 'datetime',
        // Decimales
        'precio_boleto' => 'decimal:2',
        'rating_promedio' => 'decimal:2',
        'porcentaje_completado_general' => 'decimal:2',
        'porcentaje_minimo_activacion' => 'decimal:2',
        'comision_plataforma' => 'decimal:2',
        'total_recaudado' => 'decimal:2',
        'total_comisiones' => 'decimal:2',
        'total_neto_organizador' => 'decimal:2',
        'precio_minimo_garantizado' => 'decimal:2',
        'bono_participacion' => 'decimal:2',
        'tasa_conversion' => 'decimal:2',
        // JSON
        'imagenes_adicionales' => 'json',
        'media_gallery' => 'json',
        'colores_tema' => 'json',
        'configuracion_avanzada' => 'json',
        'reglas_progresion' => 'json',
        'estructura_precios' => 'json',
        'configuracion_seo' => 'json',
        // Booleanos
        'es_destacada' => 'boolean',
        'es_premium' => 'boolean',
        'es_patrocinada' => 'boolean',
        'permite_multiples_boletos' => 'boolean',
        'permite_seleccion_numeros' => 'boolean',
        'permite_compra_grupos' => 'boolean',
        'permite_transferencia_boletos' => 'boolean',
        'requiere_verificacion_identidad' => 'boolean',
        'auto_sorteo_al_completar' => 'boolean',
        'sistema_progresivo_activo' => 'boolean',
        'visible_publico' => 'boolean',
        'destacar_en_inicio' => 'boolean',
        'enviar_notificaciones' => 'boolean',
        // Enteros
        'boletos_vendidos' => 'integer',
        'boletos_reservados' => 'integer',
        'boletos_minimos' => 'integer',
        'boletos_maximos' => 'integer',
        'max_boletos_por_persona' => 'integer',
        'max_boletos_por_transaccion' => 'integer',
        'min_participantes' => 'integer',
        'tiempo_reserva_minutos' => 'integer',
        'dias_entrega_premio' => 'integer',
        'total_premios' => 'integer',
        'premios_desbloqueados' => 'integer',
        'premios_entregados' => 'integer',
        'orden' => 'integer',
        'vistas' => 'integer',
        'vistas_unicas' => 'integer',
        'favoritos' => 'integer',
        'compartidas' => 'integer',
        'clics_compartir' => 'integer',
        'total_comentarios' => 'integer',
        'total_participantes_unicos' => 'integer',
        // Nuevos casts
        'limite_por_usuario' => 'integer',
        'descuento_promocional' => 'decimal:2',
        'codigo_valido_hasta' => 'datetime',
        'codigo_max_usos' => 'integer',
        'codigo_usos_actuales' => 'integer',
        'configuracion_notificaciones' => 'json',
        'etiquetas' => 'json',
        'nivel_dificultad' => 'integer',
        'edad_minima' => 'integer',
        'paises_permitidos' => 'json',
        'paises_restringidos' => 'json',
        'configuracion_pagos' => 'json',
        'tiempo_reserva_tickets' => 'integer',
        'max_tickets_por_compra' => 'integer',
        'mostrar_estadisticas' => 'boolean',
        'permitir_comentarios' => 'boolean',
        'moderar_comentarios' => 'boolean',
        'redes_sociales' => 'json'
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

    public function organizador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizador_id');
    }

    public function moderadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderado_por');
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

    public function scopePatrocinadas($query)
    {
        return $query->where('es_patrocinada', true);
    }

    public function scopeDestacadasEnInicio($query)
    {
        return $query->where('destacar_en_inicio', true);
    }

    public function scopeConSorteoAutomatico($query)
    {
        return $query->where('auto_sorteo_al_completar', true);
    }

    public function scopeConSistemaProgresivo($query)
    {
        return $query->where('sistema_progresivo_activo', true);
    }

    public function scopePorTipoSorteo($query, $tipo)
    {
        return $query->where('tipo_sorteo', $tipo);
    }

    public function scopePorMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago_preferido', $metodo);
    }

    public function scopeConSeleccionNumeros($query)
    {
        return $query->where('permite_seleccion_numeros', true);
    }

    public function scopeConCompraGrupos($query)
    {
        return $query->where('permite_compra_grupos', true);
    }

    public function scopePopulares($query, $limite = 10)
    {
        return $query->orderByDesc('vistas')
                    ->orderByDesc('total_participantes_unicos')
                    ->limit($limite);
    }

    public function scopeProximasASortear($query, $dias = 7)
    {
        return $query->where('fecha_sorteo', '>=', now())
                    ->where('fecha_sorteo', '<=', now()->addDays($dias))
                    ->orderBy('fecha_sorteo');
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

    public function getRequiereMinimosAttribute()
    {
        return $this->total_participantes_unicos < $this->min_participantes;
    }

    public function getPuedeSeleccionarNumerosAttribute()
    {
        return $this->permite_seleccion_numeros && $this->estado === 'activa';
    }

    public function getTieneEspacioDisponibleAttribute()
    {
        return $this->boletos_disponibles > 0;
    }

    public function getProximoSorteoAttribute()
    {
        if ($this->fecha_sorteo && $this->fecha_sorteo > now()) {
            return $this->fecha_sorteo->diffForHumans();
        }
        return null;
    }

    public function getDiasPendientesEntregaAttribute()
    {
        return $this->dias_entrega_premio ?? 30;
    }

    public function getEsRifaEspecialAttribute()
    {
        return $this->es_premium || $this->es_patrocinada || $this->destacar_en_inicio;
    }

    public function getMetodosPagoDisponiblesAttribute()
    {
        $metodos = ['yape', 'plin', 'transferencia', 'efectivo'];
        
        if ($this->metodo_pago_preferido !== 'cualquiera') {
            // Priorizar el método preferido
            $metodos = array_unique(array_merge([$this->metodo_pago_preferido], $metodos));
        }
        
        return $metodos;
    }

    /**
     * Métodos para sorteo automático
     */
    public function puedeRealizarSorteoAutomatico()
    {
        return $this->auto_sorteo_al_completar && 
               $this->boletos_vendidos >= $this->total_boletos &&
               $this->estado === 'activa';
    }

    public function ejecutarSorteoAutomatico()
    {
        if (!$this->puedeRealizarSorteoAutomatico()) {
            return false;
        }

        // Lógica para ejecutar sorteo automático
        $this->update([
            'estado' => 'sorteada',
            'fecha_sorteo' => now(),
            'fecha_ultimo_sorteo' => now()
        ]);

        return true;
    }

    /**
     * Métodos para sistema progresivo
     */
    public function activarSistemaProgresivo(array $reglas)
    {
        $this->update([
            'sistema_progresivo_activo' => true,
            'reglas_progresion' => $reglas
        ]);
    }

    public function aplicarProgresoVentas()
    {
        if (!$this->sistema_progresivo_activo) {
            return false;
        }

        $porcentajeVendido = ($this->boletos_vendidos / $this->total_boletos) * 100;
        $reglas = $this->reglas_progresion ?? [];

        foreach ($reglas as $regla) {
            if ($porcentajeVendido >= $regla['porcentaje_minimo']) {
                // Aplicar beneficios de la regla
                if (isset($regla['descuento_adicional'])) {
                    // Lógica para aplicar descuentos progresivos
                }
            }
        }

        return true;
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

    /**
     * Métodos para estadísticas y análisis avanzados
     */
    public function actualizarEstadisticasAvanzadas()
    {
        $ventasHoy = $this->ventas()->whereDate('created_at', today())->count();
        $participantesUnicos = $this->ventas()->distinct('user_id')->count('user_id');
        $vistasHoy = $this->vistas_unicas;
        
        $tasaConversion = $vistasHoy > 0 ? ($this->boletos_vendidos / $vistasHoy) * 100 : 0;

        $this->update([
            'total_participantes_unicos' => $participantesUnicos,
            'tasa_conversion' => round($tasaConversion, 2)
        ]);
    }

    public function incrementarVistasUnicas()
    {
        $this->increment('vistas_unicas');
    }

    public function getEstadisticasCompletas()
    {
        return [
            'boletos_vendidos' => $this->boletos_vendidos,
            'boletos_disponibles' => $this->boletos_disponibles,
            'porcentaje_vendido' => $this->porcentaje_vendido,
            'total_participantes' => $this->total_participantes_unicos,
            'vistas_unicas' => $this->vistas_unicas,
            'tasa_conversion' => $this->tasa_conversion . '%',
            'ingresos_totales' => $this->total_recaudado,
            'dias_restantes' => $this->fecha_fin ? $this->fecha_fin->diffInDays(now()) : null,
            'es_patrocinada' => $this->es_patrocinada,
            'destacada' => $this->destacar_en_inicio,
            'tipo_sorteo' => $this->tipo_sorteo,
            'metodo_pago_preferido' => $this->metodo_pago_preferido,
        ];
    }

    /**
     * Métodos para moderación y administración
     */
    public function marcarComoModerada($moderadorId, $destacar = false)
    {
        $this->update([
            'moderado_por' => $moderadorId,
            'fecha_moderacion' => now(),
            'destacar_en_inicio' => $destacar
        ]);
    }

    public function suspender($razon, $moderadorId)
    {
        $this->update([
            'estado' => 'suspendida',
            'razon_suspension' => $razon,
            'moderado_por' => $moderadorId,
            'fecha_moderacion' => now(),
            'visible_publico' => false
        ]);
    }

    public function reactivar($moderadorId)
    {
        $this->update([
            'estado' => 'activa',
            'razon_suspension' => null,
            'moderado_por' => $moderadorId,
            'fecha_moderacion' => now(),
            'visible_publico' => true
        ]);
    }

    /**
     * Métodos para configuración SEO
     */
    public function actualizarSEO(array $configuracion)
    {
        $this->update(['configuracion_seo' => $configuracion]);
    }

    public function getSeoTituloAttribute()
    {
        $seo = $this->configuracion_seo ?? [];
        return $seo['titulo'] ?? $this->titulo;
    }

    public function getSeoDescripcionAttribute()
    {
        $seo = $this->configuracion_seo ?? [];
        return $seo['descripcion'] ?? $this->descripcion;
    }

    public function getSeoKeywordsAttribute()
    {
        $seo = $this->configuracion_seo ?? [];
        return $seo['keywords'] ?? [];
    }
}

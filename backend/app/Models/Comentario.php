<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comentario extends Model
{
    use HasFactory;

    protected $table = 'comentarios';

    protected $fillable = [
        'user_id',
        'rifa_id',
        'comentario_padre_id',
        'comentario',
        'rating',
        'activo',
        'moderado',
        'moderado_por',
        'fecha_moderacion',
        'razon_moderacion',
        'likes',
        'dislikes',
        'ip_address',
        'user_agent',
        'editado',
        'fecha_edicion',
        'editado_por',
        'es_spam',
        'reportes',
        'imagenes',
        'notificar_respuestas'
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'activo' => 'boolean',
        'moderado' => 'boolean',
        'fecha_moderacion' => 'datetime',
        'likes' => 'integer',
        'dislikes' => 'integer',
        'editado' => 'boolean',
        'fecha_edicion' => 'datetime',
        'es_spam' => 'boolean',
        'reportes' => 'integer',
        'imagenes' => 'json',
        'notificar_respuestas' => 'boolean'
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function comentarioPadre(): BelongsTo
    {
        return $this->belongsTo(Comentario::class, 'comentario_padre_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(Comentario::class, 'comentario_padre_id')
                    ->orderBy('created_at', 'asc');
    }

    public function moderadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderado_por');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeModerados($query)
    {
        return $query->where('moderado', true);
    }

    public function scopePendienteModeracion($query)
    {
        return $query->where('moderado', false);
    }

    public function scopePublicos($query)
    {
        return $query->where('activo', true)
                    ->where('moderado', true);
    }

    public function scopePorRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopePrincipales($query)
    {
        return $query->whereNull('comentario_padre_id');
    }

    public function scopeRespuestas($query)
    {
        return $query->whereNotNull('comentario_padre_id');
    }

    public function scopeReportados($query)
    {
        return $query->where('reportado', true);
    }

    public function scopePorRifa($query, $rifaId)
    {
        return $query->where('rifa_id', $rifaId);
    }

    // Métodos auxiliares
    public function aprobar($usuarioAprobador = null)
    {
        $this->update([
            'aprobado' => true,
            'aprobado_por' => $usuarioAprobador,
            'fecha_aprobacion' => now(),
            'estado' => 'publico'
        ]);

        return $this;
    }

    public function rechazar($motivo = null)
    {
        $this->update([
            'aprobado' => false,
            'estado' => 'rechazado',
            'metadata' => array_merge($this->metadata ?? [], [
                'motivo_rechazo' => $motivo,
                'fecha_rechazo' => now()
            ])
        ]);

        return $this;
    }

    public function reportar($motivo = null, $usuarioReportador = null)
    {
        $this->increment('reportes_count');
        
        $metadata = $this->metadata ?? [];
        $reportes = $metadata['reportes'] ?? [];
        
        $reportes[] = [
            'usuario_id' => $usuarioReportador,
            'motivo' => $motivo,
            'fecha' => now(),
            'ip' => request()->ip()
        ];
        
        $metadata['reportes'] = $reportes;
        
        $this->update([
            'reportado' => true,
            'motivo_reporte' => $motivo,
            'metadata' => $metadata
        ]);

        // Auto-ocultar si tiene muchos reportes
        if ($this->reportes_count >= 3) {
            $this->update(['estado' => 'oculto']);
        }

        return $this;
    }

    public function responder($contenido, $userId, $opciones = [])
    {
        return self::create(array_merge([
            'user_id' => $userId,
            'rifa_id' => $this->rifa_id,
            'contenido' => $contenido,
            'comentario_padre_id' => $this->id,
            'nivel' => $this->nivel + 1,
            'es_anonimo' => false,
            'mostrar_nombre_usuario' => true,
            'aprobado' => false,
            'estado' => 'pendiente',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ], $opciones));
    }

    public function getNombreMostrarAttribute()
    {
        if ($this->es_anonimo || !$this->mostrar_nombre_usuario) {
            return 'Usuario Anónimo';
        }

        return $this->user->nombre_completo ?? 'Usuario';
    }

    public function getEsRespuestaAttribute()
    {
        return !is_null($this->comentario_padre_id);
    }

    public function getTieneRespuestasAttribute()
    {
        return $this->respuestas()->exists();
    }

    public function getCantidadRespuestasAttribute()
    {
        return $this->respuestas()->aprobados()->count();
    }

    public function getEstaAprobadoAttribute()
    {
        return $this->aprobado && $this->estado === 'publico';
    }

    public function getEstaReportadoAttribute()
    {
        return $this->reportado && $this->reportes_count > 0;
    }

    public function getCalificacionEstrellas()
    {
        return str_repeat('★', $this->calificacion) . str_repeat('☆', 5 - $this->calificacion);
    }

    // Obtener estadísticas de comentarios
    public static function estadisticasRifa($rifaId)
    {
        $comentarios = self::where('rifa_id', $rifaId);
        
        return [
            'total' => $comentarios->count(),
            'aprobados' => $comentarios->aprobados()->count(),
            'pendientes' => $comentarios->pendientes()->count(),
            'reportados' => $comentarios->reportados()->count(),
            'calificacion_promedio' => $comentarios->aprobados()
                                                 ->whereNotNull('calificacion')
                                                 ->avg('calificacion'),
            'por_calificacion' => [
                '5' => $comentarios->aprobados()->where('calificacion', 5)->count(),
                '4' => $comentarios->aprobados()->where('calificacion', 4)->count(),
                '3' => $comentarios->aprobados()->where('calificacion', 3)->count(),
                '2' => $comentarios->aprobados()->where('calificacion', 2)->count(),
                '1' => $comentarios->aprobados()->where('calificacion', 1)->count(),
            ]
        ];
    }

    public static function limpiarComentariosAntiguos($diasAntiguedad = 365)
    {
        return self::where('created_at', '<', now()->subDays($diasAntiguedad))
                   ->where('estado', 'oculto')
                   ->delete();
    }

    // Detectar spam básico
    public function detectarSpam()
    {
        $contenido = strtolower($this->contenido);
        $indicadoresSpam = ['http://', 'https://', 'www.', '@', 'telegram', 'whatsapp'];
        
        foreach ($indicadoresSpam as $indicador) {
            if (strpos($contenido, $indicador) !== false) {
                return true;
            }
        }

        // Verificar repetición excesiva de caracteres
        if (preg_match('/(.)\1{4,}/', $contenido)) {
            return true;
        }

        return false;
    }
}
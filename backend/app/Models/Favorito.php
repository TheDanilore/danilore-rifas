<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorito extends Model
{
    use HasFactory;

    protected $table = 'favoritos';

    protected $fillable = [
        'user_id',
        'rifa_id',
        'notificar_cambios',
        'notificar_limite',
        'notificar_sorteo',
        'fecha_agregado',
        'activo'
    ];

    protected $casts = [
        'notificar_cambios' => 'boolean',
        'notificar_limite' => 'boolean',
        'notificar_sorteo' => 'boolean',
        'fecha_agregado' => 'datetime',
        'activo' => 'boolean'
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

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeConNotificaciones($query, $tipo = null)
    {
        $query = $query->where('activo', true);
        
        if ($tipo === 'cambios') {
            return $query->where('notificar_cambios', true);
        } elseif ($tipo === 'limite') {
            return $query->where('notificar_limite', true);
        } elseif ($tipo === 'sorteo') {
            return $query->where('notificar_sorteo', true);
        }
        
        return $query->where(function($q) {
            $q->where('notificar_cambios', true)
              ->orWhere('notificar_limite', true)
              ->orWhere('notificar_sorteo', true);
        });
    }

    // Métodos auxiliares
    public static function toggleFavorito($userId, $rifaId, $opciones = [])
    {
        $favorito = self::where('user_id', $userId)
                       ->where('rifa_id', $rifaId)
                       ->first();

        if ($favorito) {
            if ($favorito->activo) {
                // Si está activo, lo desactivamos
                $favorito->update(['activo' => false]);
                return ['accion' => 'removido', 'favorito' => $favorito];
            } else {
                // Si está inactivo, lo reactivamos
                $favorito->update(array_merge(['activo' => true], $opciones));
                return ['accion' => 'agregado', 'favorito' => $favorito];
            }
        } else {
            // Crear nuevo favorito
            $favorito = self::create(array_merge([
                'user_id' => $userId,
                'rifa_id' => $rifaId,
                'notificar_cambios' => true,
                'notificar_limite' => true,
                'notificar_sorteo' => true,
                'fecha_agregado' => now(),
                'activo' => true
            ], $opciones));
            
            return ['accion' => 'agregado', 'favorito' => $favorito];
        }
    }

    public function configurarNotificaciones($cambios = null, $limite = null, $sorteo = null)
    {
        $updates = [];
        
        if ($cambios !== null) {
            $updates['notificar_cambios'] = $cambios;
        }
        
        if ($limite !== null) {
            $updates['notificar_limite'] = $limite;
        }
        
        if ($sorteo !== null) {
            $updates['notificar_sorteo'] = $sorteo;
        }
        
        if (!empty($updates)) {
            $this->update($updates);
        }
        
        return $this;
    }

    public function getTieneNotificacionesAttribute()
    {
        return $this->notificar_cambios || $this->notificar_limite || $this->notificar_sorteo;
    }

    public function getEsFavoritoActivoAttribute()
    {
        return $this->activo && $this->rifa->esta_activa;
    }

    // Verificar si el usuario debe ser notificado sobre esta rifa
    public function debeNotificarCambio($tipoEvento)
    {
        if (!$this->activo) {
            return false;
        }

        switch ($tipoEvento) {
            case 'precio_actualizado':
            case 'imagen_actualizada':
            case 'descripcion_actualizada':
                return $this->notificar_cambios;
                
            case 'cerca_limite':
            case 'limite_alcanzado':
                return $this->notificar_limite;
                
            case 'sorteo_programado':
            case 'sorteo_realizado':
                return $this->notificar_sorteo;
                
            default:
                return false;
        }
    }

    // Obtener estadísticas de favoritos por usuario
    public static function estadisticasUsuario($userId)
    {
        return [
            'total_favoritos' => self::where('user_id', $userId)->where('activo', true)->count(),
            'rifas_activas' => self::where('user_id', $userId)
                                 ->where('activo', true)
                                 ->whereHas('rifa', function($q) {
                                     $q->where('estado', 'activa');
                                 })
                                 ->count(),
            'con_notificaciones' => self::where('user_id', $userId)
                                      ->where('activo', true)
                                      ->where(function($q) {
                                          $q->where('notificar_cambios', true)
                                            ->orWhere('notificar_limite', true)
                                            ->orWhere('notificar_sorteo', true);
                                      })
                                      ->count(),
            'fecha_ultimo_agregado' => self::where('user_id', $userId)
                                         ->where('activo', true)
                                         ->latest('fecha_agregado')
                                         ->value('fecha_agregado')
        ];
    }

    // Limpiar favoritos de rifas finalizadas automáticamente
    public static function limpiarFavoritosFinalizados()
    {
        return self::whereHas('rifa', function($q) {
                     $q->whereIn('estado', ['finalizada', 'cancelada']);
                 })
                 ->where('activo', true)
                 ->update(['activo' => false]);
    }
}
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
        'notificar',
        'fecha_expiracion',
        'prioridad',
        'notas',
        'categorias_usuario',
        'configuracion_alertas',
        'ultimo_acceso',
        'contador_accesos',
        'es_publico',
        'recordatorio_fecha',
        'recordatorio_mensaje'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'notificar' => 'boolean',
        'fecha_expiracion' => 'datetime',
        'prioridad' => 'integer',
        'categorias_usuario' => 'json',
        'configuracion_alertas' => 'json',
        'ultimo_acceso' => 'datetime',
        'contador_accesos' => 'integer',
        'es_publico' => 'boolean',
        'recordatorio_fecha' => 'datetime'
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
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorRifa($query, $rifaId)
    {
        return $query->where('rifa_id', $rifaId);
    }

    // Métodos auxiliares
    public static function toggleFavorito($userId, $rifaId)
    {
        $favorito = self::where('user_id', $userId)
                       ->where('rifa_id', $rifaId)
                       ->first();

        if ($favorito) {
            // Si existe, lo eliminamos (toggle off)
            $favorito->delete();
            return ['accion' => 'removido', 'favorito' => null];
        } else {
            // Crear nuevo favorito
            $favorito = self::create([
                'user_id' => $userId,
                'rifa_id' => $rifaId
            ]);
            
            return ['accion' => 'agregado', 'favorito' => $favorito];
        }
    }

    // Verificar si una rifa es favorita del usuario
    public static function esFavorito($userId, $rifaId)
    {
        return self::where('user_id', $userId)
                  ->where('rifa_id', $rifaId)
                  ->exists();
    }
}
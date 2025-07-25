<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'titulo',
        'mensaje',
        'tipo',
        'canal',
        'datos_adicionales',
        'leida',
        'fecha_leida',
        'enviada',
        'fecha_envio',
        'error_envio',
        'referencia_externa'
    ];

    protected $casts = [
        'datos_adicionales' => 'json',
        'leida' => 'boolean',
        'enviada' => 'boolean',
        'fecha_leida' => 'datetime',
        'fecha_envio' => 'datetime'
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    public function scopeLeidas($query)
    {
        return $query->where('leida', true);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopePorCanal($query, $canal)
    {
        return $query->where('canal', $canal);
    }

    public function scopeEnviadas($query)
    {
        return $query->where('enviada', true);
    }

    // Métodos auxiliares
    public function marcarComoLeida()
    {
        if (!$this->leida) {
            $this->update([
                'leida' => true,
                'fecha_leida' => now()
            ]);
        }
    }

    public function marcarComoEnviada($referenciaExterna = null)
    {
        $this->update([
            'enviada' => true,
            'fecha_envio' => now(),
            'referencia_externa' => $referenciaExterna
        ]);
    }

    public function marcarErrorEnvio($error)
    {
        $this->update([
            'enviada' => false,
            'error_envio' => $error
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Boleto extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'user_id',
        'venta_id',
        // Información del boleto
        'numero',
        'codigo_verificacion',
        'precio_pagado',
        // Estados y fechas
        'estado',
        'fecha_reserva',
        'fecha_expiracion_reserva',
        'fecha_pago',
        'fecha_confirmacion',
        // Información del sorteo
        'es_ganador',
        'posicion_sorteo',
        'tipo_premio',
        'valor_premio',
        'fecha_sorteo',
        'premio_entregado',
        'fecha_entrega_premio',
        // Transferencias de boletos
        'transferido_a',
        'transferido_por',
        'fecha_transferencia',
        'motivo_transferencia',
        // Información adicional
        'origen',
        'metadatos',
        'notas',
        // Auditoría
        'ip_creacion',
        'creado_por',
    ];

    protected $casts = [
        'precio_pagado' => 'decimal:2',
        'valor_premio' => 'decimal:2',
        'fecha_reserva' => 'datetime',
        'fecha_expiracion_reserva' => 'datetime',
        'fecha_pago' => 'datetime',
        'fecha_confirmacion' => 'datetime',
        'fecha_sorteo' => 'datetime',
        'fecha_entrega_premio' => 'datetime',
        'fecha_transferencia' => 'datetime',
        'es_ganador' => 'boolean',
        'premio_entregado' => 'boolean',
        'metadatos' => 'json',
        'posicion_sorteo' => 'integer',
    ];

    // Relaciones
    public function rifa(): BelongsTo
    {
        return $this->belongsTo(Rifa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function transferidoA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferido_a');
    }

    public function transferidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferido_por');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function sorteoGanador(): HasOne
    {
        return $this->hasOne(Sorteo::class, 'boleto_ganador_id');
    }

    // Scopes
    public function scopeReservados($query)
    {
        return $query->where('estado', 'reservado');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeConfirmados($query)
    {
        return $query->where('estado', 'confirmado');
    }

    public function scopeGanadores($query)
    {
        return $query->where('es_ganador', true);
    }

    public function scopeTransferidos($query)
    {
        return $query->where('estado', 'transferido');
    }

    public function scopePorOrigen($query, $origen)
    {
        return $query->where('origen', $origen);
    }

    public function scopeDisponibles($query, $rifaId)
    {
        return $query->where('rifa_id', $rifaId)
                    ->where('estado', '!=', 'pagado')
                    ->where('estado', '!=', 'confirmado')
                    ->where(function($q) {
                        $q->where('fecha_expiracion_reserva', '<', now())
                          ->orWhereNull('fecha_expiracion_reserva');
                    });
    }

    // Métodos auxiliares
    public function estaDisponible()
    {
        return !in_array($this->estado, ['pagado', 'confirmado']) && 
               ($this->fecha_expiracion_reserva === null || 
                $this->fecha_expiracion_reserva < now());
    }

    public function estaReservadoYValido()
    {
        return $this->estado === 'reservado' && 
               $this->fecha_expiracion_reserva && 
               $this->fecha_expiracion_reserva > now();
    }

    public function puedeSerTransferido()
    {
        return in_array($this->estado, ['pagado', 'confirmado']) &&
               $this->rifa->permite_transferencia_boletos &&
               !$this->es_ganador;
    }

    public function transferirA($usuarioDestino, $motivo = null)
    {
        if (!$this->puedeSerTransferido()) {
            throw new \Exception('Este boleto no puede ser transferido');
        }

        $this->update([
            'transferido_a' => $usuarioDestino->id,
            'transferido_por' => $this->user_id,
            'user_id' => $usuarioDestino->id,
            'fecha_transferencia' => now(),
            'motivo_transferencia' => $motivo,
            'estado' => 'transferido'
        ]);

        return $this;
    }

    public function marcarComoGanador($posicion, $tipoPremio, $valorPremio = null)
    {
        $this->update([
            'es_ganador' => true,
            'posicion_sorteo' => $posicion,
            'tipo_premio' => $tipoPremio,
            'valor_premio' => $valorPremio,
            'fecha_sorteo' => now()
        ]);

        return $this;
    }

    public function marcarPremioEntregado()
    {
        if (!$this->es_ganador) {
            throw new \Exception('Este boleto no es ganador');
        }

        $this->update([
            'premio_entregado' => true,
            'fecha_entrega_premio' => now()
        ]);

        return $this;
    }

    public function getHistorialTransferenciasAttribute()
    {
        return [
            'transferido_de' => $this->transferidoPor,
            'transferido_a' => $this->transferidoA,
            'fecha' => $this->fecha_transferencia,
            'motivo' => $this->motivo_transferencia
        ];
    }

    public static function generarCodigoVerificacion()
    {
        do {
            $codigo = strtoupper(substr(md5(time() . rand()), 0, 10));
        } while (self::where('codigo_verificacion', $codigo)->exists());
        
        return $codigo;
    }
}

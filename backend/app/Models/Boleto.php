<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Boleto extends Model
{
    use HasFactory;

    protected $fillable = [
        'rifa_id',
        'user_id',
        'venta_id',
        'numero',
        'precio_pagado',
        'estado',
        'fecha_reserva',
        'fecha_expiracion_reserva',
        'fecha_pago',
        'codigo_verificacion',
        'es_ganador'
    ];

    protected $casts = [
        'precio_pagado' => 'decimal:2',
        'fecha_reserva' => 'datetime',
        'fecha_expiracion_reserva' => 'datetime',
        'fecha_pago' => 'datetime',
        'es_ganador' => 'boolean'
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

    // Scopes
    public function scopeReservados($query)
    {
        return $query->where('estado', 'reservado');
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeGanadores($query)
    {
        return $query->where('es_ganador', true);
    }

    public function scopeDisponibles($query, $rifaId)
    {
        return $query->where('rifa_id', $rifaId)
                    ->where('estado', '!=', 'pagado')
                    ->where(function($q) {
                        $q->where('fecha_expiracion_reserva', '<', now())
                          ->orWhereNull('fecha_expiracion_reserva');
                    });
    }

    // Métodos auxiliares
    public function estaDisponible()
    {
        return $this->estado !== 'pagado' && 
               ($this->fecha_expiracion_reserva === null || 
                $this->fecha_expiracion_reserva < now());
    }

    public function estaReservadoYValido()
    {
        return $this->estado === 'reservado' && 
               $this->fecha_expiracion_reserva && 
               $this->fecha_expiracion_reserva > now();
    }

    public static function generarCodigoVerificacion()
    {
        do {
            $codigo = strtoupper(substr(md5(time() . rand()), 0, 10));
        } while (self::where('codigo_verificacion', $codigo)->exists());
        
        return $codigo;
    }
}

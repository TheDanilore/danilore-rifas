<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rifa_id',
        'codigo_venta',
        'cantidad_boletos',
        'subtotal',
        'descuento',
        'total',
        'estado',
        'metodo_pago',
        'fecha_expiracion',
        'notas_cliente',
        'notas_admin',
        'comprador_nombre',
        'comprador_email',
        'comprador_telefono',
        'comprador_tipo_documento',
        'comprador_numero_documento',
        'referencia_pago',
        'fecha_pago',
        'monto_pagado',
        'comprobante_pago'
    ];

    protected $casts = [
        'cantidad_boletos' => 'integer',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'fecha_expiracion' => 'datetime',
        'fecha_pago' => 'datetime'
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

    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagada');
    }

    public function scopeExpiradas($query)
    {
        return $query->where('estado', 'expirada')
                    ->orWhere('fecha_expiracion', '<', now());
    }

    // Métodos auxiliares
    public function estaExpirada()
    {
        return $this->fecha_expiracion && $this->fecha_expiracion < now();
    }

    public function puedeSerPagada()
    {
        return $this->estado === 'pendiente' && !$this->estaExpirada();
    }

    public static function generarCodigoVenta()
    {
        do {
            $codigo = 'VT' . date('Ymd') . strtoupper(substr(md5(time() . rand()), 0, 6));
        } while (self::where('codigo_venta', $codigo)->exists());
        
        return $codigo;
    }
}

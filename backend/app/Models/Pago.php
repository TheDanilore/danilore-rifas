<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'metodo_pago',
        'monto',
        'referencia_externa',
        'numero_operacion',
        'fecha_transaccion',
        'estado',
        'comprobante_url',
        'notas_verificacion',
        'verificado_por',
        'fecha_verificacion',
        'datos_pago'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_transaccion' => 'datetime',
        'fecha_verificacion' => 'datetime',
        'datos_pago' => 'json'
    ];

    // Relaciones
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeVerificados($query)
    {
        return $query->where('estado', 'verificado');
    }

    public function scopeRechazados($query)
    {
        return $query->where('estado', 'rechazado');
    }

    public function scopePorMetodo($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    // Métodos auxiliares
    public function puedeSerVerificado()
    {
        return $this->estado === 'pendiente' && $this->comprobante_url;
    }

    public function marcarComoVerificado($adminId, $notas = null)
    {
        $this->update([
            'estado' => 'verificado',
            'verificado_por' => $adminId,
            'fecha_verificacion' => now(),
            'notas_verificacion' => $notas
        ]);
    }

    public function marcarComoRechazado($adminId, $notas)
    {
        $this->update([
            'estado' => 'rechazado',
            'verificado_por' => $adminId,
            'fecha_verificacion' => now(),
            'notas_verificacion' => $notas
        ]);
    }
}

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
        // Información de la venta
        'cantidad_boletos',
        'precio_unitario',
        'subtotal',
        'descuento',
        'impuestos',
        'comision',
        'total',
        // Estados y control
        'estado',
        'metodo_pago',
        'fecha_reserva',
        'fecha_expiracion',
        'fecha_pago',
        'fecha_confirmacion',
        // Información adicional
        'numeros_seleccionados',
        'cupon_descuento',
        'porcentaje_descuento',
        'notas_cliente',
        'notas_admin',
        // Datos del comprador
        'usar_datos_usuario',
        'comprador_nombre',
        'comprador_email',
        'comprador_telefono',
        'comprador_tipo_documento',
        'comprador_numero_documento',
        // Información de entrega/contacto
        'direccion_entrega',
        'ciudad_entrega',
        'telefono_contacto',
        'instrucciones_entrega',
        // Datos de pago
        'referencia_pago',
        'monto_pagado',
        'comprobante_pago',
        'datos_pago_adicionales',
        // Seguimiento y auditoría
        'ip_cliente',
        'user_agent',
        'origen',
        'procesado_por',
        'fecha_procesamiento',
    ];

    protected $casts = [
        'cantidad_boletos' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'impuestos' => 'decimal:2',
        'comision' => 'decimal:2',
        'total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'fecha_reserva' => 'datetime',
        'fecha_expiracion' => 'datetime',
        'fecha_pago' => 'datetime',
        'fecha_confirmacion' => 'datetime',
        'fecha_procesamiento' => 'datetime',
        'usar_datos_usuario' => 'boolean',
        'numeros_seleccionados' => 'json',
        'datos_pago_adicionales' => 'json',
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

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }

    public function cupon()
    {
        return $this->belongsTo(Cupon::class, 'cupon_descuento', 'codigo');
    }

    // Scopes
    public function scopeCarrito($query)
    {
        return $query->where('estado', 'carrito');
    }

    public function scopeReservadas($query)
    {
        return $query->where('estado', 'reservado');
    }

    public function scopePendientesPago($query)
    {
        return $query->where('estado', 'pendiente_pago');
    }

    public function scopePagadas($query)
    {
        return $query->where('estado', 'pagado');
    }

    public function scopeConfirmadas($query)
    {
        return $query->where('estado', 'confirmado');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', 'cancelado');
    }

    public function scopeExpiradas($query)
    {
        return $query->where(function($query) {
            $query->where('estado', 'expirado')
                  ->orWhere('fecha_expiracion', '<', now());
        });
    }

    public function scopePorMetodoPago($query, $metodo)
    {
        return $query->where('metodo_pago', $metodo);
    }

    public function scopePorOrigen($query, $origen)
    {
        return $query->where('origen', $origen);
    }

    // Métodos auxiliares
    public function estaExpirada()
    {
        return $this->fecha_expiracion && $this->fecha_expiracion < now();
    }

    public function puedeSerPagada()
    {
        return in_array($this->estado, ['reservado', 'pendiente_pago']) && !$this->estaExpirada();
    }

    public function puedeSerCancelada()
    {
        return !in_array($this->estado, ['confirmado', 'cancelado', 'reembolsado']);
    }

    public function aplicarCupon($cuponCodigo)
    {
        $cupon = Cupon::where('codigo', $cuponCodigo)
                     ->where('activo', true)
                     ->where('fecha_inicio', '<=', now())
                     ->where('fecha_fin', '>=', now())
                     ->first();

        if (!$cupon || !$cupon->puedeSerUsado($this->user_id, $this->rifa_id)) {
            throw new \Exception('Cupón inválido o no disponible');
        }

        $descuento = $cupon->calcularDescuento($this->subtotal);
        
        $this->update([
            'cupon_descuento' => $cuponCodigo,
            'descuento' => $descuento,
            'porcentaje_descuento' => $cupon->tipo_descuento === 'porcentaje' ? $cupon->valor_descuento : 0,
            'total' => $this->subtotal - $descuento + $this->impuestos
        ]);

        return $this;
    }

    public function calcularTotal()
    {
        $total = $this->subtotal - $this->descuento + $this->impuestos + $this->comision;
        return max(0, $total);
    }

    public function marcarComoPagada($metodoPago, $referenciaPago = null, $montoPagado = null)
    {
        $this->update([
            'estado' => 'pagado',
            'metodo_pago' => $metodoPago,
            'fecha_pago' => now(),
            'referencia_pago' => $referenciaPago,
            'monto_pagado' => $montoPagado ?? $this->total
        ]);

        // Actualizar boletos a pagados
        $this->boletos()->update([
            'estado' => 'pagado',
            'fecha_pago' => now()
        ]);

        return $this;
    }

    public function confirmarVenta($adminId = null)
    {
        $this->update([
            'estado' => 'confirmado',
            'fecha_confirmacion' => now(),
            'procesado_por' => $adminId
        ]);

        // Actualizar boletos a confirmados
        $this->boletos()->update([
            'estado' => 'confirmado',
            'fecha_confirmacion' => now()
        ]);

        // Actualizar estadísticas del usuario
        $this->user->actualizarEstadisticas(
            $this->cantidad_boletos,
            $this->total,
            $this->user->primera_compra === null
        );

        return $this;
    }

    public function cancelarVenta($motivo = null)
    {
        if (!$this->puedeSerCancelada()) {
            throw new \Exception('Esta venta no puede ser cancelada');
        }

        $this->update([
            'estado' => 'cancelado',
            'notas_admin' => $motivo
        ]);

        // Liberar boletos
        $this->boletos()->update(['estado' => 'cancelado']);

        return $this;
    }

    public function getDatosCompradorAttribute()
    {
        if ($this->usar_datos_usuario && $this->user) {
            return [
                'nombre' => $this->user->name,
                'email' => $this->user->email,
                'telefono' => $this->user->telefono,
                'tipo_documento' => $this->user->tipo_documento,
                'numero_documento' => $this->user->numero_documento,
            ];
        }

        return [
            'nombre' => $this->comprador_nombre,
            'email' => $this->comprador_email,
            'telefono' => $this->comprador_telefono,
            'tipo_documento' => $this->comprador_tipo_documento,
            'numero_documento' => $this->comprador_numero_documento,
        ];
    }

    public static function generarCodigoVenta()
    {
        do {
            $codigo = 'VT' . date('Ymd') . strtoupper(substr(md5(time() . rand()), 0, 6));
        } while (self::where('codigo_venta', $codigo)->exists());
        
        return $codigo;
    }
}

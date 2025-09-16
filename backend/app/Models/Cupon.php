<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cupon extends Model
{
    use HasFactory;

    protected $table = 'cupones';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'tipo_descuento',
        'valor_descuento',
        'descuento_maximo',
        'compra_minima',
        'usos_maximos',
        'usos_actuales',
        'usos_por_usuario',
        'solo_primera_compra',
        'fecha_inicio',
        'fecha_fin',
        'rifas_aplicables',
        'categorias_aplicables',
        'usuarios_aplicables',
        'monto_maximo_descuento',
        'activo',
        'visible_publico',
        'estado',
        'creado_por',
        'notas_admin',
        'campana'
    ];

    protected $casts = [
        'valor_descuento' => 'decimal:2',
        'descuento_maximo' => 'decimal:2',
        'compra_minima' => 'decimal:2',
        'monto_maximo_descuento' => 'decimal:2',
        'usos_maximos' => 'integer',
        'usos_actuales' => 'integer',
        'usos_por_usuario' => 'integer',
        'solo_primera_compra' => 'boolean',
        'activo' => 'boolean',
        'visible_publico' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'rifas_aplicables' => 'json',
        'categorias_aplicables' => 'json',
        'usuarios_aplicables' => 'json'
    ];

    // Relaciones
    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'cupon_descuento', 'codigo');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true)
                    ->where('fecha_inicio', '<=', now())
                    ->where('fecha_fin', '>=', now());
    }

    public function scopeVisiblesPublico($query)
    {
        return $query->where('visible_publico', true);
    }

    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    public function scopePorCampana($query, $campana)
    {
        return $query->where('campana', $campana);
    }

    // Métodos auxiliares
    public function puedeSerUsado($userId = null, $rifaId = null)
    {
        // Verificar si está activo y en fechas válidas
        if (!$this->activo || $this->estado !== 'activo') {
            return false;
        }

        if ($this->fecha_inicio > now() || $this->fecha_fin < now()) {
            return false;
        }

        // Verificar límite de usos globales
        if ($this->usos_maximos && $this->usos_actuales >= $this->usos_maximos) {
            return false;
        }

        // Verificar usuario específico
        if ($userId) {
            // Verificar si está restringido a usuarios específicos
            if ($this->usuarios_aplicables && !in_array($userId, $this->usuarios_aplicables)) {
                return false;
            }

            // Verificar límite de usos por usuario
            $usosUsuario = $this->ventas()->where('user_id', $userId)->count();
            if ($usosUsuario >= $this->usos_por_usuario) {
                return false;
            }

            // Verificar si es solo para primera compra
            if ($this->solo_primera_compra) {
                $user = User::find($userId);
                if ($user && $user->primera_compra !== null) {
                    return false;
                }
            }
        }

        // Verificar rifa específica
        if ($rifaId && $this->rifas_aplicables && !in_array($rifaId, $this->rifas_aplicables)) {
            return false;
        }

        return true;
    }

    public function calcularDescuento($montoBase)
    {
        if ($montoBase < $this->compra_minima) {
            return 0;
        }

        $descuento = 0;

        if ($this->tipo_descuento === 'porcentaje') {
            $descuento = $montoBase * ($this->valor_descuento / 100);
            
            // Aplicar límite máximo si existe
            if ($this->descuento_maximo) {
                $descuento = min($descuento, $this->descuento_maximo);
            }
        } else {
            $descuento = $this->valor_descuento;
        }

        // Aplicar monto máximo de descuento si existe
        if ($this->monto_maximo_descuento) {
            $descuento = min($descuento, $this->monto_maximo_descuento);
        }

        // No puede ser mayor al monto base
        return min($descuento, $montoBase);
    }

    public function incrementarUso()
    {
        $this->increment('usos_actuales');
        
        // Verificar si se agotó
        if ($this->usos_maximos && $this->usos_actuales >= $this->usos_maximos) {
            $this->update(['estado' => 'agotado']);
        }
    }

    public function getUsosRestantesAttribute()
    {
        if (!$this->usos_maximos) {
            return null; // Ilimitado
        }
        
        return max(0, $this->usos_maximos - $this->usos_actuales);
    }

    public function getEstaValidoAttribute()
    {
        return $this->activo && 
               $this->estado === 'activo' && 
               $this->fecha_inicio <= now() && 
               $this->fecha_fin >= now() &&
               (!$this->usos_maximos || $this->usos_actuales < $this->usos_maximos);
    }

    public function getDescripcionDescuentoAttribute()
    {
        if ($this->tipo_descuento === 'porcentaje') {
            return $this->valor_descuento . '% de descuento';
        } else {
            return 'S/ ' . number_format($this->valor_descuento, 2) . ' de descuento';
        }
    }

    public static function generarCodigo($longitud = 8)
    {
        do {
            $codigo = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $longitud));
        } while (self::where('codigo', $codigo)->exists());
        
        return $codigo;
    }
}
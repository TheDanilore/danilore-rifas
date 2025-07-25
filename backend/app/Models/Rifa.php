<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rifa extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'precio_boleto',
        'boletos_minimos',
        'boletos_vendidos',
        'imagen_principal',
        'imagenes_adicionales',
        'media_gallery',
        'fecha_inicio',
        'fecha_fin',
        'fecha_sorteo',
        'estado',
        'tipo',
        'categoria_id',
        'codigo_unico',
        'es_destacada',
        'max_boletos_por_persona',
        'terminos_condiciones',
        'orden',
        'rifa_requerida_id',
        'notas_admin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_sorteo' => 'datetime',
        'precio_boleto' => 'decimal:2',
        'imagenes_adicionales' => 'json',
        'media_gallery' => 'json',
        'es_destacada' => 'boolean',
        'boletos_vendidos' => 'integer',
        'boletos_minimos' => 'integer',
        'max_boletos_por_persona' => 'integer',
        'orden' => 'integer'
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function premios(): HasMany
    {
        return $this->hasMany(Premio::class);
    }

    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function rifaRequerida(): BelongsTo
    {
        return $this->belongsTo(Rifa::class, 'rifa_requerida_id');
    }

    public function rifasDependientes(): HasMany
    {
        return $this->hasMany(Rifa::class, 'rifa_requerida_id');
    }

    // Scopes
    public function scopeActuales($query)
    {
        return $query->where('tipo', 'actual');
    }

    public function scopeFuturas($query)
    {
        return $query->where('tipo', 'futura')->orderBy('orden');
    }

    public function scopeEnVenta($query)
    {
        return $query->where('estado', 'en_venta');
    }

    public function scopeDestacadas($query)
    {
        return $query->where('es_destacada', true);
    }

    public function scopePorCategoria($query, $categoriaId)
    {
        return $query->where('categoria_id', $categoriaId);
    }

    // Métodos auxiliares
    public function getPorcentajeVendidoAttribute()
    {
        if ($this->boletos_minimos == 0) return 0;
        return round(($this->boletos_vendidos / $this->boletos_minimos) * 100, 2);
    }

    public function getBoletosDisponiblesAttribute()
    {
        return max(0, $this->boletos_minimos - $this->boletos_vendidos);
    }

    public function getEstaConfirmadaAttribute()
    {
        return $this->boletos_vendidos >= $this->boletos_minimos;
    }

    public function getPuedeEjecutarseAttribute()
    {
        if ($this->rifa_requerida_id) {
            $rifaRequerida = $this->rifaRequerida;
            return $rifaRequerida && $rifaRequerida->estado === 'confirmada';
        }
        return true;
    }

    // Generar código único
    public static function generarCodigoUnico()
    {
        do {
            $codigo = 'RF' . strtoupper(substr(md5(time() . rand()), 0, 8));
        } while (self::where('codigo_unico', $codigo)->exists());
        
        return $codigo;
    }
}

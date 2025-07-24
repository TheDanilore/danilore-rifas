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
        'total_boletos',
        'boletos_vendidos',
        'imagen_principal',
        'imagenes_adicionales',
        'premio_valor',
        'premio_descripcion',
        'fecha_inicio',
        'fecha_fin',
        'fecha_sorteo',
        'estado',
        'categoria_id',
        'codigo_unico',
        'es_destacada',
        'max_boletos_por_persona',
        'terminos_condiciones',
        'ganador_user_id',
        'numero_ganador',
        'notas_admin'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_sorteo' => 'datetime',
        'precio_boleto' => 'decimal:2',
        'premio_valor' => 'decimal:2',
        'imagenes_adicionales' => 'json',
        'es_destacada' => 'boolean',
        'boletos_vendidos' => 'integer',
        'total_boletos' => 'integer',
        'max_boletos_por_persona' => 'integer'
    ];

    // Relaciones
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function ganador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ganador_user_id');
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa')
                    ->where('fecha_inicio', '<=', now())
                    ->where('fecha_fin', '>=', now());
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
        if ($this->total_boletos == 0) return 0;
        return round(($this->boletos_vendidos / $this->total_boletos) * 100, 2);
    }

    public function getBoletosDisponiblesAttribute()
    {
        return $this->total_boletos - $this->boletos_vendidos;
    }

    public function getEstadoActivaAttribute()
    {
        return $this->estado === 'activa' && 
               $this->fecha_inicio <= now() && 
               $this->fecha_fin >= now();
    }

    public function getEstaFinalizadaAttribute()
    {
        return $this->estado === 'finalizada' || $this->fecha_fin < now();
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

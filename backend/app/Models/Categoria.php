<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'icono',
        'color',
        'activa',
        'orden'
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden' => 'integer'
    ];

    // Relaciones
    public function rifas(): HasMany
    {
        return $this->hasMany(Rifa::class);
    }

    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden');
    }

    // Métodos auxiliares
    public function getRifasActivasCount()
    {
        return $this->rifas()->enVenta()->count();
    }
}

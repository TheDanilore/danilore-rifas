<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telefono',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'ciudad',
        'departamento',
        'codigo_postal',
        'rol',
        'activo',
        'ultimo_acceso',
        'avatar',
        'notif_email',
        'notif_sms',
        'notif_whatsapp',
        'total_boletos_comprados',
        'total_gastado',
        'rifas_ganadas',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'fecha_nacimiento' => 'date',
            'ultimo_acceso' => 'datetime',
            'activo' => 'boolean',
            'notif_email' => 'boolean',
            'notif_sms' => 'boolean',
            'notif_whatsapp' => 'boolean',
            'total_boletos_comprados' => 'integer',
            'total_gastado' => 'decimal:2',
            'rifas_ganadas' => 'integer',
        ];
    }

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function boletos()
    {
        return $this->hasMany(Boleto::class);
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class);
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('rol', 'admin');
    }

    public function scopeUsuarios($query)
    {
        return $query->where('rol', 'usuario');
    }
}

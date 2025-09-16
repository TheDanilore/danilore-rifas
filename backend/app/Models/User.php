<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        // Información personal y contacto
        'telefono',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'genero',
        // Dirección completa
        'direccion',
        'ciudad',
        'departamento',
        'codigo_postal',
        'pais',
        // Sistema de usuarios y permisos
        'rol',
        'activo',
        'verificado',
        'ultimo_acceso',
        'avatar',
        'zona_horaria',
        // Preferencias de notificaciones
        'preferencias_notificacion',
        // Estadísticas del usuario
        'total_boletos_comprados',
        'total_gastado',
        'total_rifas_participadas',
        'rifas_ganadas',
        'primera_compra',
        'ultima_compra',
        // Configuración de seguridad
        'doble_autenticacion',
        'intentos_login_fallidos',
        'bloqueado_hasta',
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
            'verificado' => 'boolean',
            'preferencias_notificacion' => 'json',
            'total_boletos_comprados' => 'integer',
            'total_gastado' => 'decimal:2',
            'total_rifas_participadas' => 'integer',
            'rifas_ganadas' => 'integer',
            'primera_compra' => 'datetime',
            'ultima_compra' => 'datetime',
            'doble_autenticacion' => 'boolean',
            'intentos_login_fallidos' => 'integer',
            'bloqueado_hasta' => 'datetime',
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

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class);
    }

    public function favoritos(): HasMany
    {
        return $this->hasMany(Favorito::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function sorteosEjecutados(): HasMany
    {
        return $this->hasMany(Sorteo::class, 'ejecutado_por');
    }

    public function sorteosValidados(): HasMany
    {
        return $this->hasMany(Sorteo::class, 'validado_por');
    }

    public function reportesGenerados(): HasMany
    {
        return $this->hasMany(Reporte::class, 'generado_por');
    }

    public function configuracionesModificadas(): HasMany
    {
        return $this->hasMany(Configuracion::class, 'modificado_por');
    }

    public function cuponesCreados(): HasMany
    {
        return $this->hasMany(Cupon::class, 'creado_por');
    }

    public function pagosVerificados()
    {
        return $this->hasMany(Pago::class, 'verificado_por');
    }

    public function rifasCreadas()
    {
        return $this->hasMany(Rifa::class, 'creado_por');
    }

    public function boletosTransferidos()
    {
        return $this->hasMany(Boleto::class, 'transferido_a');
    }

    public function boletosQueTransfirio()
    {
        return $this->hasMany(Boleto::class, 'transferido_por');
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeVerificados($query)
    {
        return $query->where('verificado', true);
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('rol', ['admin', 'super_admin', 'moderador']);
    }

    public function scopeUsuarios($query)
    {
        return $query->where('rol', 'usuario');
    }

    public function scopeBloqueados($query)
    {
        return $query->where('bloqueado_hasta', '>', now());
    }

    // Métodos auxiliares
    public function estaBloqueado()
    {
        return $this->bloqueado_hasta && $this->bloqueado_hasta > now();
    }

    public function puedeIniciarSesion()
    {
        return $this->activo && !$this->estaBloqueado();
    }

    public function bloquearUsuario($minutos = 30)
    {
        $this->update([
            'bloqueado_hasta' => now()->addMinutes($minutos)
        ]);
    }

    public function incrementarIntentosFallidos()
    {
        $this->increment('intentos_login_fallidos');
        
        $maxIntentos = config('auth.max_login_attempts', 5);
        if ($this->intentos_login_fallidos >= $maxIntentos) {
            $this->bloquearUsuario();
        }
    }

    public function reiniciarIntentosFallidos()
    {
        $this->update([
            'intentos_login_fallidos' => 0,
            'bloqueado_hasta' => null
        ]);
    }

    public function actualizarUltimoAcceso()
    {
        $this->update(['ultimo_acceso' => now()]);
    }

    public function actualizarEstadisticas($boletos = 0, $monto = 0, $primeraCompra = false)
    {
        $this->increment('total_boletos_comprados', $boletos);
        $this->increment('total_gastado', $monto);
        
        if ($primeraCompra && !$this->primera_compra) {
            $this->primera_compra = now();
        }
        
        $this->ultima_compra = now();
        $this->save();
    }

    public function esAdmin()
    {
        return in_array($this->rol, ['admin', 'super_admin', 'moderador']);
    }

    public function esSuperAdmin()
    {
        return $this->rol === 'super_admin';
    }

    public function getPreferenciasNotificacion()
    {
        return $this->preferencias_notificacion ?? [
            'email' => true,
            'sms' => false,
            'whatsapp' => true,
            'push' => true,
            'marketing' => false
        ];
    }

    public function actualizarPreferenciasNotificacion($preferencias)
    {
        $this->preferencias_notificacion = array_merge(
            $this->getPreferenciasNotificacion(),
            $preferencias
        );
        $this->save();
    }
}

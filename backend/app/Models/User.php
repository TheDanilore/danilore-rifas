<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Información personal básica
        'nombre',
        'apellido',
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
        // Sistema de usuarios y permisos (roles gestionados por Spatie Permission)
        'activo',
        'verificado',
        'ultimo_acceso',
        'avatar',
        'zona_horaria',
        // Preferencias de notificaciones (para configuraciones flexibles)
        'preferencias_notificacion', // JSON: email_rifas, push_promociones, sms_enabled, etc.
        // Términos legales (campos separados para compliance y auditoría)
        'acepta_terminos',           // Boolean: REQUERIDO para registro
        'fecha_aceptacion_terminos', // DateTime: cuándo aceptó términos
        'acepta_marketing',          // Boolean: opcional para promociones
        'fecha_aceptacion_marketing', // DateTime: cuándo aceptó marketing
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
        // Nuevos campos mejorados
        'ip_registro',
        'ip_ultimo_acceso',
        'dispositivo_preferido',
        'user_agent_ultimo_acceso',
        'configuracion_notificaciones',
        'idioma',
        'tema_preferido',
        'perfil_adicional',
        'configuracion_privacidad',
        'ultimo_intento_fallido',
        'cuenta_bloqueada',
        'motivo_bloqueo',
        'bloqueado_por',
        'configuracion_2fa',
        'dos_factores_habilitado',
        'codigos_recuperacion_2fa',
        'preferencias_marketing',
        'version_terminos_aceptada',
        'configuracion_cuenta',
        'es_cuenta_demo',
        'demo_expira_en',
        'origen_registro',
        'codigo_referido_usado',
        'codigo_referido_personal',
        'estadisticas_usuario'
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
            // Casts para términos y marketing
            'acepta_terminos' => 'boolean',
            'fecha_aceptacion_terminos' => 'datetime',
            'acepta_marketing' => 'boolean', 
            'fecha_aceptacion_marketing' => 'datetime',
            // Estadísticas
            'total_boletos_comprados' => 'integer',
            'total_gastado' => 'decimal:2',
            'total_rifas_participadas' => 'integer',
            'rifas_ganadas' => 'integer',
            'primera_compra' => 'datetime',
            'ultima_compra' => 'datetime',
            'doble_autenticacion' => 'boolean',
            'intentos_login_fallidos' => 'integer',
            'bloqueado_hasta' => 'datetime',
            // Nuevos casts
            'configuracion_notificaciones' => 'json',
            'perfil_adicional' => 'json',
            'configuracion_privacidad' => 'json',
            'ultimo_intento_fallido' => 'datetime',
            'cuenta_bloqueada' => 'boolean',
            'configuracion_2fa' => 'json',
            'dos_factores_habilitado' => 'boolean',
            'preferencias_marketing' => 'json',
            'fecha_aceptacion_terminos' => 'datetime',
            'configuracion_cuenta' => 'json',
            'es_cuenta_demo' => 'boolean',
            'demo_expira_en' => 'datetime',
            'estadisticas_usuario' => 'json'
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

    public function usuariosBloqueados(): HasMany
    {
        return $this->hasMany(User::class, 'bloqueado_por');
    }

    public function bloqueadoPor()
    {
        return $this->belongsTo(User::class, 'bloqueado_por');
    }

    public function reportesResueltos(): HasMany
    {
        return $this->hasMany(Reporte::class, 'resuelto_por');
    }

    public function cuponesCreados(): HasMany
    {
        return $this->hasMany(Cupon::class, 'creado_por');
    }

    public function pagosVerificados(): HasMany
    {
        return $this->hasMany(Pago::class, 'verificado_por');
    }

    public function rifasCreadas(): HasMany
    {
        return $this->hasMany(Rifa::class, 'creado_por');
    }

    public function boletosTransferidos(): HasMany
    {
        return $this->hasMany(Boleto::class, 'transferido_a');
    }

    public function boletosQueTransfirio(): HasMany
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
        return $query->whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'super_admin', 'moderador']);
        });
    }

    public function scopeUsuarios($query)
    {
        return $query->whereHas('roles', function($q) {
            $q->where('name', 'usuario');
        });
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
        return $this->hasAnyRole(['admin', 'super_admin', 'moderador']);
    }

    public function esSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }

    public function esModeradorOSuperior()
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'moderador']);
    }

    public function puedeAccederAdmin()
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    public function tienePermisosPara($permiso)
    {
        return $this->can($permiso);
    }

    // Método para asignar rol por defecto a usuarios nuevos
    public function asignarRolPorDefecto()
    {
        if (!$this->roles()->exists()) {
            $this->assignRole('usuario');
        }
    }

    // Método para obtener el rol principal del usuario
    public function getRolPrincipalAttribute()
    {
        $roles = $this->getRoleNames();
        
        if ($roles->contains('super_admin')) return 'super_admin';
        if ($roles->contains('admin')) return 'admin';
        if ($roles->contains('moderador')) return 'moderador';
        
        return 'usuario';
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

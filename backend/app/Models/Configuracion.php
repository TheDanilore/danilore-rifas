<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'clave',
        'valor',
        'tipo',
        'grupo',
        'categoria',
        'descripcion',
        'valor_defecto',
        'editable',
        'visible_admin',
        'requiere_reinicio',
        'validaciones',
        'orden',
        'modificado_por',
        'fecha_modificacion',
        'comentario_modificacion',
        'activa',
        'icono',
        'ayuda',
        'dependencias',
        'tipo_input',
        'opciones',
        'es_visible',
        'es_sistema'
    ];

    protected $casts = [
        'editable' => 'boolean',
        'visible_admin' => 'boolean',
        'requiere_reinicio' => 'boolean',
        'validaciones' => 'json',
        'orden' => 'integer',
        'fecha_modificacion' => 'datetime',
        'activa' => 'boolean',
        'dependencias' => 'json',
        'opciones' => 'json',
        'es_visible' => 'boolean',
        'es_sistema' => 'boolean'
    ];

    // Relaciones
    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }

    // Scopes
    public function scopeVisiblesAdmin($query)
    {
        return $query->where('visible_admin', true);
    }

    public function scopeEditables($query)
    {
        return $query->where('editable', true);
    }

    public function scopePorCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    public function scopePorGrupo($query, $grupo)
    {
        return $query->where('grupo', $grupo);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeOrdenadas($query)
    {
        return $query->orderBy('categoria')->orderBy('orden');
    }

    // Métodos auxiliares
    public function actualizar($nuevoValor, $usuarioModificador = null)
    {
        if (!$this->editable) {
            throw new \Exception('Esta configuración no es editable');
        }

        // Validar el nuevo valor
        if (!$this->validarValor($nuevoValor)) {
            throw new \Exception('El valor proporcionado no es válido para esta configuración');
        }

        $this->update([
            'valor' => $nuevoValor,
            'modificado_por' => $usuarioModificador,
            'fecha_modificacion' => now()
        ]);

        return $this;
    }

    public function validarValor($valor)
    {
        // Validar según el tipo
        switch ($this->tipo) {
            case 'boolean':
                return is_bool($valor) || in_array($valor, [0, 1, '0', '1', 'true', 'false']);
            
            case 'integer':
                return is_numeric($valor) && is_int((int)$valor);
            
            case 'decimal':
                return is_numeric($valor);
            
            case 'string':
                return is_string($valor);
            
            case 'json':
                return is_array($valor) || is_object($valor);
            
            case 'enum':
                return in_array($valor, $this->opciones_validas ?? []);
            
            case 'email':
                return filter_var($valor, FILTER_VALIDATE_EMAIL) !== false;
            
            case 'url':
                return filter_var($valor, FILTER_VALIDATE_URL) !== false;
        }

        // Validar con regex si está configurado
        if ($this->validacion_regex) {
            return preg_match($this->validacion_regex, $valor);
        }

        return true;
    }

    public function getValorFormateado()
    {
        switch ($this->tipo) {
            case 'boolean':
                return $this->valor ? 'Sí' : 'No';
            
            case 'decimal':
                return number_format($this->valor, 2);
            
            case 'json':
                return json_encode($this->valor, JSON_PRETTY_PRINT);
            
            default:
                return $this->valor;
        }
    }

    public function restaurarDefecto($usuarioModificador = null)
    {
        if (!$this->es_editable) {
            throw new \Exception('Esta configuración no es editable');
        }

        $this->update([
            'valor' => $this->valor_defecto,
            'modificado_por' => $usuarioModificador,
            'fecha_modificacion' => now()
        ]);

        return $this;
    }

    // Métodos estáticos para gestión de configuraciones
    public static function obtener($clave, $defecto = null)
    {
        $config = self::where('clave', $clave)->first();
        
        if (!$config) {
            return $defecto;
        }

        return $config->valor;
    }

    public static function establecer($clave, $valor, $usuarioModificador = null)
    {
        $config = self::where('clave', $clave)->first();
        
        if (!$config) {
            throw new \Exception("La configuración '{$clave}' no existe");
        }

        return $config->actualizar($valor, $usuarioModificador);
    }

    public static function obtenerPublicas()
    {
        return self::publicas()
                  ->ordenadas()
                  ->get()
                  ->groupBy('categoria');
    }

    public static function obtenerPorCategoria($categoria)
    {
        return self::porCategoria($categoria)
                  ->ordenadas()
                  ->get()
                  ->keyBy('clave');
    }

    public static function inicializarConfiguraciones()
    {
        $configuraciones = [
            // Configuraciones generales
            [
                'clave' => 'sitio_nombre',
                'valor' => 'Danilore Rifas',
                'tipo' => 'string',
                'categoria' => 'general',
                'descripcion' => 'Nombre del sitio web',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 1
            ],
            [
                'clave' => 'sitio_descripcion',
                'valor' => 'Sistema de rifas progresivas',
                'tipo' => 'string',
                'categoria' => 'general',
                'descripcion' => 'Descripción del sitio web',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 2
            ],
            [
                'clave' => 'sitio_email',
                'valor' => 'contacto@danilorerifas.com',
                'tipo' => 'email',
                'categoria' => 'general',
                'descripcion' => 'Email de contacto principal',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 3
            ],
            [
                'clave' => 'sitio_telefono',
                'valor' => '+51 999 999 999',
                'tipo' => 'string',
                'categoria' => 'general',
                'descripcion' => 'Teléfono de contacto',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 4
            ],
            
            // Configuraciones de rifas
            [
                'clave' => 'rifas_max_boletos_usuario',
                'valor' => 100,
                'tipo' => 'integer',
                'categoria' => 'rifas',
                'descripcion' => 'Máximo de boletos que puede comprar un usuario por rifa',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 1
            ],
            [
                'clave' => 'rifas_tiempo_reserva',
                'valor' => 15,
                'tipo' => 'integer',
                'categoria' => 'rifas',
                'descripcion' => 'Tiempo en minutos para mantener boletos reservados',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 2
            ],
            [
                'clave' => 'rifas_comision_plataforma',
                'valor' => 10.0,
                'tipo' => 'decimal',
                'categoria' => 'rifas',
                'descripcion' => 'Porcentaje de comisión de la plataforma',
                'es_publica' => false,
                'es_editable' => true,
                'orden' => 3
            ],
            [
                'clave' => 'rifas_auto_sorteo',
                'valor' => true,
                'tipo' => 'boolean',
                'categoria' => 'rifas',
                'descripcion' => 'Activar sorteo automático al completar boletos',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 4
            ],
            
            // Configuraciones de pagos
            [
                'clave' => 'pagos_yape_activo',
                'valor' => true,
                'tipo' => 'boolean',
                'categoria' => 'pagos',
                'descripcion' => 'Activar pagos con Yape',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 1
            ],
            [
                'clave' => 'pagos_plin_activo',
                'valor' => true,
                'tipo' => 'boolean',
                'categoria' => 'pagos',
                'descripcion' => 'Activar pagos con Plin',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 2
            ],
            [
                'clave' => 'pagos_transferencia_activo',
                'valor' => true,
                'tipo' => 'boolean',
                'categoria' => 'pagos',
                'descripcion' => 'Activar pagos con transferencia bancaria',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 3
            ],
            [
                'clave' => 'pagos_tiempo_confirmacion',
                'valor' => 24,
                'tipo' => 'integer',
                'categoria' => 'pagos',
                'descripcion' => 'Horas para confirmar pago manual',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 4
            ],
            
            // Configuraciones de notificaciones
            [
                'clave' => 'notif_email_activo',
                'valor' => true,
                'tipo' => 'boolean',
                'categoria' => 'notificaciones',
                'descripcion' => 'Activar notificaciones por email',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 1
            ],
            [
                'clave' => 'notif_sms_activo',
                'valor' => false,
                'tipo' => 'boolean',
                'categoria' => 'notificaciones',
                'descripcion' => 'Activar notificaciones por SMS',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 2
            ],
            [
                'clave' => 'notif_whatsapp_activo',
                'valor' => false,
                'tipo' => 'boolean',
                'categoria' => 'notificaciones',
                'descripcion' => 'Activar notificaciones por WhatsApp',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 3
            ],
            
            // Configuraciones del sistema progresivo
            [
                'clave' => 'progresivo_activo',
                'valor' => true,
                'tipo' => 'boolean',
                'categoria' => 'progresivo',
                'descripcion' => 'Activar sistema de premios progresivos',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 1
            ],
            [
                'clave' => 'progresivo_niveles_max',
                'valor' => 10,
                'tipo' => 'integer',
                'categoria' => 'progresivo',
                'descripcion' => 'Máximo número de niveles progresivos',
                'es_publica' => true,
                'es_editable' => true,
                'orden' => 2
            ],
            
            // Configuraciones de seguridad
            [
                'clave' => 'seguridad_intentos_login',
                'valor' => 5,
                'tipo' => 'integer',
                'categoria' => 'seguridad',
                'descripcion' => 'Máximo intentos de login antes de bloqueo',
                'es_publica' => false,
                'es_editable' => true,
                'orden' => 1
            ],
            [
                'clave' => 'seguridad_tiempo_bloqueo',
                'valor' => 30,
                'tipo' => 'integer',
                'categoria' => 'seguridad',
                'descripcion' => 'Minutos de bloqueo tras intentos fallidos',
                'es_publica' => false,
                'es_editable' => true,
                'orden' => 2
            ]
        ];

        foreach ($configuraciones as $config) {
            self::updateOrCreate(
                ['clave' => $config['clave']],
                array_merge($config, [
                    'valor_defecto' => $config['valor'],
                    'fecha_modificacion' => now()
                ])
            );
        }
    }

    public static function exportarConfiguraciones()
    {
        return self::all()
                  ->map(function($config) {
                      return [
                          'clave' => $config->clave,
                          'valor' => $config->valor,
                          'tipo' => $config->tipo,
                          'categoria' => $config->categoria,
                          'descripcion' => $config->descripcion
                      ];
                  })
                  ->groupBy('categoria');
    }

    public static function importarConfiguraciones($configuraciones, $usuarioModificador = null)
    {
        $resultados = [];

        foreach ($configuraciones as $clave => $valor) {
            try {
                self::establecer($clave, $valor, $usuarioModificador);
                $resultados[$clave] = 'éxito';
            } catch (\Exception $e) {
                $resultados[$clave] = 'error: ' . $e->getMessage();
            }
        }

        return $resultados;
    }

    // Cache de configuraciones para mejorar rendimiento
    public static function obtenerCache()
    {
        return cache()->remember('configuraciones_sistema', 3600, function() {
            return self::all()->keyBy('clave');
        });
    }

    public static function limpiarCache()
    {
        cache()->forget('configuraciones_sistema');
    }

    // Override del método save para limpiar cache
    public function save(array $options = [])
    {
        $result = parent::save($options);
        self::limpiarCache();
        return $result;
    }

    public function delete()
    {
        $result = parent::delete();
        self::limpiarCache();
        return $result;
    }
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar configuraciones iniciales del sistema
        $configuraciones = [
            // Configuraciones generales
            [
                'clave' => 'sistema.nombre',
                'valor' => 'Danilore Rifas',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'basico',
                'descripcion' => 'Nombre de la plataforma',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'sistema.descripcion',
                'valor' => 'Plataforma de rifas online con premios progresivos',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'basico',
                'descripcion' => 'Descripción corta del sistema',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'sistema.zona_horaria',
                'valor' => 'America/Lima',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'basico',
                'descripcion' => 'Zona horaria del sistema',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],
            
            // Configuraciones de rifas
            [
                'clave' => 'rifas.tiempo_reserva_defecto',
                'valor' => '15',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'reservas',
                'descripcion' => 'Tiempo en minutos para completar el pago de boletos reservados',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 10
            ],
            [
                'clave' => 'rifas.max_boletos_por_usuario',
                'valor' => '10',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'limites',
                'descripcion' => 'Máximo de boletos que puede comprar un usuario por rifa',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 11
            ],
            [
                'clave' => 'rifas.max_boletos_por_transaccion',
                'valor' => '5',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'limites',
                'descripcion' => 'Máximo de boletos por transacción',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 12
            ],
            
            // Configuraciones de pagos
            [
                'clave' => 'pagos.comision_defecto',
                'valor' => '10.00',
                'tipo' => 'number',
                'grupo' => 'pagos',
                'categoria' => 'comisiones',
                'descripcion' => 'Comisión por defecto de la plataforma (porcentaje)',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 20
            ],
            [
                'clave' => 'pagos.yape_habilitado',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'pagos',
                'categoria' => 'metodos',
                'descripcion' => 'Habilitar pagos con Yape',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 21
            ],
            [
                'clave' => 'pagos.plin_habilitado',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'pagos',
                'categoria' => 'metodos',
                'descripcion' => 'Habilitar pagos con Plin',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 22
            ],
            
            // Configuraciones de notificaciones
            [
                'clave' => 'notificaciones.email_habilitado',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'notificaciones',
                'categoria' => 'canales',
                'descripcion' => 'Habilitar notificaciones por email',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 30
            ],
            [
                'clave' => 'notificaciones.sms_habilitado',
                'valor' => 'false',
                'tipo' => 'boolean',
                'grupo' => 'notificaciones',
                'categoria' => 'canales',
                'descripcion' => 'Habilitar notificaciones por SMS',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 31
            ],
            
            // Configuraciones de seguridad
            [
                'clave' => 'seguridad.max_intentos_login',
                'valor' => '5',
                'tipo' => 'number',
                'grupo' => 'seguridad',
                'categoria' => 'acceso',
                'descripcion' => 'Máximo de intentos de login fallidos antes de bloquear',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 40
            ],
            [
                'clave' => 'seguridad.tiempo_bloqueo_minutos',
                'valor' => '30',
                'tipo' => 'number',
                'grupo' => 'seguridad',
                'categoria' => 'acceso',
                'descripcion' => 'Tiempo de bloqueo en minutos después de superar intentos fallidos',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 41
            ]
        ];
        
        foreach ($configuraciones as $config) {
            DB::table('configuraciones')->insert(array_merge($config, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
        
        // Insertar categorías iniciales
        $categorias = [
            [
                'nombre' => 'Electrónicos',
                'slug' => 'electronicos',
                'descripcion' => 'Dispositivos electrónicos, smartphones, laptops, tablets',
                'icono' => 'fas fa-laptop',
                'color' => '#3B82F6',
                'activa' => true,
                'orden' => 1
            ],
            [
                'nombre' => 'Gaming',
                'slug' => 'gaming',
                'descripcion' => 'Consolas, videojuegos, accesorios gaming',
                'icono' => 'fas fa-gamepad',
                'color' => '#8B5CF6',
                'activa' => true,
                'orden' => 2
            ],
            [
                'nombre' => 'Automóviles',
                'slug' => 'automoviles',
                'descripcion' => 'Vehículos, motocicletas, accesorios automotrices',
                'icono' => 'fas fa-car',
                'color' => '#EF4444',
                'activa' => true,
                'orden' => 3
            ],
            [
                'nombre' => 'Electrodomésticos',
                'slug' => 'electrodomesticos',
                'descripcion' => 'Electrodomésticos para el hogar',
                'icono' => 'fas fa-blender',
                'color' => '#10B981',
                'activa' => true,
                'orden' => 4
            ],
            [
                'nombre' => 'Dinero en Efectivo',
                'slug' => 'dinero-efectivo',
                'descripcion' => 'Premios en efectivo',
                'icono' => 'fas fa-money-bill-wave',
                'color' => '#F59E0B',
                'activa' => true,
                'orden' => 5
            ]
        ];
        
        foreach ($categorias as $categoria) {
            DB::table('categorias')->insert(array_merge($categoria, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar configuraciones insertadas
        DB::table('configuraciones')->whereIn('clave', [
            'sistema.nombre',
            'sistema.descripcion',
            'sistema.zona_horaria',
            'rifas.tiempo_reserva_defecto',
            'rifas.max_boletos_por_usuario',
            'rifas.max_boletos_por_transaccion',
            'pagos.comision_defecto',
            'pagos.yape_habilitado',
            'pagos.plin_habilitado',
            'notificaciones.email_habilitado',
            'notificaciones.sms_habilitado',
            'seguridad.max_intentos_login',
            'seguridad.tiempo_bloqueo_minutos'
        ])->delete();
        
        // Eliminar categorías insertadas
        DB::table('categorias')->whereIn('slug', [
            'electronicos',
            'gaming',
            'automoviles',
            'electrodomesticos',
            'dinero-efectivo'
        ])->delete();
    }
};
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuraciones básicas del sistema
        $configuraciones = [
            // Configuraciones generales
            [
                'clave' => 'app.nombre',
                'valor' => 'Danilore Rifas',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'basico',
                'descripcion' => 'Nombre de la aplicación',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'app.descripcion',
                'valor' => 'Plataforma de rifas online con premios progresivos',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'basico',
                'descripcion' => 'Descripción de la aplicación',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'app.email_contacto',
                'valor' => 'contacto@danilorerifas.com',
                'tipo' => 'email',
                'grupo' => 'general',
                'categoria' => 'contacto',
                'descripcion' => 'Email de contacto principal',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],
            [
                'clave' => 'app.telefono_contacto',
                'valor' => '+51 999 888 777',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'contacto',
                'descripcion' => 'Teléfono de contacto principal',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 4
            ],
            [
                'clave' => 'app.direccion',
                'valor' => 'Lima, Perú',
                'tipo' => 'string',
                'grupo' => 'general',
                'categoria' => 'contacto',
                'descripcion' => 'Dirección de la empresa',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 5
            ],

            // Configuraciones de rifas
            [
                'clave' => 'rifas.comision_porcentaje',
                'valor' => '10.0',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'configuracion',
                'descripcion' => 'Porcentaje de comisión por venta',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'rifas.max_boletos_por_compra',
                'valor' => '50',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'limites',
                'descripcion' => 'Máximo de boletos por compra',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'rifas.min_boletos_por_compra',
                'valor' => '1',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'limites',
                'descripcion' => 'Mínimo de boletos por compra',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],
            [
                'clave' => 'rifas.tiempo_reserva_minutos',
                'valor' => '15',
                'tipo' => 'number',
                'grupo' => 'rifas',
                'categoria' => 'tiempos',
                'descripcion' => 'Tiempo en minutos para mantener boletos reservados',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 4
            ],
            [
                'clave' => 'rifas.auto_sorteo',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'rifas',
                'categoria' => 'automatizacion',
                'descripcion' => 'Realizar sorteo automático al completar venta de boletos',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 5
            ],

            // Configuraciones de pagos
            [
                'clave' => 'pagos.metodos_activos',
                'valor' => json_encode(['yape', 'plin', 'transferencia', 'efectivo']),
                'tipo' => 'json',
                'grupo' => 'pagos',
                'categoria' => 'metodos',
                'descripcion' => 'Métodos de pago activos',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'pagos.timeout_minutos',
                'valor' => '30',
                'tipo' => 'number',
                'grupo' => 'pagos',
                'categoria' => 'tiempos',
                'descripcion' => 'Tiempo límite para completar pago (minutos)',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'pagos.confirmacion_manual_horas',
                'valor' => '24',
                'tipo' => 'number',
                'grupo' => 'pagos',
                'categoria' => 'tiempos',
                'descripcion' => 'Horas para confirmar pago manual',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],

            // Configuraciones de Yape
            [
                'clave' => 'yape.numero',
                'valor' => '999888777',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'yape',
                'descripcion' => 'Número de Yape para recibir pagos',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 10
            ],
            [
                'clave' => 'yape.nombre_titular',
                'valor' => 'Danilore Rifas',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'yape',
                'descripcion' => 'Nombre del titular de Yape',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 11
            ],

            // Configuraciones de Plin
            [
                'clave' => 'plin.numero',
                'valor' => '999888777',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'plin',
                'descripcion' => 'Número de Plin para recibir pagos',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 15
            ],
            [
                'clave' => 'plin.nombre_titular',
                'valor' => 'Danilore Rifas',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'plin',
                'descripcion' => 'Nombre del titular de Plin',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 16
            ],

            // Configuraciones de transferencia bancaria
            [
                'clave' => 'banco.nombre',
                'valor' => 'BCP',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'banco',
                'descripcion' => 'Nombre del banco principal',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 20
            ],
            [
                'clave' => 'banco.numero_cuenta',
                'valor' => '123-456789-0-12',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'banco',
                'descripcion' => 'Número de cuenta bancaria',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 21
            ],
            [
                'clave' => 'banco.cci',
                'valor' => '002-123-456789012345-12',
                'tipo' => 'string',
                'grupo' => 'pagos',
                'categoria' => 'banco',
                'descripcion' => 'Código de cuenta interbancaria (CCI)',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 22
            ],

            // Configuraciones de notificaciones
            [
                'clave' => 'notificaciones.email_admin',
                'valor' => 'admin@danilorerifas.com',
                'tipo' => 'email',
                'grupo' => 'notificaciones',
                'categoria' => 'configuracion',
                'descripcion' => 'Email para notificaciones administrativas',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'notificaciones.nueva_venta',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'notificaciones',
                'categoria' => 'eventos',
                'descripcion' => 'Enviar notificación por nueva venta',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'notificaciones.sorteo_completado',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'notificaciones',
                'categoria' => 'eventos',
                'descripcion' => 'Enviar notificación cuando se complete un sorteo',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],
            [
                'clave' => 'notificaciones.nuevo_usuario',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'notificaciones',
                'categoria' => 'eventos',
                'descripcion' => 'Enviar notificación por nuevo usuario registrado',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 4
            ],

            // Configuraciones de redes sociales
            [
                'clave' => 'social.facebook',
                'valor' => 'https://facebook.com/danilorerifas',
                'tipo' => 'url',
                'grupo' => 'social',
                'categoria' => 'redes',
                'descripcion' => 'URL de Facebook',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'social.instagram',
                'valor' => 'https://instagram.com/danilorerifas',
                'tipo' => 'url',
                'grupo' => 'social',
                'categoria' => 'redes',
                'descripcion' => 'URL de Instagram',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'social.whatsapp',
                'valor' => 'https://wa.me/51999888777',
                'tipo' => 'url',
                'grupo' => 'social',
                'categoria' => 'redes',
                'descripcion' => 'URL de WhatsApp',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],
            [
                'clave' => 'social.telegram',
                'valor' => 'https://t.me/danilorerifas',
                'tipo' => 'url',
                'grupo' => 'social',
                'categoria' => 'redes',
                'descripcion' => 'URL de Telegram',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 4
            ],

            // Configuraciones legales
            [
                'clave' => 'legal.terminos_url',
                'valor' => '/terminos-y-condiciones',
                'tipo' => 'url',
                'grupo' => 'legal',
                'categoria' => 'documentos',
                'descripcion' => 'URL de términos y condiciones',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'legal.privacidad_url',
                'valor' => '/politica-de-privacidad',
                'tipo' => 'url',
                'grupo' => 'legal',
                'categoria' => 'documentos',
                'descripcion' => 'URL de política de privacidad',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'legal.cookies_url',
                'valor' => '/politica-de-cookies',
                'tipo' => 'url',
                'grupo' => 'legal',
                'categoria' => 'documentos',
                'descripcion' => 'URL de política de cookies',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],

            // Configuraciones del sistema
            [
                'clave' => 'sistema.mantenimiento_activo',
                'valor' => 'false',
                'tipo' => 'boolean',
                'grupo' => 'sistema',
                'categoria' => 'estado',
                'descripcion' => 'Modo mantenimiento activo',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'sistema.mantenimiento_mensaje',
                'valor' => 'Estamos realizando mantenimiento. Regresa pronto.',
                'tipo' => 'string',
                'grupo' => 'sistema',
                'categoria' => 'estado',
                'descripcion' => 'Mensaje de modo mantenimiento',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'sistema.registro_usuarios_activo',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'sistema',
                'categoria' => 'usuarios',
                'descripcion' => 'Permitir registro de nuevos usuarios',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],
            [
                'clave' => 'sistema.verificacion_email_obligatoria',
                'valor' => 'true',
                'tipo' => 'boolean',
                'grupo' => 'sistema',
                'categoria' => 'usuarios',
                'descripcion' => 'Requerir verificación de email para nuevos usuarios',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 4
            ],

            // Configuraciones de seguridad
            [
                'clave' => 'seguridad.max_intentos_login',
                'valor' => '5',
                'tipo' => 'number',
                'grupo' => 'seguridad',
                'categoria' => 'acceso',
                'descripcion' => 'Máximo intentos de login antes de bloqueo',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
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
                'orden' => 2
            ],
            [
                'clave' => 'seguridad.session_timeout_minutos',
                'valor' => '120',
                'tipo' => 'number',
                'grupo' => 'seguridad',
                'categoria' => 'sesiones',
                'descripcion' => 'Tiempo de expiración de sesión en minutos',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ],

            // Configuraciones de SEO
            [
                'clave' => 'seo.meta_titulo',
                'valor' => 'Danilore Rifas - Rifas Online con Premios Progresivos',
                'tipo' => 'string',
                'grupo' => 'seo',
                'categoria' => 'meta',
                'descripcion' => 'Título meta principal del sitio',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 1
            ],
            [
                'clave' => 'seo.meta_descripcion',
                'valor' => 'Participa en rifas online con premios progresivos. Compra boletos, gana premios increíbles y disfruta de la emoción del sorteo.',
                'tipo' => 'string',
                'grupo' => 'seo',
                'categoria' => 'meta',
                'descripcion' => 'Descripción meta principal del sitio',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 2
            ],
            [
                'clave' => 'seo.meta_keywords',
                'valor' => 'rifas online, premios progresivos, sorteos, boletos, premios',
                'tipo' => 'string',
                'grupo' => 'seo',
                'categoria' => 'meta',
                'descripcion' => 'Palabras clave meta del sitio',
                'editable' => true,
                'visible_admin' => true,
                'orden' => 3
            ]
        ];

        foreach ($configuraciones as $config) {
            DB::table('configuraciones')->updateOrInsert(
                ['clave' => $config['clave']],
                array_merge($config, [
                    'valor_defecto' => $config['valor'],
                    'requiere_reinicio' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('✓ ConfiguracionSeeder: ' . count($configuraciones) . ' configuraciones del sistema creadas correctamente.');
    }
}
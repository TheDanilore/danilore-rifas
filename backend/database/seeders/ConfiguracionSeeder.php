<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configuraciones básicas de la aplicación
        $configuraciones = [
            [
                'clave' => 'app.nombre',
                'valor' => 'Danilore Rifas',
                'descripcion' => 'Nombre de la aplicación',
                'tipo' => 'texto',
                'categoria' => 'general'
            ],
            [
                'clave' => 'app.descripcion',
                'valor' => 'Plataforma de rifas online',
                'descripcion' => 'Descripción de la aplicación',
                'tipo' => 'texto',
                'categoria' => 'general'
            ],
            [
                'clave' => 'app.email_contacto',
                'valor' => 'contacto@danilore.com',
                'descripcion' => 'Email de contacto principal',
                'tipo' => 'email',
                'categoria' => 'contacto'
            ],
            [
                'clave' => 'app.telefono_contacto',
                'valor' => '+51 999 888 777',
                'descripcion' => 'Teléfono de contacto principal',
                'tipo' => 'telefono',
                'categoria' => 'contacto'
            ],
            [
                'clave' => 'rifas.comision_porcentaje',
                'valor' => '10',
                'descripcion' => 'Porcentaje de comisión por venta',
                'tipo' => 'numero',
                'categoria' => 'rifas'
            ],
            [
                'clave' => 'rifas.max_boletos_por_compra',
                'valor' => '50',
                'descripcion' => 'Máximo de boletos por compra',
                'tipo' => 'numero',
                'categoria' => 'rifas'
            ],
            [
                'clave' => 'rifas.min_boletos_por_compra',
                'valor' => '1',
                'descripcion' => 'Mínimo de boletos por compra',
                'tipo' => 'numero',
                'categoria' => 'rifas'
            ],
            [
                'clave' => 'rifas.auto_sorteo',
                'valor' => 'true',
                'descripcion' => 'Realizar sorteo automático al completar venta de boletos',
                'tipo' => 'booleano',
                'categoria' => 'rifas'
            ],
            [
                'clave' => 'pagos.metodos_activos',
                'valor' => json_encode(['yape', 'plin', 'transferencia', 'efectivo']),
                'descripcion' => 'Métodos de pago activos',
                'tipo' => 'json',
                'categoria' => 'pagos'
            ],
            [
                'clave' => 'pagos.timeout_minutos',
                'valor' => '30',
                'descripcion' => 'Tiempo límite para completar pago (minutos)',
                'tipo' => 'numero',
                'categoria' => 'pagos'
            ],
            [
                'clave' => 'yape.numero',
                'valor' => '999888777',
                'descripcion' => 'Número de Yape para recibir pagos',
                'tipo' => 'texto',
                'categoria' => 'pagos'
            ],
            [
                'clave' => 'yape.nombre',
                'valor' => 'Danilore Rifas',
                'descripcion' => 'Nombre del titular de Yape',
                'tipo' => 'texto',
                'categoria' => 'pagos'
            ],
            [
                'clave' => 'plin.numero',
                'valor' => '999888777',
                'descripcion' => 'Número de Plin para recibir pagos',
                'tipo' => 'texto',
                'categoria' => 'pagos'
            ],
            [
                'clave' => 'notificaciones.email_admin',
                'valor' => 'admin@danilore.com',
                'descripcion' => 'Email para notificaciones administrativas',
                'tipo' => 'email',
                'categoria' => 'notificaciones'
            ],
            [
                'clave' => 'notificaciones.nueva_venta',
                'valor' => 'true',
                'descripcion' => 'Enviar notificación por nueva venta',
                'tipo' => 'booleano',
                'categoria' => 'notificaciones'
            ],
            [
                'clave' => 'notificaciones.sorteo_completado',
                'valor' => 'true',
                'descripcion' => 'Enviar notificación cuando se complete un sorteo',
                'tipo' => 'booleano',
                'categoria' => 'notificaciones'
            ],
            [
                'clave' => 'social.facebook',
                'valor' => 'https://facebook.com/danilorerifas',
                'descripcion' => 'URL de Facebook',
                'tipo' => 'url',
                'categoria' => 'social'
            ],
            [
                'clave' => 'social.instagram',
                'valor' => 'https://instagram.com/danilorerifas',
                'descripcion' => 'URL de Instagram',
                'tipo' => 'url',
                'categoria' => 'social'
            ],
            [
                'clave' => 'social.whatsapp',
                'valor' => 'https://wa.me/51999888777',
                'descripcion' => 'URL de WhatsApp',
                'tipo' => 'url',
                'categoria' => 'social'
            ],
            [
                'clave' => 'terminos.url',
                'valor' => '/terminos-y-condiciones',
                'descripcion' => 'URL de términos y condiciones',
                'tipo' => 'url',
                'categoria' => 'legal'
            ],
            [
                'clave' => 'privacidad.url',
                'valor' => '/politica-de-privacidad',
                'descripcion' => 'URL de política de privacidad',
                'tipo' => 'url',
                'categoria' => 'legal'
            ],
            [
                'clave' => 'mantenimiento.activo',
                'valor' => 'false',
                'descripcion' => 'Modo mantenimiento activo',
                'tipo' => 'booleano',
                'categoria' => 'sistema'
            ],
            [
                'clave' => 'mantenimiento.mensaje',
                'valor' => 'Estamos realizando mantenimiento. Regresa pronto.',
                'descripcion' => 'Mensaje de modo mantenimiento',
                'tipo' => 'texto',
                'categoria' => 'sistema'
            ]
        ];

        foreach ($configuraciones as $config) {
            DB::table('configuraciones')->updateOrInsert(
                ['clave' => $config['clave']],
                array_merge($config, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }

        $this->command->info('Configuraciones creadas correctamente.');
    }
}

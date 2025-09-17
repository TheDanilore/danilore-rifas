<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Último acceso del usuario
            $table->timestamp('ultimo_acceso')->nullable()->after('email_verified_at');
            
            // IP de registro
            $table->string('ip_registro', 45)->nullable()->after('ultimo_acceso');
            
            // IP del último acceso
            $table->string('ip_ultimo_acceso', 45)->nullable()->after('ip_registro');
            
            // Dispositivo preferido del usuario
            $table->string('dispositivo_preferido')->nullable()->after('ip_ultimo_acceso');
            
            // User agent del último acceso
            $table->text('user_agent_ultimo_acceso')->nullable()->after('dispositivo_preferido');
            
            // Configuración de notificaciones (JSON)
            $table->json('configuracion_notificaciones')->nullable()->after('user_agent_ultimo_acceso');
            
            // Zona horaria del usuario
            $table->string('zona_horaria', 50)->default('America/Lima')->after('configuracion_notificaciones');
            
            // Idioma preferido
            $table->string('idioma', 5)->default('es')->after('zona_horaria');
            
            // Tema preferido (dark/light)
            $table->enum('tema_preferido', ['light', 'dark', 'auto'])->default('auto')->after('idioma');
            
            // Información adicional del perfil (JSON)
            $table->json('perfil_adicional')->nullable()->after('tema_preferido');
            
            // Configuración de privacidad (JSON)
            $table->json('configuracion_privacidad')->nullable()->after('perfil_adicional');
            
            // Número de intentos de login fallidos
            $table->integer('intentos_login_fallidos')->default(0)->after('configuracion_privacidad');
            
            // Fecha del último intento de login fallido
            $table->timestamp('ultimo_intento_fallido')->nullable()->after('intentos_login_fallidos');
            
            // Cuenta bloqueada temporalmente
            $table->boolean('cuenta_bloqueada')->default(false)->after('ultimo_intento_fallido');
            
            // Fecha hasta la cual está bloqueada la cuenta
            $table->timestamp('bloqueado_hasta')->nullable()->after('cuenta_bloqueada');
            
            // Motivo del bloqueo
            $table->text('motivo_bloqueo')->nullable()->after('bloqueado_hasta');
            
            // Usuario que bloqueó la cuenta
            $table->unsignedBigInteger('bloqueado_por')->nullable()->after('motivo_bloqueo');
            $table->foreign('bloqueado_por')->references('id')->on('users')->onDelete('set null');
            
            // Configuración de dos factores (JSON)
            $table->json('configuracion_2fa')->nullable()->after('bloqueado_por');
            
            // Está habilitado el 2FA
            $table->boolean('dos_factores_habilitado')->default(false)->after('configuracion_2fa');
            
            // Códigos de recuperación del 2FA (JSON encriptado)
            $table->text('codigos_recuperacion_2fa')->nullable()->after('dos_factores_habilitado');
            
            // Preferencias de marketing (JSON)
            $table->json('preferencias_marketing')->nullable()->after('codigos_recuperacion_2fa');
            
            // Versión de términos aceptada
            $table->string('version_terminos_aceptada', 10)->nullable()->after('preferencias_marketing');

            // Configuración de la cuenta (JSON)
            $table->json('configuracion_cuenta')->nullable()->after('version_terminos_aceptada');
            
            // Es cuenta de prueba/demo
            $table->boolean('es_cuenta_demo')->default(false)->after('configuracion_cuenta');
            
            // Fecha de expiración de la cuenta demo
            $table->timestamp('demo_expira_en')->nullable()->after('es_cuenta_demo');
            
            // Origen de registro (web, mobile, api, social)
            $table->enum('origen_registro', ['web', 'mobile', 'api', 'social', 'admin'])->default('web')->after('demo_expira_en');
            
            // Referido por (código de referido)
            $table->string('codigo_referido_usado', 50)->nullable()->after('origen_registro');
            
            // Código de referido personal
            $table->string('codigo_referido_personal', 50)->unique()->nullable()->after('codigo_referido_usado');
            
            // Estadísticas del usuario (JSON)
            $table->json('estadisticas_usuario')->nullable()->after('codigo_referido_personal');
            
            // Índices para optimizar consultas
            $table->index(['ultimo_acceso', 'activo']);
            $table->index(['cuenta_bloqueada', 'bloqueado_hasta']);
            $table->index(['dos_factores_habilitado', 'activo']);
            $table->index(['es_cuenta_demo', 'demo_expira_en']);
            $table->index(['origen_registro', 'created_at']);
            $table->index('ip_ultimo_acceso');
            $table->index('zona_horaria');
            $table->index('codigo_referido_personal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['codigo_referido_personal']);
            $table->dropIndex(['zona_horaria']);
            $table->dropIndex(['ip_ultimo_acceso']);
            $table->dropIndex(['origen_registro', 'created_at']);
            $table->dropIndex(['es_cuenta_demo', 'demo_expira_en']);
            $table->dropIndex(['dos_factores_habilitado', 'activo']);
            $table->dropIndex(['cuenta_bloqueada', 'bloqueado_hasta']);
            $table->dropIndex(['ultimo_acceso', 'activo']);
            
            $table->dropForeign(['bloqueado_por']);
            
            $table->dropColumn([
                'ultimo_acceso',
                'ip_registro',
                'ip_ultimo_acceso',
                'dispositivo_preferido',
                'user_agent_ultimo_acceso',
                'configuracion_notificaciones',
                'zona_horaria',
                'idioma',
                'tema_preferido',
                'perfil_adicional',
                'configuracion_privacidad',
                'intentos_login_fallidos',
                'ultimo_intento_fallido',
                'cuenta_bloqueada',
                'bloqueado_hasta',
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
            ]);
        });
    }
};
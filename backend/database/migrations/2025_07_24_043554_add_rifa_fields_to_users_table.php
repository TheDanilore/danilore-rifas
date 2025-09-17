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
            // Información personal básica
            $table->string('nombre', 100)->nullable()->after('name');
            $table->string('apellido', 100)->nullable()->after('nombre');
            
            // Información personal y contacto
            $table->string('telefono', 15)->nullable()->after('apellido');
            $table->enum('tipo_documento', ['dni', 'ce', 'passport', 'ruc', 'otros'])->default('dni')->after('telefono');
            $table->string('numero_documento', 20)->nullable()->after('tipo_documento');
            $table->date('fecha_nacimiento')->nullable()->after('numero_documento');
            $table->enum('genero', ['masculino', 'femenino', 'otro', 'no_especificar'])->nullable()->after('fecha_nacimiento');
            
            // Dirección completa
            $table->text('direccion')->nullable()->after('genero');
            $table->string('ciudad', 100)->nullable()->after('direccion');
            $table->string('departamento', 100)->nullable()->after('ciudad');
            $table->string('codigo_postal', 10)->nullable()->after('departamento');
            $table->string('pais', 3)->default('PE')->after('codigo_postal'); // Código ISO del país
            
            // Sistema de usuarios y permisos (los roles se manejan con Spatie Permission)
            $table->boolean('activo')->default(true)->after('pais');
            $table->boolean('verificado')->default(false)->after('activo'); // Para verificación de cuenta
            $table->datetime('ultimo_acceso')->nullable()->after('verificado');
            $table->string('avatar')->nullable()->after('ultimo_acceso');
            $table->string('zona_horaria', 50)->default('America/Lima')->after('avatar');
            
            // Preferencias de notificaciones
            $table->json('preferencias_notificacion')->nullable()->after('zona_horaria'); // JSON más flexible
            
            // Términos y condiciones
            $table->boolean('acepta_terminos')->default(false)->after('preferencias_notificacion'); // Acepta términos y condiciones
            $table->datetime('fecha_aceptacion_terminos')->nullable()->after('acepta_terminos'); // Fecha cuando aceptó términos
            $table->boolean('acepta_marketing')->default(false)->after('fecha_aceptacion_terminos'); // Acepta recibir promociones
            $table->datetime('fecha_aceptacion_marketing')->nullable()->after('acepta_marketing'); // Fecha cuando aceptó marketing
            
            // Estadísticas del usuario (se actualizan automáticamente)
            $table->integer('total_boletos_comprados')->default(0)->after('preferencias_notificacion');
            $table->decimal('total_gastado', 12, 2)->default(0)->after('total_boletos_comprados');
            $table->integer('total_rifas_participadas')->default(0)->after('total_gastado');
            $table->integer('rifas_ganadas')->default(0)->after('total_rifas_participadas');
            $table->datetime('primera_compra')->nullable()->after('rifas_ganadas');
            $table->datetime('ultima_compra')->nullable()->after('primera_compra');
            
            // Configuración de seguridad
            $table->boolean('doble_autenticacion')->default(false)->after('ultima_compra');
            $table->integer('intentos_login_fallidos')->default(0)->after('doble_autenticacion');
            $table->datetime('bloqueado_hasta')->nullable()->after('intentos_login_fallidos');
            
            // Índices optimizados
            $table->index(['tipo_documento', 'numero_documento']);
            $table->index('telefono');
            $table->index(['activo', 'verificado']);
            $table->index(['ciudad', 'departamento']);
            $table->index('ultimo_acceso');
            $table->index(['total_boletos_comprados', 'total_gastado']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nombre', 'apellido', 'telefono', 'tipo_documento', 'numero_documento', 'fecha_nacimiento', 
                'genero', 'direccion', 'ciudad', 'departamento', 'codigo_postal', 'pais',
                'activo', 'verificado', 'ultimo_acceso', 'avatar', 'zona_horaria',
                'preferencias_notificacion', 'acepta_terminos', 'fecha_aceptacion_terminos', 
                'acepta_marketing', 'fecha_aceptacion_marketing', 'total_boletos_comprados', 'total_gastado', 
                'total_rifas_participadas', 'rifas_ganadas', 'primera_compra', 'ultima_compra',
                'doble_autenticacion', 'intentos_login_fallidos', 'bloqueado_hasta'
            ]);
        });
    }
};

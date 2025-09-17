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
        Schema::table('favoritos', function (Blueprint $table) {
            // Notificar al usuario sobre cambios en la rifa
            $table->boolean('notificar')->default(true)->after('user_id');
            
            // Fecha de expiración del favorito
            $table->timestamp('fecha_expiracion')->nullable()->after('notificar');
            
            // Prioridad del favorito (1-5, donde 5 es máxima prioridad)
            $table->tinyInteger('prioridad')->default(3)->after('fecha_expiracion');
            
            // Notas personales del usuario sobre la rifa
            $table->text('notas')->nullable()->after('prioridad');
            
            // Categorías personalizadas del usuario (JSON)
            $table->json('categorias_usuario')->nullable()->after('notas');
            
            // Configuración de alertas (JSON)
            $table->json('configuracion_alertas')->nullable()->after('categorias_usuario');
            
            // Último acceso a la rifa desde favoritos
            $table->timestamp('ultimo_acceso')->nullable()->after('configuracion_alertas');
            
            // Contador de veces que ha accedido desde favoritos
            $table->integer('contador_accesos')->default(0)->after('ultimo_acceso');
            
            // Es favorito público (visible para otros usuarios)
            $table->boolean('es_publico')->default(false)->after('contador_accesos');
            
            // Recordatorio configurado
            $table->timestamp('recordatorio_fecha')->nullable()->after('es_publico');
            $table->text('recordatorio_mensaje')->nullable()->after('recordatorio_fecha');
            
            // Índices para optimizar consultas
            $table->index(['prioridad', 'created_at']);
            $table->index(['notificar', 'fecha_expiracion']);
            $table->index(['es_publico', 'prioridad']);
            $table->index('ultimo_acceso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favoritos', function (Blueprint $table) {
            $table->dropIndex(['ultimo_acceso']);
            $table->dropIndex(['es_publico', 'prioridad']);
            $table->dropIndex(['notificar', 'fecha_expiracion']);
            $table->dropIndex(['prioridad', 'created_at']);
            
            $table->dropColumn([
                'notificar',
                'fecha_expiracion',
                'prioridad',
                'notas',
                'categorias_usuario',
                'configuracion_alertas',
                'ultimo_acceso',
                'contador_accesos',
                'es_publico',
                'recordatorio_fecha',
                'recordatorio_mensaje'
            ]);
        });
    }
};
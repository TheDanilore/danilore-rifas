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
        Schema::table('comentarios', function (Blueprint $table) {
            // Campos de auditoría y seguridad
            $table->string('ip_address', 45)->nullable()->after('dislikes'); // IPv4 e IPv6
            $table->text('user_agent')->nullable()->after('ip_address');
            
            // Control de ediciones
            $table->boolean('editado')->default(false)->after('user_agent');
            $table->datetime('fecha_edicion')->nullable()->after('editado');
            $table->unsignedBigInteger('editado_por')->nullable()->after('fecha_edicion');
            
            // Métricas adicionales
            $table->boolean('es_spam')->default(false)->after('editado_por');
            $table->integer('reportes')->default(0)->after('es_spam');
            $table->json('imagenes')->nullable()->after('reportes'); // URLs de imágenes adjuntas
            
            // Control de notificaciones
            $table->boolean('notificar_respuestas')->default(true)->after('imagenes');
            
            // Índices adicionales
            $table->index(['editado', 'fecha_edicion']);
            $table->index(['es_spam', 'reportes']);
            $table->index('ip_address');
            
            // Relación para quien editó
            $table->foreign('editado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comentarios', function (Blueprint $table) {
            $table->dropForeign(['editado_por']);
            $table->dropIndex(['editado', 'fecha_edicion']);
            $table->dropIndex(['es_spam', 'reportes']);
            $table->dropIndex(['ip_address']);
            
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'editado',
                'fecha_edicion',
                'editado_por',
                'es_spam',
                'reportes',
                'imagenes',
                'notificar_respuestas'
            ]);
        });
    }
};
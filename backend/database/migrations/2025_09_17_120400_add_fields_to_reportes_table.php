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
        Schema::table('reportes', function (Blueprint $table) {
            // Si el reporte está resuelto
            $table->boolean('resuelto')->default(false)->after('estado');
            
            // Usuario que resolvió el reporte
            $table->unsignedBigInteger('resuelto_por')->nullable()->after('resuelto');
            $table->foreign('resuelto_por')->references('id')->on('users')->onDelete('set null');
            
            // Fecha de resolución
            $table->timestamp('fecha_resolucion')->nullable()->after('resuelto_por');
            
            // Acción tomada para resolver el reporte
            $table->text('accion_tomada')->nullable()->after('fecha_resolucion');
            
            // Prioridad del reporte (1-5, donde 5 es crítico)
            $table->tinyInteger('prioridad')->default(3)->after('accion_tomada');
            
            // Categoría del reporte
            $table->enum('categoria', [
                'spam', 
                'contenido_inapropiado', 
                'fraude', 
                'violacion_terminos', 
                'problema_tecnico', 
                'otro'
            ])->default('otro')->after('prioridad');
            
            // Subcategoría específica
            $table->string('subcategoria', 100)->nullable()->after('categoria');
            
            // Evidencias adjuntas (JSON con URLs)
            $table->json('evidencias')->nullable()->after('subcategoria');
            
            // IP del usuario que reporta
            $table->string('ip_reporter', 45)->nullable()->after('evidencias');
            
            // User agent del usuario que reporta
            $table->text('user_agent_reporter')->nullable()->after('ip_reporter');
            
            // Seguimiento interno (JSON)
            $table->json('seguimiento')->nullable()->after('user_agent_reporter');
            
            // Número de reportes similares
            $table->integer('reportes_similares')->default(0)->after('seguimiento');
            
            // Es un reporte duplicado
            $table->boolean('es_duplicado')->default(false)->after('reportes_similares');
            
            // Reporte original (si es duplicado)
            $table->unsignedBigInteger('reporte_original_id')->nullable()->after('es_duplicado');
            $table->foreign('reporte_original_id')->references('id')->on('reportes')->onDelete('set null');
            
            // Tiempo estimado de resolución (en horas)
            $table->integer('tiempo_estimado_resolucion')->nullable()->after('reporte_original_id');
            
            // Notificar al reporter cuando se resuelva
            $table->boolean('notificar_resolucion')->default(true)->after('tiempo_estimado_resolucion');
            
            // Feedback del reporter sobre la resolución
            $table->text('feedback_reporter')->nullable()->after('notificar_resolucion');
            $table->tinyInteger('rating_resolucion')->nullable()->after('feedback_reporter'); // 1-5
            
            // Índices para optimizar consultas
            $table->index(['resuelto', 'prioridad', 'created_at']);
            $table->index(['categoria', 'resuelto']);
            $table->index(['prioridad', 'estado']);
            $table->index(['es_duplicado', 'reporte_original_id']);
            $table->index('fecha_resolucion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropIndex(['fecha_resolucion']);
            $table->dropIndex(['es_duplicado', 'reporte_original_id']);
            $table->dropIndex(['prioridad', 'estado']);
            $table->dropIndex(['categoria', 'resuelto']);
            $table->dropIndex(['resuelto', 'prioridad', 'created_at']);
            
            $table->dropForeign(['reporte_original_id']);
            $table->dropForeign(['resuelto_por']);
            
            $table->dropColumn([
                'resuelto',
                'resuelto_por',
                'fecha_resolucion',
                'accion_tomada',
                'prioridad',
                'categoria',
                'subcategoria',
                'evidencias',
                'ip_reporter',
                'user_agent_reporter',
                'seguimiento',
                'reportes_similares',
                'es_duplicado',
                'reporte_original_id',
                'tiempo_estimado_resolucion',
                'notificar_resolucion',
                'feedback_reporter',
                'rating_resolucion'
            ]);
        });
    }
};
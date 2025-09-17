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
        Schema::table('progreso_premios', function (Blueprint $table) {
            // Observaciones sobre el progreso
            $table->text('observaciones')->nullable()->after('historial_diario');
            
            // Si el progreso es público (visible para usuarios)
            $table->boolean('es_publico')->default(true)->after('observaciones');
            
            // Orden de visualización en la UI
            $table->integer('orden_visualizacion')->default(0)->after('es_publico');
            
            // Color del progreso para la UI
            $table->string('color_progreso', 7)->default('#007bff')->after('orden_visualizacion');
            
            // Mensaje personalizado para mostrar en la UI
            $table->string('mensaje_personalizado')->nullable()->after('color_progreso');
            
            // Hitos del progreso (JSON) - ej: [{"porcentaje": 25, "mensaje": "¡25% completado!"}, ...]
            $table->json('hitos')->nullable()->after('mensaje_personalizado');
            
            // Fecha de último cálculo
            $table->timestamp('fecha_ultimo_calculo')->nullable()->after('hitos');
            
            // Estadísticas adicionales (JSON)
            $table->json('estadisticas')->nullable()->after('fecha_ultimo_calculo');
            
            // Tendencia de ventas (calculada)
            $table->decimal('tendencia_ventas', 5, 2)->default(0)->after('estadisticas');
            
            // Velocidad de ventas (tickets por hora)
            $table->decimal('velocidad_ventas', 8, 2)->default(0)->after('tendencia_ventas');
            
            // Proyección de finalización
            $table->timestamp('proyeccion_finalizacion')->nullable()->after('velocidad_ventas');
            
            // Es tendencia alcista
            $table->boolean('tendencia_alcista')->default(false)->after('proyeccion_finalizacion');
            
            // Notificar cuando se alcance cierto porcentaje
            $table->decimal('porcentaje_notificacion', 5, 2)->nullable()->after('tendencia_alcista');
            $table->boolean('notificacion_enviada')->default(false)->after('porcentaje_notificacion');
            
            // Índices para optimizar consultas
            $table->index(['es_publico', 'orden_visualizacion']);
            $table->index(['porcentaje_completado', 'es_publico']);
            $table->index('fecha_ultimo_calculo');
            $table->index('tendencia_alcista');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progreso_premios', function (Blueprint $table) {
            $table->dropIndex(['tendencia_alcista']);
            $table->dropIndex(['fecha_ultimo_calculo']);
            $table->dropIndex(['porcentaje_completado', 'es_publico']);
            $table->dropIndex(['es_publico', 'orden_visualizacion']);
            
            $table->dropColumn([
                'observaciones',
                'es_publico',
                'orden_visualizacion',
                'color_progreso',
                'mensaje_personalizado',
                'hitos',
                'fecha_ultimo_calculo',
                'estadisticas',
                'tendencia_ventas',
                'velocidad_ventas',
                'proyeccion_finalizacion',
                'tendencia_alcista',
                'porcentaje_notificacion',
                'notificacion_enviada'
            ]);
        });
    }
};
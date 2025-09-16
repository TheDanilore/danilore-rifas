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
        Schema::create('sorteos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rifa_id');
            $table->string('codigo_sorteo', 20)->unique();
            
            // Información del sorteo
            $table->datetime('fecha_programada');
            $table->datetime('fecha_realizada')->nullable();
            $table->enum('estado', ['programado', 'en_progreso', 'completado', 'cancelado'])->default('programado');
            $table->enum('tipo', ['automatico', 'manual', 'streaming_en_vivo'])->default('automatico');
            $table->text('descripcion')->nullable();
            
            // Configuración del sorteo
            $table->string('metodo_sorteo', 50)->default('aleatorio'); // aleatorio, secuencial, etc.
            $table->json('configuracion_sorteo')->nullable(); // Configuraciones específicas
            $table->string('semilla_aleatoriedad')->nullable(); // Para reproducibilidad
            $table->boolean('publico')->default(true); // Si el sorteo es público
            $table->string('url_transmision')->nullable(); // URL del streaming en vivo
            
            // Resultados
            $table->json('resultados')->nullable(); // Ganadores y orden
            $table->integer('total_participantes')->default(0);
            $table->integer('total_boletos_participantes')->default(0);
            
            // Verificación y transparencia
            $table->string('hash_verificacion')->nullable(); // Hash para verificar integridad
            $table->text('evidencia_sorteo')->nullable(); // Descripción de la evidencia
            $table->json('archivos_evidencia')->nullable(); // URLs de archivos de evidencia
            $table->boolean('verificado')->default(false);
            $table->unsignedBigInteger('verificado_por')->nullable();
            $table->datetime('fecha_verificacion')->nullable();
            
            // Información administrativa
            $table->unsignedBigInteger('realizado_por')->nullable();
            $table->text('observaciones')->nullable();
            $table->json('log_acciones')->nullable(); // Log de acciones durante el sorteo
            
            $table->timestamps();
            
            // Índices
            $table->index(['rifa_id', 'estado']);
            $table->index(['fecha_programada', 'estado']);
            $table->index(['tipo', 'publico']);
            $table->index(['verificado', 'fecha_verificacion']);
            $table->index(['realizado_por', 'fecha_realizada']);
            
            // Relaciones
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('realizado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verificado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sorteos');
    }
};
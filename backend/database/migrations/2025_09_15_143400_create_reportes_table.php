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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['ventas', 'usuarios', 'rifas', 'premios', 'financiero', 'personalizado'])->default('ventas');
            $table->json('parametros'); // Parámetros del reporte (fechas, filtros, etc.)
            $table->json('configuracion')->nullable(); // Configuración de columnas, formatos, etc.
            $table->enum('formato', ['excel', 'pdf', 'csv', 'json'])->default('excel');
            $table->enum('estado', ['pendiente', 'procesando', 'completado', 'error'])->default('pendiente');
            $table->text('error_mensaje')->nullable();
            $table->string('archivo_generado')->nullable(); // Ruta del archivo generado
            $table->integer('total_registros')->nullable();
            $table->datetime('fecha_inicio_procesamiento')->nullable();
            $table->datetime('fecha_fin_procesamiento')->nullable();
            $table->unsignedBigInteger('solicitado_por');
            $table->boolean('programado')->default(false); // Si es un reporte programado
            $table->string('frecuencia')->nullable(); // diario, semanal, mensual
            $table->datetime('proxima_ejecucion')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['tipo', 'estado']);
            $table->index(['solicitado_por', 'created_at']);
            $table->index(['programado', 'proxima_ejecucion']);
            $table->index(['estado', 'fecha_inicio_procesamiento']);
            
            // Relaciones
            $table->foreign('solicitado_por')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
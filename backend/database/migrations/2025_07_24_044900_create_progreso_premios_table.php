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
        Schema::create('progreso_premios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rifa_id');
            $table->unsignedBigInteger('premio_id')->nullable(); // NULL para progreso general de la rifa
            $table->unsignedBigInteger('nivel_id')->nullable(); // NULL si es progreso general del premio
            $table->integer('tickets_actuales')->default(0);
            $table->integer('tickets_objetivo');
            $table->integer('tickets_restantes')->storedAs('tickets_objetivo - tickets_actuales');
            $table->decimal('porcentaje_completado', 5, 2)->default(0); // 0.00 - 100.00
            $table->boolean('objetivo_alcanzado')->default(false);
            $table->datetime('fecha_alcanzado')->nullable();
            $table->datetime('ultimo_ticket')->nullable(); // Fecha del último ticket que contribuyó
            $table->integer('tickets_hoy')->default(0); // Tickets vendidos hoy
            $table->integer('tickets_esta_semana')->default(0); // Tickets vendidos esta semana
            $table->decimal('velocidad_venta', 8, 2)->default(0); // Tickets por día promedio
            $table->datetime('fecha_estimada_completacion')->nullable(); // Estimación basada en velocidad
            $table->json('historial_diario')->nullable(); // Historial de ventas por día
            $table->timestamps();
            
            // Índices optimizados
            $table->unique(['rifa_id', 'premio_id', 'nivel_id']); // Un registro único por combinación
            $table->index(['rifa_id', 'objetivo_alcanzado']);
            $table->index(['premio_id', 'objetivo_alcanzado']);
            $table->index(['porcentaje_completado', 'objetivo_alcanzado']);
            $table->index('ultimo_ticket');
            $table->index(['rifa_id', 'tickets_actuales']);
            $table->index(['fecha_estimada_completacion', 'objetivo_alcanzado'], 'pp_fecha_estimada_objetivo_idx');
            $table->index(['velocidad_venta', 'objetivo_alcanzado'], 'pp_velocidad_objetivo_idx');
            
            // Relaciones con cascada
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('premio_id')->references('id')->on('premios')->onDelete('cascade');
            $table->foreign('nivel_id')->references('id')->on('niveles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progreso_premios');
    }
};

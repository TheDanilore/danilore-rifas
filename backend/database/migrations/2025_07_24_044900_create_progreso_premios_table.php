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
            $table->bigInteger('rifa_id')->unsigned()->nullable();
            $table->unsignedBigInteger('premio_id');
            $table->unsignedBigInteger('nivel_id')->nullable(); // NULL si es progreso general del premio
            $table->integer('tickets_actuales')->default(0);
            $table->integer('tickets_objetivo');
            $table->decimal('porcentaje_completado', 5, 2)->default(0); // 0.00 - 100.00
            $table->boolean('objetivo_alcanzado')->default(false);
            $table->datetime('fecha_alcanzado')->nullable();
            $table->datetime('ultimo_ticket')->nullable(); // Fecha del último ticket que contribuyó
            $table->integer('tickets_restantes')->virtualAs('tickets_objetivo - tickets_actuales');
            $table->timestamps();
            
            // Índices
            $table->unique(['premio_id', 'nivel_id']); // Un registro por premio-nivel
            $table->index(['premio_id', 'objetivo_alcanzado']);
            $table->index(['porcentaje_completado', 'objetivo_alcanzado']);
            $table->index('ultimo_ticket');
            // Agregar índice para mejorar performance
            $table->index(['rifa_id', 'premio_id']);
            $table->index(['rifa_id', 'nivel_id']);
            
            // Agregar foreign key constraint
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            // Relaciones
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

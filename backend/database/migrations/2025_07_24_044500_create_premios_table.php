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
        Schema::create('premios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rifa_id');
            $table->string('codigo', 10); // p1, p2, p3, etc.
            $table->string('titulo');
            $table->text('descripcion');
            $table->decimal('valor_estimado', 12, 2)->nullable(); // Valor aproximado del premio completo
            $table->string('imagen_principal')->nullable();
            $table->json('media_gallery')->nullable(); // Imágenes y videos adicionales
            $table->integer('orden'); // Orden del premio (1, 2, 3...)
            $table->integer('tickets_minimos_desbloqueo'); // Tickets mínimos para iniciar este premio
            $table->unsignedBigInteger('premio_requerido_id')->nullable(); // Premio que debe completarse antes
            $table->enum('estado', ['bloqueado', 'activo', 'completado'])->default('bloqueado');
            $table->boolean('desbloqueado')->default(false);
            $table->datetime('fecha_desbloqueo')->nullable();
            $table->datetime('fecha_completado')->nullable();
            $table->integer('total_niveles')->default(0); // Calculado automáticamente
            $table->integer('niveles_completados')->default(0); // Calculado automáticamente
            $table->decimal('porcentaje_completado', 5, 2)->default(0); // 0.00 - 100.00
            $table->boolean('es_premio_final')->default(false); // Si es el último premio de la rifa
            $table->text('condiciones_especiales')->nullable(); // Condiciones adicionales
            $table->text('notas_admin')->nullable();
            $table->timestamps();
            
            // Índices optimizados
            $table->unique(['rifa_id', 'codigo']); // Un código por rifa
            $table->index(['rifa_id', 'orden']);
            $table->index(['estado', 'desbloqueado']);
            $table->index(['rifa_id', 'estado', 'orden']);
            $table->index('tickets_minimos_desbloqueo');
            $table->index(['es_premio_final', 'estado']);
            
            // Relaciones
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('premio_requerido_id')->references('id')->on('premios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premios');
    }
};

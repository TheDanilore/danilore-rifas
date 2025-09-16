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
        Schema::create('niveles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('premio_id');
            $table->string('codigo', 10); // n1, n2, n3, etc.
            $table->string('titulo');
            $table->text('descripcion');
            $table->integer('tickets_necesarios'); // Tickets necesarios para desbloquear este nivel
            $table->integer('tickets_acumulados'); // Tickets acumulados para llegar a este nivel
            $table->decimal('valor_aproximado', 12, 2)->nullable(); // Valor estimado del nivel
            $table->json('media_gallery')->nullable(); // Galería de imágenes del nivel
            $table->string('imagen')->nullable(); // Imagen principal del nivel
            $table->integer('orden'); // Orden dentro del premio (1, 2, 3...)
            $table->enum('estado', ['bloqueado', 'activo', 'completado'])->default('bloqueado');
            $table->boolean('desbloqueado')->default(false);
            $table->boolean('es_nivel_final')->default(false); // Si es el último nivel del premio
            $table->datetime('fecha_desbloqueo')->nullable();
            $table->datetime('fecha_completado')->nullable();
            $table->integer('progreso_actual')->default(0); // Tickets actuales hacia este nivel
            $table->decimal('porcentaje_progreso', 5, 2)->default(0); // 0.00 - 100.00
            $table->json('especificaciones')->nullable(); // Especificaciones técnicas del producto
            $table->json('contenido_adicional')->nullable(); // Videos, fotos extras, etc.
            $table->text('mensaje_desbloqueo')->nullable(); // Mensaje personalizado al desbloquearse
            $table->timestamps();
            
            // Índices optimizados
            $table->unique(['premio_id', 'codigo']); // Un código por premio
            $table->index(['premio_id', 'orden']);
            $table->index(['estado', 'desbloqueado']);
            $table->index(['premio_id', 'estado', 'orden']);
            $table->index('tickets_necesarios');
            $table->index(['es_nivel_final', 'estado']);
            $table->index(['desbloqueado', 'fecha_desbloqueo']);
            
            // Relaciones
            $table->foreign('premio_id')->references('id')->on('premios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('niveles');
    }
};

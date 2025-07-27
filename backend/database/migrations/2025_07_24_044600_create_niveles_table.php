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
            $table->integer('tickets_necesarios'); // Tickets necesarios para desbloquear
            $table->decimal('valor_aproximado', 12, 2)->nullable(); // Valor estimado del nivel
            $table->json('media_gallery')->nullable(); // Galería de imágenes del nivel
            $table->string('imagen')->nullable(); // Imagen principal del nivel
            $table->integer('orden'); // Orden dentro del premio (1, 2, 3...)
            $table->boolean('desbloqueado')->default(false);
            $table->boolean('es_actual')->default(false); // Si es el nivel activo actualmente
            $table->datetime('fecha_desbloqueo')->nullable();
            $table->text('especificaciones')->nullable(); // JSON con especificaciones técnicas
            $table->timestamps();
            
            // Índices
            $table->unique(['premio_id', 'codigo']); // Un código por premio
            $table->index(['premio_id', 'orden']);
            $table->index(['desbloqueado', 'es_actual']);
            $table->index('tickets_necesarios');
            
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

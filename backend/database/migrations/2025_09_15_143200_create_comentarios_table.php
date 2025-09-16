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
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rifa_id');
            $table->unsignedBigInteger('comentario_padre_id')->nullable(); // Para respuestas
            $table->text('comentario');
            $table->decimal('rating', 3, 2)->nullable(); // 0.00 - 5.00, solo para comentarios principales
            $table->boolean('activo')->default(true);
            $table->boolean('moderado')->default(false);
            $table->unsignedBigInteger('moderado_por')->nullable();
            $table->datetime('fecha_moderacion')->nullable();
            $table->text('razon_moderacion')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('dislikes')->default(0);
            $table->timestamps();
            
            // Índices
            $table->index(['rifa_id', 'activo', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['comentario_padre_id', 'activo']);
            $table->index(['moderado', 'activo']);
            $table->index('rating');
            
            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('comentario_padre_id')->references('id')->on('comentarios')->onDelete('cascade');
            $table->foreign('moderado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->text('valor');
            $table->enum('tipo', ['string', 'number', 'boolean', 'json', 'array', 'email', 'url', 'color', 'date'])->default('string');
            $table->string('categoria', 50)->nullable(); // Subcategoría dentro del grupo
            $table->text('descripcion')->nullable();
            $table->boolean('editable')->default(true);
            $table->boolean('visible_admin')->default(true); // Si aparece en el panel de admin
            $table->unsignedBigInteger('modificado_por')->nullable(); // Usuario que hizo la última modificación
            $table->datetime('fecha_modificacion')->nullable();
            $table->text('comentario_modificacion')->nullable();
            $table->timestamps();

            // Índices optimizados
            $table->index(['categoria']);
            $table->index(['editable', 'visible_admin']);
            $table->index(['editable']);
            $table->index(['modificado_por', 'fecha_modificacion']);
            $table->index('tipo');

            // Relación
            $table->foreign('modificado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};

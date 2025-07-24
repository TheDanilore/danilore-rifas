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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('icono', 50)->nullable(); // Para iconos CSS como 'fas fa-car'
            $table->string('color', 7)->default('#8B5CF6'); // Color hex para la categoría
            $table->boolean('activa')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
            
            // Índices
            $table->index(['activa', 'orden']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};

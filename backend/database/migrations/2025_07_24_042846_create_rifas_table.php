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
        Schema::create('rifas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->decimal('precio_boleto', 10, 2);
            $table->integer('total_boletos');
            $table->integer('boletos_vendidos')->default(0);
            $table->string('imagen_principal')->nullable();
            $table->json('imagenes_adicionales')->nullable();
            $table->decimal('premio_valor', 12, 2);
            $table->text('premio_descripcion');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->datetime('fecha_sorteo');
            $table->enum('estado', ['activa', 'pausada', 'finalizada', 'cancelada'])->default('activa');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('codigo_unico', 20)->unique();
            $table->boolean('es_destacada')->default(false);
            $table->integer('max_boletos_por_persona')->default(10);
            $table->text('terminos_condiciones')->nullable();
            $table->unsignedBigInteger('ganador_user_id')->nullable();
            $table->string('numero_ganador', 10)->nullable();
            $table->text('notas_admin')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['estado', 'fecha_inicio', 'fecha_fin']);
            $table->index('categoria_id');
            $table->index('es_destacada');
            
            // Relaciones
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('ganador_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rifas');
    }
};

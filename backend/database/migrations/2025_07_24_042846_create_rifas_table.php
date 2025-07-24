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
            $table->integer('boletos_minimos'); // Cambiado de total_boletos
            $table->integer('boletos_vendidos')->default(0);
            $table->string('imagen_principal')->nullable();
            $table->json('imagenes_adicionales')->nullable();
            $table->json('media_gallery')->nullable(); // Para múltiples medios
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->datetime('fecha_sorteo');
            $table->enum('estado', ['en_venta', 'confirmada', 'sorteada', 'cancelada'])->default('en_venta');
            $table->enum('tipo', ['actual', 'futura'])->default('futura');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('codigo_unico', 20)->unique();
            $table->boolean('es_destacada')->default(false);
            $table->integer('max_boletos_por_persona')->default(10);
            $table->text('terminos_condiciones')->nullable();
            $table->integer('orden')->default(1); // Para ordenar rifas futuras
            $table->unsignedBigInteger('rifa_requerida_id')->nullable(); // Para rifas que dependen de otras
            $table->text('notas_admin')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['estado', 'tipo', 'fecha_inicio']);
            $table->index(['tipo', 'orden']);
            $table->index('categoria_id');
            $table->index('es_destacada');
            
            // Relaciones
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('rifa_requerida_id')->references('id')->on('rifas')->onDelete('set null');
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

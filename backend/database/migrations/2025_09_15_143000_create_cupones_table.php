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
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre'); // Nombre descriptivo del cupón
            $table->text('descripcion')->nullable();
            
            // Tipo y configuración del descuento
            $table->enum('tipo_descuento', ['porcentaje', 'monto_fijo'])->default('porcentaje');
            $table->decimal('valor_descuento', 10, 2); // Porcentaje o monto fijo
            $table->decimal('descuento_maximo', 10, 2)->nullable(); // Límite máximo para porcentajes
            $table->decimal('compra_minima', 10, 2)->default(0); // Compra mínima requerida
            
            // Restricciones de uso
            $table->integer('usos_maximos')->nullable(); // NULL = ilimitado
            $table->integer('usos_actuales')->default(0);
            $table->integer('usos_por_usuario')->default(1); // Límite por usuario
            $table->boolean('solo_primera_compra')->default(false);
            
            // Fechas de validez
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin');
            
            // Restricciones adicionales
            $table->json('rifas_aplicables')->nullable(); // IDs de rifas específicas (null = todas)
            $table->json('categorias_aplicables')->nullable(); // IDs de categorías específicas
            $table->json('usuarios_aplicables')->nullable(); // IDs de usuarios específicos
            $table->decimal('monto_maximo_descuento', 10, 2)->nullable();
            
            // Estado y configuración
            $table->boolean('activo')->default(true);
            $table->boolean('visible_publico')->default(false); // Si aparece en listados públicos
            $table->enum('estado', ['activo', 'pausado', 'agotado', 'expirado'])->default('activo');
            
            // Información administrativa
            $table->unsignedBigInteger('creado_por');
            $table->text('notas_admin')->nullable();
            $table->string('campana', 100)->nullable(); // Campaña de marketing asociada
            
            $table->timestamps();
            
            // Índices
            $table->index(['activo', 'fecha_inicio', 'fecha_fin']);
            $table->index(['estado', 'visible_publico']);
            $table->index(['tipo_descuento', 'activo']);
            $table->index(['usos_actuales', 'usos_maximos']);
            $table->index(['creado_por', 'created_at']);
            $table->index('campana');
            
            // Relaciones
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
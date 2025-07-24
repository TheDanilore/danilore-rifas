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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_afectada'); // Nombre de la tabla
            $table->string('accion'); // INSERT, UPDATE, DELETE
            $table->unsignedBigInteger('registro_id'); // ID del registro afectado
            $table->unsignedBigInteger('user_id')->nullable(); // Usuario que realizó la acción
            $table->json('datos_anteriores')->nullable(); // Datos antes del cambio
            $table->json('datos_nuevos')->nullable(); // Datos después del cambio
            $table->string('ip_address', 45)->nullable(); // IP del usuario
            $table->string('user_agent')->nullable(); // Navegador del usuario
            $table->text('observaciones')->nullable(); // Comentarios adicionales
            $table->timestamps();
            
            // Índices
            $table->index(['tabla_afectada', 'registro_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('accion');
            
            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};

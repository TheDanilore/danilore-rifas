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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('titulo');
            $table->text('mensaje');
            $table->enum('tipo', ['info', 'success', 'warning', 'error'])->default('info');
            $table->enum('canal', ['sistema', 'email', 'sms', 'whatsapp'])->default('sistema');
            $table->json('datos_adicionales')->nullable(); // Para datos específicos como rifa_id, venta_id, etc.
            $table->boolean('leida')->default(false);
            $table->datetime('fecha_leida')->nullable();
            $table->boolean('enviada')->default(false);
            $table->datetime('fecha_envio')->nullable();
            $table->text('error_envio')->nullable();
            $table->string('referencia_externa')->nullable(); // ID del servicio de envío
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'leida']);
            $table->index(['tipo', 'canal']);
            $table->index(['enviada', 'fecha_envio']);
            
            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};

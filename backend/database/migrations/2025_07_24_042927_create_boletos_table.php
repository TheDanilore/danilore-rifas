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
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rifa_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('venta_id');
            $table->string('numero', 10); // Número del boleto
            $table->decimal('precio_pagado', 10, 2);
            $table->enum('estado', ['reservado', 'pagado', 'cancelado'])->default('reservado');
            $table->datetime('fecha_reserva');
            $table->datetime('fecha_expiracion_reserva')->nullable();
            $table->datetime('fecha_pago')->nullable();
            $table->string('codigo_verificacion', 50)->unique(); // Código único para verificar el boleto
            $table->boolean('es_ganador')->default(false);
            $table->timestamps();
            
            // Índices
            $table->unique(['rifa_id', 'numero']); // Un número por rifa
            $table->index(['rifa_id', 'user_id']);
            $table->index(['estado', 'fecha_expiracion_reserva']);
            $table->index('codigo_verificacion');
            
            // Relaciones
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};

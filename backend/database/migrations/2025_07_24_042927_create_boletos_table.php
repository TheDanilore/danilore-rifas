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
            
            // Información del boleto
            $table->string('numero', 10); // Número del boleto
            $table->string('codigo_verificacion', 50)->unique(); // Código único para verificar el boleto
            $table->decimal('precio_pagado', 10, 2);
            
            // Estados y fechas
            $table->enum('estado', ['reservado', 'pagado', 'confirmado', 'cancelado', 'transferido'])->default('reservado');
            $table->datetime('fecha_reserva');
            $table->datetime('fecha_expiracion_reserva')->nullable();
            $table->datetime('fecha_pago')->nullable();
            $table->datetime('fecha_confirmacion')->nullable();
            
            // Información del sorteo
            $table->boolean('es_ganador')->default(false);
            $table->integer('posicion_sorteo')->nullable(); // Posición en el sorteo (1er lugar, 2do, etc.)
            $table->string('tipo_premio', 100)->nullable(); // Tipo de premio ganado
            $table->decimal('valor_premio', 12, 2)->nullable(); // Valor del premio ganado
            $table->datetime('fecha_sorteo')->nullable();
            $table->boolean('premio_entregado')->default(false);
            $table->datetime('fecha_entrega_premio')->nullable();
            
            // Transferencias de boletos
            $table->unsignedBigInteger('transferido_a')->nullable(); // Usuario al que se transfirió
            $table->unsignedBigInteger('transferido_por')->nullable(); // Usuario que hizo la transferencia
            $table->datetime('fecha_transferencia')->nullable();
            $table->text('motivo_transferencia')->nullable();
            
            // Información adicional
            $table->string('origen', 50)->default('compra'); // compra, regalo, transferencia, promocion
            $table->json('metadatos')->nullable(); // Información adicional flexible
            $table->text('notas')->nullable();
            
            // Auditoría
            $table->string('ip_creacion', 45)->nullable();
            $table->unsignedBigInteger('creado_por')->nullable(); // Admin que creó el boleto
            
            $table->timestamps();
            
            // Índices optimizados
            $table->unique(['rifa_id', 'numero']); // Un número por rifa
            $table->index(['rifa_id', 'user_id']);
            $table->index(['venta_id', 'estado']);
            $table->index(['estado', 'fecha_expiracion_reserva']);
            $table->index('codigo_verificacion');
            $table->index(['es_ganador', 'posicion_sorteo']);
            $table->index(['rifa_id', 'es_ganador']);
            $table->index(['transferido_a', 'fecha_transferencia']);
            $table->index(['premio_entregado', 'es_ganador']);
            $table->index(['origen', 'estado']);
            $table->index(['user_id', 'estado', 'created_at']);
            
            // Relaciones
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('transferido_a')->references('id')->on('users')->onDelete('set null');
            $table->foreign('transferido_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
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

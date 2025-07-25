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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('rifa_id');
            $table->string('codigo_venta', 20)->unique(); // Código único de la venta
            $table->integer('cantidad_boletos');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('estado', ['pendiente', 'pagada', 'cancelada', 'expirada'])->default('pendiente');
            $table->enum('metodo_pago', ['yape', 'plin', 'transferencia', 'efectivo'])->nullable();
            $table->datetime('fecha_expiracion')->nullable(); // Para reservas temporales
            $table->text('notas_cliente')->nullable();
            $table->text('notas_admin')->nullable();
            
            // Datos del comprador
            $table->string('comprador_nombre');
            $table->string('comprador_email');
            $table->string('comprador_telefono');
            $table->enum('comprador_tipo_documento', ['dni', 'ce', 'passport', 'ruc', 'otros'])->default('dni');
            $table->string('comprador_numero_documento', 20)->nullable();
            
            // Datos de pago
            $table->string('referencia_pago')->nullable();
            $table->datetime('fecha_pago')->nullable();
            $table->decimal('monto_pagado', 12, 2)->nullable();
            $table->string('comprobante_pago')->nullable(); // URL del comprobante
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'estado']);
            $table->index(['rifa_id', 'estado']);
            $table->index(['estado', 'fecha_expiracion']);
            $table->index('metodo_pago');
            
            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};

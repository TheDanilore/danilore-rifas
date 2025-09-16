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
            
            // Información de la venta
            $table->integer('cantidad_boletos');
            $table->decimal('precio_unitario', 10, 2); // Precio por boleto al momento de la venta
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuestos', 12, 2)->default(0);
            $table->decimal('comision', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            // Estados y control
            $table->enum('estado', ['carrito', 'reservado', 'pendiente_pago', 'pagado', 'confirmado', 'cancelado', 'expirado', 'reembolsado'])->default('carrito');
            $table->enum('metodo_pago', ['yape', 'plin', 'transferencia_bancaria', 'efectivo', 'tarjeta_credito', 'tarjeta_debito'])->nullable();
            $table->datetime('fecha_reserva')->nullable();
            $table->datetime('fecha_expiracion')->nullable(); // Para reservas temporales
            $table->datetime('fecha_pago')->nullable();
            $table->datetime('fecha_confirmacion')->nullable();
            
            // Información adicional
            $table->json('numeros_seleccionados')->nullable(); // Números específicos si se permiten elegir
            $table->string('cupon_descuento', 20)->nullable();
            $table->decimal('porcentaje_descuento', 5, 2)->default(0);
            $table->text('notas_cliente')->nullable();
            $table->text('notas_admin')->nullable();
            
            // Datos del comprador (pueden diferir del usuario registrado)
            $table->boolean('usar_datos_usuario')->default(true); // Si usa los datos del perfil
            $table->string('comprador_nombre')->nullable();
            $table->string('comprador_email')->nullable();
            $table->string('comprador_telefono', 15)->nullable();
            $table->enum('comprador_tipo_documento', ['dni', 'ce', 'passport', 'ruc', 'otros'])->nullable();
            $table->string('comprador_numero_documento', 20)->nullable();
            
            // Información de entrega/contacto
            $table->text('direccion_entrega')->nullable(); // Para premios físicos
            $table->string('ciudad_entrega', 100)->nullable();
            $table->string('telefono_contacto', 15)->nullable();
            $table->text('instrucciones_entrega')->nullable();
            
            // Datos de pago
            $table->string('referencia_pago')->nullable();
            $table->decimal('monto_pagado', 12, 2)->nullable();
            $table->string('comprobante_pago')->nullable(); // URL del comprobante
            $table->json('datos_pago_adicionales')->nullable(); // Datos específicos por método de pago
            
            // Seguimiento y auditoría
            $table->string('ip_cliente', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('origen', 50)->nullable(); // web, mobile, admin
            $table->unsignedBigInteger('procesado_por')->nullable(); // Admin que procesó la venta
            $table->datetime('fecha_procesamiento')->nullable();
            
            $table->timestamps();
            
            // Índices optimizados
            $table->index(['user_id', 'estado']);
            $table->index(['rifa_id', 'estado']);
            $table->index(['estado', 'fecha_expiracion']);
            $table->index(['metodo_pago', 'estado']);
            $table->index(['fecha_reserva', 'estado']);
            $table->index(['comprador_tipo_documento', 'comprador_numero_documento']);
            $table->index(['cupon_descuento', 'estado']);
            $table->index(['total', 'estado']);
            $table->index(['procesado_por', 'fecha_procesamiento']);
            $table->index('created_at'); // Para reportes cronológicos
            
            // Relaciones
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('rifa_id')->references('id')->on('rifas')->onDelete('cascade');
            $table->foreign('procesado_por')->references('id')->on('users')->onDelete('set null');
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

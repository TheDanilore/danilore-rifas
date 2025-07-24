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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->string('metodo_pago'); // yape, plin, transferencia, efectivo
            $table->decimal('monto', 12, 2);
            $table->string('referencia_externa')->nullable(); // ID de transacción del banco/app
            $table->string('numero_operacion')->nullable();
            $table->datetime('fecha_transaccion');
            $table->enum('estado', ['pendiente', 'verificado', 'rechazado'])->default('pendiente');
            $table->string('comprobante_url')->nullable(); // URL del comprobante subido
            $table->text('notas_verificacion')->nullable();
            $table->unsignedBigInteger('verificado_por')->nullable(); // ID del admin que verificó
            $table->datetime('fecha_verificacion')->nullable();
            
            // Datos específicos por método de pago
            $table->json('datos_pago')->nullable(); // Para guardar datos específicos según el método
            
            $table->timestamps();
            
            // Índices
            $table->index(['venta_id', 'estado']);
            $table->index(['metodo_pago', 'estado']);
            $table->index('fecha_transaccion');
            
            // Relaciones
            $table->foreign('venta_id')->references('id')->on('ventas')->onDelete('cascade');
            $table->foreign('verificado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

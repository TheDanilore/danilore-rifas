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
        Schema::table('rifas', function (Blueprint $table) {
            // Agregar boletos_maximos
            $table->integer('boletos_maximos')->nullable()->after('boletos_minimos');
            
            // Cambiar enum de estados para que coincida con el frontend
            $table->dropColumn('estado');
        });
        
        Schema::table('rifas', function (Blueprint $table) {
            $table->enum('estado', ['borrador', 'activa', 'pausada', 'finalizada', 'cancelada'])->default('borrador');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rifas', function (Blueprint $table) {
            // Remover boletos_maximos
            $table->dropColumn('boletos_maximos');
            
            // Restaurar enum original
            $table->dropColumn('estado');
        });
        
        Schema::table('rifas', function (Blueprint $table) {
            $table->enum('estado', ['en_venta', 'confirmada', 'sorteada', 'cancelada'])->default('en_venta');
        });
    }
};

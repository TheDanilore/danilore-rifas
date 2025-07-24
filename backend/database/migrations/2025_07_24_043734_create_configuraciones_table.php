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
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 100)->unique();
            $table->text('valor');
            $table->string('tipo', 50)->default('string'); // string, number, boolean, json
            $table->string('grupo', 50)->nullable(); // general, pagos, notificaciones, etc.
            $table->text('descripcion')->nullable();
            $table->boolean('editable')->default(true);
            $table->timestamps();
            
            // Índices
            $table->index('grupo');
            $table->index(['grupo', 'editable']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};

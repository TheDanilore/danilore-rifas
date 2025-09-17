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
        Schema::table('configuraciones', function (Blueprint $table) {
            // Estado de la configuración
            $table->boolean('activa')->default(true)->after('valor');
            
            // Icono para la UI
            $table->string('icono', 50)->nullable()->after('activa');
            
            // Texto de ayuda para el usuario
            $table->text('ayuda')->nullable()->after('icono');
            
            // Configuraciones dependientes (JSON)
            $table->json('dependencias')->nullable()->after('ayuda');
            
            // Orden de visualización
            $table->integer('orden')->default(0)->after('dependencias');
            
            // Grupo de configuración
            $table->string('grupo', 100)->nullable()->after('orden');
            
            // Tipo de input para el frontend
            $table->enum('tipo_input', ['text', 'number', 'email', 'password', 'textarea', 'select', 'checkbox', 'radio', 'file', 'color', 'date'])->default('text')->after('grupo');
            
            // Opciones para select/radio (JSON)
            $table->json('opciones')->nullable()->after('tipo_input');
            
            // Validaciones (JSON)
            $table->json('validaciones')->nullable()->after('opciones');
            
            // Es visible en la UI
            $table->boolean('es_visible')->default(true)->after('validaciones');
            
            // Requiere reinicio de la aplicación
            $table->boolean('requiere_reinicio')->default(false)->after('es_visible');
            
            // Configuración del sistema (no editable por usuario)
            $table->boolean('es_sistema')->default(false)->after('requiere_reinicio');
            
            // Valor por defecto
            $table->text('valor_defecto')->nullable()->after('es_sistema');
            
            // Índices para optimizar consultas
            $table->index(['grupo', 'orden']);
            $table->index(['activa', 'es_visible']);
            $table->index('clave'); // Asegurar que la clave esté indexada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            $table->dropIndex(['clave']);
            $table->dropIndex(['activa', 'es_visible']);
            $table->dropIndex(['grupo', 'orden']);
            
            $table->dropColumn([
                'activa',
                'icono',
                'ayuda',
                'dependencias',
                'orden',
                'grupo',
                'tipo_input',
                'opciones',
                'validaciones',
                'es_visible',
                'requiere_reinicio',
                'es_sistema',
                'valor_defecto'
            ]);
        });
    }
};
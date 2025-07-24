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
        Schema::table('users', function (Blueprint $table) {
            // Campos adicionales para el sistema de rifas
            $table->string('telefono', 15)->nullable()->after('email');
            $table->string('dni', 8)->nullable()->after('telefono');
            $table->date('fecha_nacimiento')->nullable()->after('dni');
            $table->enum('genero', ['masculino', 'femenino', 'otro'])->nullable()->after('fecha_nacimiento');
            $table->string('direccion')->nullable()->after('genero');
            $table->string('ciudad', 100)->nullable()->after('direccion');
            $table->string('departamento', 100)->nullable()->after('ciudad');
            $table->string('codigo_postal', 10)->nullable()->after('departamento');
            
            // Campos para el sistema
            $table->enum('rol', ['admin', 'usuario'])->default('usuario')->after('codigo_postal');
            $table->boolean('activo')->default(true)->after('rol');
            $table->datetime('ultimo_acceso')->nullable()->after('activo');
            $table->string('avatar')->nullable()->after('ultimo_acceso');
            
            // Preferencias de notificaciones
            $table->boolean('notif_email')->default(true)->after('avatar');
            $table->boolean('notif_sms')->default(false)->after('notif_email');
            $table->boolean('notif_whatsapp')->default(true)->after('notif_sms');
            
            // Estadísticas del usuario
            $table->integer('total_boletos_comprados')->default(0)->after('notif_whatsapp');
            $table->decimal('total_gastado', 12, 2)->default(0)->after('total_boletos_comprados');
            $table->integer('rifas_ganadas')->default(0)->after('total_gastado');
            
            // Índices
            $table->index(['rol', 'activo']);
            $table->index('dni');
            $table->index('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'telefono', 'dni', 'fecha_nacimiento', 'genero', 'direccion',
                'ciudad', 'departamento', 'codigo_postal', 'rol', 'activo',
                'ultimo_acceso', 'avatar', 'notif_email', 'notif_sms', 
                'notif_whatsapp', 'total_boletos_comprados', 'total_gastado', 
                'rifas_ganadas'
            ]);
        });
    }
};

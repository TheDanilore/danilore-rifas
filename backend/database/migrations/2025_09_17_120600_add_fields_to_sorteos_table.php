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
        Schema::table('sorteos', function (Blueprint $table) {
            // Transmisión en vivo
            $table->boolean('transmision_en_vivo')->default(false)->after('fecha_programada');
            
            // URL de la transmisión en vivo
            $table->string('url_transmision')->nullable()->after('transmision_en_vivo');
            
            // Plataforma de transmisión
            $table->enum('plataforma_transmision', ['youtube', 'facebook', 'twitch', 'instagram', 'tiktok', 'otra'])->nullable()->after('url_transmision');
            
            // Seed aleatorio para garantizar transparencia
            $table->string('seed_aleatorio')->nullable()->after('plataforma_transmision');
            
            // Método de sorteo utilizado
            $table->enum('metodo_sorteo', ['aleatorio_simple', 'aleatorio_con_seed', 'mersenne_twister', 'cryptographically_secure', 'manual'])->default('aleatorio_simple')->after('seed_aleatorio');
            
            // Algoritmo específico usado
            $table->string('algoritmo_usado', 100)->nullable()->after('metodo_sorteo');
            
            // Hash de verificación del sorteo
            $table->string('hash_verificacion')->nullable()->after('algoritmo_usado');
            
            // Número de intentos del sorteo
            $table->integer('intentos_sorteo')->default(0)->after('hash_verificacion');
            
            // Motivo de repetición (si aplica)
            $table->text('motivo_repeticion')->nullable()->after('intentos_sorteo');
            
            // Es sorteo de prueba
            $table->boolean('es_prueba')->default(false)->after('motivo_repeticion');
            
            // Duración del sorteo en segundos
            $table->integer('duracion_segundos')->nullable()->after('es_prueba');
            
            // Número de espectadores en vivo
            $table->integer('espectadores_en_vivo')->default(0)->after('duracion_segundos');
            
            // Máximo de espectadores alcanzado
            $table->integer('max_espectadores')->default(0)->after('espectadores_en_vivo');
            
            // Configuración del sorteo (JSON)
            $table->json('configuracion_sorteo')->nullable()->after('max_espectadores');
            
            // Log de eventos del sorteo (JSON)
            $table->json('log_eventos')->nullable()->after('configuracion_sorteo');
            
            // Estadísticas de la transmisión (JSON)
            $table->json('estadisticas_transmision')->nullable()->after('log_eventos');
            
            // Zona horaria del sorteo
            $table->string('zona_horaria', 50)->default('America/Lima')->after('estadisticas_transmision');
            
            // Fecha de inicio de transmisión
            $table->timestamp('fecha_inicio_transmision')->nullable()->after('zona_horaria');
            
            // Fecha de fin de transmisión
            $table->timestamp('fecha_fin_transmision')->nullable()->after('fecha_inicio_transmision');
            
            // Personas autorizadas para el sorteo (JSON)
            $table->json('autorizados_sorteo')->nullable()->after('fecha_fin_transmision');
            
            // Certificación del sorteo
            $table->boolean('certificado')->default(false)->after('autorizados_sorteo');
            
            // Entidad certificadora
            $table->string('entidad_certificadora')->nullable()->after('certificado');
            
            // Número de certificación
            $table->string('numero_certificacion')->nullable()->after('entidad_certificadora');
            
            // Comentarios del sorteo para los participantes
            $table->text('comentarios_publicos')->nullable()->after('numero_certificacion');
            
            // Notas internas del organizador
            $table->text('notas_internas')->nullable()->after('comentarios_publicos');
            
            // Índices para optimizar consultas
            $table->index(['transmision_en_vivo', 'fecha_programada']);
            $table->index(['metodo_sorteo', 'es_prueba']);
            $table->index(['certificado', 'fecha_programada']);
            $table->index(['fecha_inicio_transmision', 'fecha_fin_transmision']);
            $table->index('hash_verificacion');
            $table->index('seed_aleatorio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sorteos', function (Blueprint $table) {
            $table->dropIndex(['seed_aleatorio']);
            $table->dropIndex(['hash_verificacion']);
            $table->dropIndex(['fecha_inicio_transmision', 'fecha_fin_transmision']);
            $table->dropIndex(['certificado', 'fecha_programada']);
            $table->dropIndex(['metodo_sorteo', 'es_prueba']);
            $table->dropIndex(['transmision_en_vivo', 'fecha_programada']);
            
            $table->dropColumn([
                'transmision_en_vivo',
                'url_transmision',
                'plataforma_transmision',
                'seed_aleatorio',
                'metodo_sorteo',
                'algoritmo_usado',
                'hash_verificacion',
                'intentos_sorteo',
                'motivo_repeticion',
                'es_prueba',
                'duracion_segundos',
                'espectadores_en_vivo',
                'max_espectadores',
                'configuracion_sorteo',
                'log_eventos',
                'estadisticas_transmision',
                'zona_horaria',
                'fecha_inicio_transmision',
                'fecha_fin_transmision',
                'autorizados_sorteo',
                'certificado',
                'entidad_certificadora',
                'numero_certificacion',
                'comentarios_publicos',
                'notas_internas'
            ]);
        });
    }
};
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
            // Si la rifa es destacada en la UI
            $table->boolean('es_destacada')->default(false)->after('estado');
            
            // Límite de tickets por usuario
            $table->integer('limite_por_usuario')->nullable()->after('es_destacada');
            
            // Código promocional
            $table->string('codigo_promocional', 50)->nullable()->after('limite_por_usuario');
            
            // Descuento promocional (porcentaje)
            $table->decimal('descuento_promocional', 5, 2)->default(0)->after('codigo_promocional');
            
            // Fecha de validez del código promocional
            $table->timestamp('codigo_valido_hasta')->nullable()->after('descuento_promocional');
            
            // Número máximo de usos del código promocional
            $table->integer('codigo_max_usos')->nullable()->after('codigo_valido_hasta');
            
            // Número actual de usos del código promocional
            $table->integer('codigo_usos_actuales')->default(0)->after('codigo_max_usos');
            
            // Configuración de notificaciones (JSON)
            $table->json('configuracion_notificaciones')->nullable()->after('codigo_usos_actuales');
            
            // Etiquetas de la rifa (JSON)
            $table->json('etiquetas')->nullable()->after('configuracion_notificaciones');
            
            // Nivel de dificultad (1-5)
            $table->tinyInteger('nivel_dificultad')->default(1)->after('etiquetas');
            
            // Edad mínima requerida
            $table->integer('edad_minima')->default(18)->after('nivel_dificultad');
            
            // Países permitidos (JSON)
            $table->json('paises_permitidos')->nullable()->after('edad_minima');
            
            // Países restringidos (JSON)
            $table->json('paises_restringidos')->nullable()->after('paises_permitidos');
            
            // Configuración de pagos (JSON)
            $table->json('configuracion_pagos')->nullable()->after('paises_restringidos');
            
            // Comisión de la plataforma (porcentaje)
            $table->decimal('comision_plataforma', 5, 2)->default(5.00)->after('configuracion_pagos');
            
            // Tiempo de reserva de tickets (en minutos)
            $table->integer('tiempo_reserva_tickets')->default(15)->after('comision_plataforma');
            
            // Permitir compra de múltiples tickets
            $table->boolean('permite_multiples_tickets')->default(true)->after('tiempo_reserva_tickets');
            
            // Máximo de tickets por compra
            $table->integer('max_tickets_por_compra')->default(10)->after('permite_multiples_tickets');
            
            // Mostrar estadísticas públicamente
            $table->boolean('mostrar_estadisticas')->default(true)->after('max_tickets_por_compra');
            
            // Permitir comentarios
            $table->boolean('permitir_comentarios')->default(true)->after('mostrar_estadisticas');
            
            // Moderar comentarios antes de publicar
            $table->boolean('moderar_comentarios')->default(false)->after('permitir_comentarios');
            
            // URL de video promocional
            $table->string('video_promocional')->nullable()->after('moderar_comentarios');
            
            // Configuración de redes sociales (JSON)
            $table->json('redes_sociales')->nullable()->after('video_promocional');
            
            // Términos y condiciones específicos
            $table->text('terminos_especificos')->nullable()->after('redes_sociales');
            
            // Política de devoluciones específica
            $table->text('politica_devoluciones')->nullable()->after('terminos_especificos');
            
            // SEO: Meta título
            $table->string('meta_titulo')->nullable()->after('politica_devoluciones');
            
            // SEO: Meta descripción
            $table->text('meta_descripcion')->nullable()->after('meta_titulo');
            
            // SEO: Palabras clave
            $table->string('meta_keywords')->nullable()->after('meta_descripcion');
            
            // Índices para optimizar consultas
            $table->index(['es_destacada', 'fecha_inicio']);
            $table->index(['categoria_id', 'es_destacada']);
            $table->index(['codigo_promocional', 'codigo_valido_hasta']);
            $table->index(['nivel_dificultad']);
            $table->index(['permite_multiples_tickets']);
            $table->index('tiempo_reserva_tickets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rifas', function (Blueprint $table) {
            $table->dropIndex(['tiempo_reserva_tickets']);
            $table->dropIndex(['permite_multiples_tickets']);
            $table->dropIndex(['nivel_dificultad']);
            $table->dropIndex(['codigo_promocional', 'codigo_valido_hasta']);
            $table->dropIndex(['categoria_id', 'es_destacada']);
            $table->dropIndex(['es_destacada', 'fecha_inicio']);
            
            $table->dropColumn([
                'es_destacada',
                'limite_por_usuario',
                'codigo_promocional',
                'descuento_promocional',
                'codigo_valido_hasta',
                'codigo_max_usos',
                'codigo_usos_actuales',
                'configuracion_notificaciones',
                'etiquetas',
                'nivel_dificultad',
                'edad_minima',
                'paises_permitidos',
                'paises_restringidos',
                'configuracion_pagos',
                'comision_plataforma',
                'tiempo_reserva_tickets',
                'permite_multiples_tickets',
                'max_tickets_por_compra',
                'mostrar_estadisticas',
                'permitir_comentarios',
                'moderar_comentarios',
                'video_promocional',
                'redes_sociales',
                'terminos_especificos',
                'politica_devoluciones',
                'meta_titulo',
                'meta_descripcion',
                'meta_keywords'
            ]);
        });
    }
};
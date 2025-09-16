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
        Schema::create('rifas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->text('descripcion_corta')->nullable(); // Para listados
            $table->decimal('precio_boleto', 10, 2);
            $table->integer('boletos_minimos'); // Mínimo para confirmar la rifa
            $table->integer('boletos_maximos')->nullable(); // Máximo permitido
            $table->integer('boletos_vendidos')->default(0);
            $table->integer('boletos_reservados')->default(0); // Reservados temporalmente
            $table->integer('boletos_disponibles')->storedAs('COALESCE(boletos_maximos, 999999) - boletos_vendidos - boletos_reservados');
            
            // Medios y presentación
            $table->string('imagen_principal')->nullable();
            $table->json('imagenes_adicionales')->nullable();
            $table->json('media_gallery')->nullable(); // Para múltiples medios (videos, etc.)
            $table->string('video_presentacion')->nullable(); // URL del video principal
            $table->json('colores_tema')->nullable(); // Colores personalizados para la rifa
            
            // Fechas y tiempos
            $table->datetime('fecha_inicio');
            $table->datetime('fecha_fin');
            $table->datetime('fecha_sorteo');
            $table->integer('tiempo_reserva_minutos')->default(15); // Tiempo para completar el pago
            
            // Estados y configuración
            $table->enum('estado', ['borrador', 'programada', 'activa', 'pausada', 'finalizada', 'cancelada'])->default('borrador');
            $table->enum('tipo', ['actual', 'futura'])->default('futura');
            $table->enum('modalidad', ['tradicional', 'progresiva', 'inmediata'])->default('progresiva');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('codigo_unico', 20)->unique();
            $table->string('slug')->unique(); // Para URLs amigables
            
            // Configuraciones especiales
            $table->boolean('es_destacada')->default(false);
            $table->boolean('es_premium')->default(false);
            $table->boolean('permite_multiples_boletos')->default(true);
            $table->integer('max_boletos_por_persona')->default(10);
            $table->integer('max_boletos_por_transaccion')->default(5);
            $table->boolean('requiere_verificacion_identidad')->default(false);
            $table->boolean('permite_transferencia_boletos')->default(false);
            
            // Términos y condiciones
            $table->text('terminos_condiciones')->nullable();
            $table->text('bases_legales')->nullable();
            $table->text('informacion_sorteo')->nullable();
            
            // Sistema progresivo
            $table->integer('total_premios')->default(0); // Calculado automáticamente
            $table->integer('premios_desbloqueados')->default(0); // Calculado automáticamente
            $table->integer('orden')->default(1); // Para ordenar rifas futuras
            $table->unsignedBigInteger('rifa_requerida_id')->nullable(); // Para rifas que dependen de otras
            $table->decimal('porcentaje_completado_general', 5, 2)->default(0); // Progreso general
            
            // Estadísticas y métricas
            $table->integer('vistas')->default(0);
            $table->integer('favoritos')->default(0);
            $table->integer('compartidas')->default(0);
            $table->decimal('rating_promedio', 3, 2)->default(0); // 0.00 - 5.00
            $table->integer('total_comentarios')->default(0);
            
            // Configuración financiera
            $table->decimal('comision_plataforma', 5, 2)->default(10.00); // Porcentaje
            $table->decimal('total_recaudado', 12, 2)->default(0);
            $table->decimal('total_comisiones', 12, 2)->default(0);
            
            // Administración
            $table->unsignedBigInteger('creado_por')->nullable(); // Usuario administrador
            $table->text('notas_admin')->nullable();
            $table->json('configuracion_avanzada')->nullable(); // Configuraciones específicas
            $table->boolean('visible_publico')->default(true);
            $table->datetime('fecha_publicacion')->nullable();
            
            $table->timestamps();
            
            // Índices optimizados
            $table->index(['estado', 'tipo', 'fecha_inicio']);
            $table->index(['tipo', 'orden']);
            $table->index('categoria_id');
            $table->index(['es_destacada', 'es_premium']);
            $table->index(['visible_publico', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index(['modalidad', 'estado']);
            $table->index(['boletos_vendidos', 'boletos_minimos']);
            $table->index(['creado_por', 'estado']);
            $table->index('slug');
            $table->index(['porcentaje_completado_general', 'estado']);
            
            // Relaciones
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('rifa_requerida_id')->references('id')->on('rifas')->onDelete('set null');
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rifas');
    }
};

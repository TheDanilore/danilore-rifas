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
            $table->datetime('fecha_limite_pago')->nullable(); // Fecha límite para completar pagos pendientes
            $table->datetime('fecha_sorteo');
            $table->datetime('fecha_ultimo_sorteo')->nullable(); // Para rifas con múltiples sorteos
            $table->integer('tiempo_reserva_minutos')->default(15); // Tiempo para completar el pago
            $table->integer('dias_entrega_premio')->default(30); // Días máximos para entregar premio

            // Estados y configuración
            $table->enum('estado', ['borrador', 'programada', 'activa', 'pausada', 'finalizada', 'cancelada', 'completada'])->default('borrador');
            $table->enum('tipo', ['actual', 'futura'])->default('futura');
            $table->enum('modalidad', ['tradicional', 'progresiva', 'inmediata', 'rapida'])->default('progresiva');
            $table->enum('tipo_sorteo', ['aleatorio_simple', 'loteria_nacional', 'streaming_vivo', 'manual'])->default('aleatorio_simple');
            $table->enum('metodo_pago_preferido', ['yape', 'plin', 'transferencia', 'efectivo', 'cualquiera'])->default('cualquiera');
            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->string('codigo_unico', 20)->unique();
            $table->string('slug')->unique(); // Para URLs amigables

            // Configuraciones especiales
            $table->boolean('es_premium')->default(false);
            $table->boolean('es_patrocinada')->default(false); // Para rifas con patrocinio
            $table->boolean('permite_multiples_boletos')->default(true);
            $table->boolean('permite_seleccion_numeros')->default(false); // Si usuarios pueden elegir números
            $table->boolean('permite_compra_grupos')->default(false); // Compra en grupo con descuentos
            $table->integer('max_boletos_por_persona')->default(10);
            $table->integer('max_boletos_por_transaccion')->default(5);
            $table->integer('min_participantes')->default(1); // Mínimo de participantes únicos
            $table->boolean('requiere_verificacion_identidad')->default(false);
            $table->boolean('permite_transferencia_boletos')->default(false);
            $table->boolean('auto_sorteo_al_completar')->default(true); // Sorteo automático al completar boletos

            // Términos y condiciones
            $table->text('terminos_condiciones')->nullable();
            $table->text('bases_legales')->nullable();
            $table->text('informacion_sorteo')->nullable();

            // Sistema progresivo
            $table->integer('total_premios')->default(0); // Calculado automáticamente
            $table->integer('premios_desbloqueados')->default(0); // Calculado automáticamente
            $table->integer('premios_entregados')->default(0); // Premios ya entregados
            $table->integer('orden')->default(1); // Para ordenar rifas futuras
            $table->unsignedBigInteger('rifa_requerida_id')->nullable(); // Para rifas que dependen de otras
            $table->decimal('porcentaje_completado_general', 5, 2)->default(0); // Progreso general
            $table->decimal('porcentaje_minimo_activacion', 5, 2)->default(0); // % mínimo para activar
            $table->boolean('sistema_progresivo_activo')->default(true); // Si usa sistema progresivo
            $table->json('reglas_progresion')->nullable(); // Reglas específicas de progresión

            // Estadísticas y métricas
            $table->integer('vistas')->default(0);
            $table->integer('vistas_unicas')->default(0); // Visitantes únicos
            $table->integer('favoritos')->default(0);
            $table->integer('compartidas')->default(0);
            $table->integer('clics_compartir')->default(0); // Clics en botones de compartir
            $table->decimal('rating_promedio', 3, 2)->default(0); // 0.00 - 5.00
            $table->integer('total_comentarios')->default(0);
            $table->integer('total_participantes_unicos')->default(0); // Usuarios únicos que compraron
            $table->decimal('tasa_conversion', 5, 2)->default(0); // % de visitantes que compran

            // Configuración financiera
            $table->decimal('total_recaudado', 12, 2)->default(0);
            $table->decimal('total_comisiones', 12, 2)->default(0);
            $table->decimal('total_neto_organizador', 12, 2)->default(0); // Lo que recibe el organizador
            $table->decimal('precio_minimo_garantizado', 12, 2)->nullable(); // Precio mínimo garantizado
            $table->decimal('bono_participacion', 10, 2)->default(0); // Bono por participar
            $table->json('estructura_precios')->nullable(); // Precios escalonados por cantidad

            // Administración y control
            $table->unsignedBigInteger('creado_por')->nullable(); // Usuario administrador
            $table->unsignedBigInteger('organizador_id')->nullable(); // Usuario organizador (diferente del creador)
            $table->text('notas_admin')->nullable();
            $table->text('notas_organizador')->nullable(); // Notas del organizador
            $table->json('configuracion_avanzada')->nullable(); // Configuraciones específicas
            $table->boolean('visible_publico')->default(true);
            $table->boolean('destacar_en_inicio')->default(false); // Para página principal
            $table->boolean('enviar_notificaciones')->default(true); // Si enviar notificaciones automáticas
            $table->datetime('fecha_publicacion')->nullable();
            $table->datetime('fecha_moderacion')->nullable(); // Cuándo fue moderada
            $table->unsignedBigInteger('moderado_por')->nullable(); // Quién la moderó
            $table->string('razon_suspension', 500)->nullable(); // Razón si fue suspendida
            $table->json('configuracion_seo')->nullable(); // Configuraciones SEO específicas

            $table->timestamps();

            // Índices optimizados
            $table->index(['estado', 'tipo', 'fecha_inicio']);
            $table->index(['tipo', 'orden']);
            $table->index('categoria_id');
            $table->index(['es_premium']);
            $table->index(['visible_publico', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index(['modalidad', 'estado']);
            $table->index(['boletos_vendidos', 'boletos_minimos']);
            $table->index(['creado_por', 'estado']);
            $table->index(['organizador_id', 'estado']);
            $table->index('slug');
            $table->index(['porcentaje_completado_general', 'estado']);
            $table->index(['destacar_en_inicio', 'visible_publico']);
            $table->index(['es_patrocinada', 'estado']);
            $table->index(['tipo_sorteo', 'fecha_sorteo']);
            $table->index(['fecha_limite_pago', 'estado']);
            $table->index(['sistema_progresivo_activo', 'modalidad']);
            $table->index(['total_participantes_unicos', 'estado']);
            $table->index(['vistas', 'created_at']); // Para rifas populares
            $table->index(['precio_boleto', 'categoria_id']); // Para filtros de precio

            // Relaciones
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('set null');
            $table->foreign('rifa_requerida_id')->references('id')->on('rifas')->onDelete('set null');
            $table->foreign('creado_por')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organizador_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('moderado_por')->references('id')->on('users')->onDelete('set null');
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RifasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías para asignar a las rifas
        $categoriaApple = DB::table('categorias')->where('slug', 'apple')->first();
        $categoriaTecnologia = DB::table('categorias')->where('slug', 'tecnologia')->first();
        $categoriaAutomoviles = DB::table('categorias')->where('slug', 'automoviles')->first();

        // Rifa progresiva: iPhone 15 Pro Max (ACTIVA con 500 tickets vendidos)
        DB::table('rifas')->updateOrInsert(
            ['codigo_unico' => 'IPHONE15PM001'],
            [
                'titulo' => 'iPhone 15 Pro Max 1TB',
                'slug' => 'iphone-15-pro-max-1tb',
                'descripcion' => 'Gana el nuevo iPhone 15 Pro Max con 1TB de almacenamiento. Sistema de premios progresivos donde cada nivel se desbloquea con la participación de la comunidad.',
                'precio_boleto' => 10.00,
                'boletos_minimos' => 700,
                'boletos_maximos' => 2000,
                'boletos_vendidos' => 500, // ✅ Actualizado - Ya hay participación
                'imagen_principal' => '/images/premios/iphone-15-pro-max-rifa.jpg',
                'media_gallery' => json_encode([
                    '/images/rifas/iphone-15-pro-max-1.jpg',
                    '/images/rifas/iphone-15-pro-max-2.jpg',
                    '/images/rifas/iphone-15-pro-max-3.jpg',
                    '/images/rifas/iphone-15-pro-max-4.jpg'
                ]),
                'categoria_id' => $categoriaApple->id,
                'modalidad' => 'progresiva',
                'estado' => 'activa',
                'tipo_sorteo' => 'aleatorio_simple',
                'metodo_pago_preferido' => 'yape',
                'es_destacada' => true,
                'es_premium' => true,
                'es_patrocinada' => false,
                'auto_sorteo_al_completar' => true,
                'sistema_progresivo_activo' => true,
                'visible_publico' => true,
                'destacar_en_inicio' => true,
                'enviar_notificaciones' => true,
                'fecha_inicio' => now()->subDays(10),
                'fecha_fin' => now()->addDays(30),
                'fecha_limite_pago' => now()->addDays(32),
                'fecha_sorteo' => now()->addDays(35),
                'rifa_requerida_id' => null, // Es independiente
                'permite_transferencia_boletos' => true,
                'permite_seleccion_numeros' => false,
                'min_participantes' => 50,
                'comision_plataforma' => 5.0,
                'total_participantes_unicos' => 45, // Simulando participantes actuales
                'vistas' => 1250,
                'vistas_unicas' => 890,
                'configuracion_avanzada' => json_encode([
                    'mostrar_progreso' => true,
                    'permitir_comentarios' => true,
                    'notificar_nuevos_niveles' => true
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Rifa futura: MacBook Pro (BLOQUEADA - se activa después del iPhone)
        DB::table('rifas')->updateOrInsert(
            ['codigo_unico' => 'MACBOOKM3001'],
            [
                'titulo' => 'MacBook Pro M3 16" 1TB',
                'slug' => 'macbook-pro-m3-16-1tb',
                'descripcion' => 'La nueva MacBook Pro con chip M3, 16 pulgadas de pantalla Liquid Retina XDR y 1TB de almacenamiento SSD. Sistema progresivo de premios.',
                'precio_boleto' => 15.00,
                'boletos_minimos' => 1000,
                'boletos_maximos' => 3000,
                'boletos_vendidos' => 0,
                'imagen_principal' => '/images/premios/macbook-pro-m3-rifa.jpg',
                'media_gallery' => json_encode([
                    '/images/rifas/macbook-pro-m3-1.jpg',
                    '/images/rifas/macbook-pro-m3-2.jpg',
                    '/images/rifas/macbook-pro-m3-3.jpg'
                ]),
                'categoria_id' => $categoriaApple->id,
                'modalidad' => 'progresiva',
                'estado' => 'programada', // ✅ Se activa cuando se complete el iPhone
                'tipo_sorteo' => 'aleatorio_simple',
                'metodo_pago_preferido' => 'yape',
                'es_destacada' => true,
                'es_premium' => true,
                'es_patrocinada' => true,
                'auto_sorteo_al_completar' => true,
                'sistema_progresivo_activo' => true,
                'visible_publico' => true,
                'destacar_en_inicio' => false,
                'enviar_notificaciones' => true,
                'fecha_inicio' => now()->addDays(40),
                'fecha_fin' => now()->addDays(90),
                'fecha_limite_pago' => now()->addDays(92),
                'fecha_sorteo' => now()->addDays(95),
                'rifa_requerida_id' => null, // Se configurará dinámicamente
                'permite_transferencia_boletos' => true,
                'permite_seleccion_numeros' => false,
                'min_participantes' => 80,
                'comision_plataforma' => 7.0,
                'total_participantes_unicos' => 0, // Aún no iniciada
                'vistas' => 320,
                'vistas_unicas' => 250,
                'configuracion_avanzada' => json_encode([
                    'mostrar_progreso' => true,
                    'permitir_comentarios' => true,
                    'notificar_nuevos_niveles' => true
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Rifa simple: Tesla Model 3 (INDEPENDIENTE)
        DB::table('rifas')->updateOrInsert(
            ['codigo_unico' => 'TESLA2025001'],
            [
                'titulo' => 'Tesla Model 3 2025',
                'slug' => 'tesla-model-3-2025',
                'descripcion' => 'Tesla Model 3 último modelo con autopilot avanzado y 500km de autonomía. Rifa simple con sorteo directo.',
                'precio_boleto' => 50.00,
                'boletos_minimos' => 2000,
                'boletos_maximos' => 5000,
                'boletos_vendidos' => 150,
                'imagen_principal' => '/images/premios/tesla-model-3-2025.jpg',
                'media_gallery' => json_encode([
                    '/images/rifas/tesla-model-3-1.jpg',
                    '/images/rifas/tesla-model-3-2.jpg',
                    '/images/rifas/tesla-model-3-3.jpg',
                    '/images/rifas/tesla-model-3-4.jpg'
                ]),
                'categoria_id' => $categoriaAutomoviles->id,
                'modalidad' => 'tradicional',
                'estado' => 'activa',
                'tipo_sorteo' => 'loteria_nacional',
                'metodo_pago_preferido' => 'transferencia',
                'es_destacada' => true,
                'es_premium' => true,
                'es_patrocinada' => false,
                'auto_sorteo_al_completar' => false,
                'sistema_progresivo_activo' => false,
                'visible_publico' => true,
                'destacar_en_inicio' => true,
                'enviar_notificaciones' => true,
                'fecha_inicio' => now()->subDays(5),
                'fecha_fin' => now()->addDays(60),
                'fecha_limite_pago' => now()->addDays(62),
                'fecha_sorteo' => now()->addDays(65),
                'rifa_requerida_id' => null,
                'permite_transferencia_boletos' => false,
                'permite_seleccion_numeros' => true,
                'min_participantes' => 100,
                'comision_plataforma' => 10.0,
                'total_participantes_unicos' => 23, // Participantes actuales
                'vistas' => 450,
                'vistas_unicas' => 380,
                'configuracion_avanzada' => json_encode([
                    'mostrar_progreso' => false,
                    'permitir_comentarios' => true,
                    'verificacion_identidad' => true
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Obtener los IDs para mostrar en el mensaje
        $rifaIphone = DB::table('rifas')->where('codigo_unico', 'IPHONE15PM001')->first();
        $rifaMacbook = DB::table('rifas')->where('codigo_unico', 'MACBOOKM3001')->first();

        echo "✅ Rifas seeders ejecutados correctamente\n";
        echo "   - Rifa iPhone 15 Pro Max (ID: {$rifaIphone->id}) - Progresiva/Actual\n";
        echo "   - Rifa MacBook Pro M3 (ID: {$rifaMacbook->id}) - Progresiva/Futura\n";
        echo "   - Rifa Tesla Model 3 - Simple/Actual\n";
    }
}

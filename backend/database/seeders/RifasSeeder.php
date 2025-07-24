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
        // Rifa progresiva: iPhone 15 Pro Max
        $rifaIphone = DB::table('rifas')->insertGetId([
            'titulo' => 'iPhone 15 Pro Max 1TB',
            'descripcion' => 'Gana el nuevo iPhone 15 Pro Max con 1TB de almacenamiento. Sistema de premios progresivos donde cada nivel se desbloquea con la participación de la comunidad.',
            'precio_boleto' => 10.00,
            'boletos_minimos' => 1000,
            'boletos_vendidos' => 0,
            'imagen_principal' => '/images/rifas/iphone-15-pro-max.jpg',
            'imagenes_adicionales' => json_encode([
                '/images/rifas/iphone-15-pro-max-2.jpg',
                '/images/rifas/iphone-15-pro-max-3.jpg'
            ]),
            'media_gallery' => json_encode([
                ['type' => 'image', 'url' => '/images/rifas/iphone-15-pro-max.jpg'],
                ['type' => 'image', 'url' => '/images/rifas/iphone-15-pro-max-2.jpg'],
                ['type' => 'image', 'url' => '/images/rifas/iphone-15-pro-max-3.jpg']
            ]),
            'fecha_inicio' => Carbon::now()->format('Y-m-d'),
            'fecha_fin' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'fecha_sorteo' => Carbon::now()->addDays(32)->format('Y-m-d H:i:s'),
            'estado' => 'en_venta',
            'tipo' => 'actual',
            'categoria_id' => null,
            'codigo_unico' => 'IPHONE15PM001',
            'es_destacada' => true,
            'max_boletos_por_persona' => 50,
            'terminos_condiciones' => 'Sorteo válido solo en territorio nacional. Mayor de edad. Un solo premio por persona.',
            'orden' => 1,
            'rifa_requerida_id' => null,
            'notas_admin' => 'Rifa progresiva con múltiples premios',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rifa futura: MacBook Pro
        $rifaMacbook = DB::table('rifas')->insertGetId([
            'titulo' => 'MacBook Pro M3 16" 1TB',
            'descripcion' => 'La nueva MacBook Pro con chip M3, perfecta para profesionales y creadores de contenido. Sistema de premios progresivos con múltiples niveles de participación.',
            'precio_boleto' => 15.00,
            'boletos_minimos' => 800,
            'boletos_vendidos' => 0,
            'imagen_principal' => '/images/rifas/macbook-pro-m3.jpg',
            'imagenes_adicionales' => json_encode([
                '/images/rifas/macbook-pro-m3-2.jpg',
                '/images/rifas/macbook-pro-m3-3.jpg'
            ]),
            'media_gallery' => json_encode([
                ['type' => 'image', 'url' => '/images/rifas/macbook-pro-m3.jpg'],
                ['type' => 'image', 'url' => '/images/rifas/macbook-pro-m3-2.jpg'],
                ['type' => 'image', 'url' => '/images/rifas/macbook-pro-m3-3.jpg']
            ]),
            'fecha_inicio' => Carbon::now()->addDays(35)->format('Y-m-d'),
            'fecha_fin' => Carbon::now()->addDays(65)->format('Y-m-d'),
            'fecha_sorteo' => Carbon::now()->addDays(67)->format('Y-m-d H:i:s'),
            'estado' => 'en_venta',
            'tipo' => 'futura',
            'categoria_id' => null,
            'codigo_unico' => 'MACBOOKM3001',
            'es_destacada' => true,
            'max_boletos_por_persona' => 40,
            'terminos_condiciones' => 'Sorteo válido solo en territorio nacional. Mayor de edad. Un solo premio por persona.',
            'orden' => 2,
            'rifa_requerida_id' => $rifaIphone,
            'notas_admin' => 'Rifa progresiva que se activa después del iPhone',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Rifa simple: Tesla Model 3
        DB::table('rifas')->insert([
            'titulo' => 'Tesla Model 3 Standard Range',
            'descripcion' => 'Vehículo eléctrico Tesla Model 3 con todas las características de lujo y tecnología avanzada.',
            'precio_boleto' => 50.00,
            'boletos_minimos' => 1000,
            'boletos_vendidos' => 0,
            'imagen_principal' => '/images/rifas/tesla-model-3.jpg',
            'imagenes_adicionales' => json_encode([
                '/images/rifas/tesla-model-3-2.jpg',
                '/images/rifas/tesla-model-3-3.jpg'
            ]),
            'media_gallery' => json_encode([
                ['type' => 'image', 'url' => '/images/rifas/tesla-model-3.jpg'],
                ['type' => 'image', 'url' => '/images/rifas/tesla-model-3-2.jpg'],
                ['type' => 'image', 'url' => '/images/rifas/tesla-model-3-3.jpg']
            ]),
            'fecha_inicio' => Carbon::now()->format('Y-m-d'),
            'fecha_fin' => Carbon::now()->addDays(45)->format('Y-m-d'),
            'fecha_sorteo' => Carbon::now()->addDays(47)->format('Y-m-d H:i:s'),
            'estado' => 'en_venta',
            'tipo' => 'actual',
            'categoria_id' => null,
            'codigo_unico' => 'TESLA3001',
            'es_destacada' => false,
            'max_boletos_por_persona' => 20,
            'terminos_condiciones' => 'Sorteo válido solo en territorio nacional. Mayor de edad. Licencia de conducir vigente.',
            'orden' => 3,
            'rifa_requerida_id' => null,
            'notas_admin' => 'Rifa simple con un solo premio',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        echo "✅ Rifas seeders ejecutados correctamente\n";
        echo "   - Rifa iPhone 15 Pro Max (ID: {$rifaIphone}) - Progresiva/Actual\n";
        echo "   - Rifa MacBook Pro M3 (ID: {$rifaMacbook}) - Progresiva/Futura\n";
        echo "   - Rifa Tesla Model 3 - Simple/Actual\n";
    }
}

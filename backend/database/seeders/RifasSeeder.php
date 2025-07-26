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
            'boletos_maximos' => 2000,
            'boletos_vendidos' => 0,
            'imagen_principal' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&h=400&fit=crop&crop=center',
            'imagenes_adicionales' => json_encode([
                'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&h=400&fit=crop&crop=center',
                'https://images.unsplash.com/photo-1603921326210-6edd2d60ca68?w=600&h=400&fit=crop&crop=center'
            ]),
            'media_gallery' => json_encode([
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&h=400&fit=crop&crop=center'],
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1603921326210-6edd2d60ca68?w=600&h=400&fit=crop&crop=center'],
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=600&h=400&fit=crop&crop=center']
            ]),
            'fecha_inicio' => Carbon::now()->format('Y-m-d'),
            'fecha_fin' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'fecha_sorteo' => Carbon::now()->addDays(32)->format('Y-m-d H:i:s'),
            'estado' => 'activa',
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
            'boletos_maximos' => 1600,
            'boletos_vendidos' => 0,
            'imagen_principal' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&h=400&fit=crop&crop=center',
            'imagenes_adicionales' => json_encode([
                'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&h=400&fit=crop&crop=center',
                'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=600&h=400&fit=crop&crop=center'
            ]),
            'media_gallery' => json_encode([
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600&h=400&fit=crop&crop=center'],
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=600&h=400&fit=crop&crop=center'],
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1484788984921-03950022c9ef?w=600&h=400&fit=crop&crop=center']
            ]),
            'fecha_inicio' => Carbon::now()->addDays(35)->format('Y-m-d'),
            'fecha_fin' => Carbon::now()->addDays(65)->format('Y-m-d'),
            'fecha_sorteo' => Carbon::now()->addDays(67)->format('Y-m-d H:i:s'),
            'estado' => 'activa',
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
            'boletos_maximos' => 2500,
            'boletos_vendidos' => 0,
            'imagen_principal' => 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=600&h=400&fit=crop&crop=center',
            'imagenes_adicionales' => json_encode([
                'https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=600&h=400&fit=crop&crop=center',
                'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center'
            ]),
            'media_gallery' => json_encode([
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=600&h=400&fit=crop&crop=center'],
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop&crop=center'],
                ['type' => 'image', 'url' => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=600&h=400&fit=crop&crop=center']
            ]),
            'fecha_inicio' => Carbon::now()->format('Y-m-d'),
            'fecha_fin' => Carbon::now()->addDays(45)->format('Y-m-d'),
            'fecha_sorteo' => Carbon::now()->addDays(47)->format('Y-m-d H:i:s'),
            'estado' => 'activa',
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

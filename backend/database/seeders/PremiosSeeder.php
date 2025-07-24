<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PremiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener las rifas progresivas
        $rifaIphone = DB::table('rifas')->where('codigo_unico', 'IPHONE15PM001')->first();
        $rifaMacbook = DB::table('rifas')->where('codigo_unico', 'MACBOOKM3001')->first();

        if ($rifaIphone) {
            // Premio 1: AirPods Pro (iPhone)
            $premio1 = DB::table('premios')->insertGetId([
                'rifa_id' => $rifaIphone->id,
                'codigo' => 'p1',
                'titulo' => 'AirPods Pro 2da Gen',
                'descripcion' => 'Los nuevos AirPods Pro con cancelación activa de ruido y audio espacial personalizado.',
                'imagen_principal' => '/images/premios/airpods-pro-gen2.jpg',
                'media_gallery' => json_encode([
                    '/images/premios/airpods-pro-gen2-1.jpg',
                    '/images/premios/airpods-pro-gen2-2.jpg',
                    '/images/premios/airpods-pro-gen2-3.jpg'
                ]),
                'orden' => 1,
                'premio_requerido_id' => null,
                'estado' => 'activo',
                'desbloqueado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Premio 2: iPad Air (iPhone)
            $premio2 = DB::table('premios')->insertGetId([
                'rifa_id' => $rifaIphone->id,
                'codigo' => 'p2',
                'titulo' => 'iPad Air M2 256GB',
                'descripcion' => 'iPad Air con chip M2, pantalla Liquid Retina de 10.9 pulgadas y compatibilidad con Apple Pencil.',
                'imagen_principal' => '/images/premios/ipad-air-m2.jpg',
                'media_gallery' => json_encode([
                    '/images/premios/ipad-air-m2-1.jpg',
                    '/images/premios/ipad-air-m2-2.jpg',
                    '/images/premios/ipad-air-m2-3.jpg'
                ]),
                'orden' => 2,
                'premio_requerido_id' => $premio1,
                'estado' => 'bloqueado',
                'desbloqueado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Premio 3: iPhone 15 Pro Max (iPhone) - Premio final
            DB::table('premios')->insert([
                'rifa_id' => $rifaIphone->id,
                'codigo' => 'p3',
                'titulo' => 'iPhone 15 Pro Max 1TB',
                'descripcion' => 'El iPhone más avanzado con chip A17 Pro, sistema de cámaras Pro de 48MP y pantalla Super Retina XDR de 6.7 pulgadas.',
                'imagen_principal' => '/images/premios/iphone-15-pro-max-final.jpg',
                'media_gallery' => json_encode([
                    '/images/premios/iphone-15-pro-max-final-1.jpg',
                    '/images/premios/iphone-15-pro-max-final-2.jpg',
                    '/images/premios/iphone-15-pro-max-final-3.jpg',
                    '/images/premios/iphone-15-pro-max-final-4.jpg'
                ]),
                'orden' => 3,
                'premio_requerido_id' => $premio2,
                'estado' => 'bloqueado',
                'desbloqueado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($rifaMacbook) {
            // Premio 1: Magic Keyboard (MacBook)
            $premioMac1 = DB::table('premios')->insertGetId([
                'rifa_id' => $rifaMacbook->id,
                'codigo' => 'p1',
                'titulo' => 'Magic Keyboard con Touch ID',
                'descripcion' => 'Magic Keyboard inalámbrico con Touch ID y teclado numérico para un flujo de trabajo eficiente.',
                'imagen_principal' => '/images/premios/magic-keyboard-touchid.jpg',
                'media_gallery' => json_encode([
                    '/images/premios/magic-keyboard-touchid-1.jpg',
                    '/images/premios/magic-keyboard-touchid-2.jpg',
                    '/images/premios/magic-keyboard-touchid-3.jpg'
                ]),
                'orden' => 1,
                'premio_requerido_id' => null,
                'estado' => 'activo',
                'desbloqueado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Premio 2: Studio Display (MacBook)
            $premioMac2 = DB::table('premios')->insertGetId([
                'rifa_id' => $rifaMacbook->id,
                'codigo' => 'p2',
                'titulo' => 'Studio Display 27"',
                'descripcion' => 'Monitor Studio Display de 27 pulgadas con pantalla Retina 5K, cámara Ultra Wide y audio espacial.',
                'imagen_principal' => '/images/premios/studio-display-27.jpg',
                'media_gallery' => json_encode([
                    '/images/premios/studio-display-27-1.jpg',
                    '/images/premios/studio-display-27-2.jpg',
                    '/images/premios/studio-display-27-3.jpg'
                ]),
                'orden' => 2,
                'premio_requerido_id' => $premioMac1,
                'estado' => 'bloqueado',
                'desbloqueado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Premio 3: MacBook Pro M3 (MacBook) - Premio final
            DB::table('premios')->insert([
                'rifa_id' => $rifaMacbook->id,
                'codigo' => 'p3',
                'titulo' => 'MacBook Pro M3 16" 1TB',
                'descripcion' => 'MacBook Pro de 16 pulgadas con chip M3, 1TB SSD y 18GB de memoria unificada. Perfecta para profesionales creativos.',
                'imagen_principal' => '/images/premios/macbook-pro-m3-final.jpg',
                'media_gallery' => json_encode([
                    '/images/premios/macbook-pro-m3-final-1.jpg',
                    '/images/premios/macbook-pro-m3-final-2.jpg',
                    '/images/premios/macbook-pro-m3-final-3.jpg',
                    '/images/premios/macbook-pro-m3-final-4.jpg'
                ]),
                'orden' => 3,
                'premio_requerido_id' => $premioMac2,
                'estado' => 'bloqueado',
                'desbloqueado' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "✅ Premios seeders ejecutados correctamente\n";
        echo "   - 3 premios para rifa iPhone (p1: AirPods, p2: iPad, p3: iPhone)\n";
        echo "   - 3 premios para rifa MacBook (p1: Magic Keyboard, p2: Studio Display, p3: MacBook)\n";
    }
}

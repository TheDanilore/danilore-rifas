<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Tecnología',
                'slug' => 'tecnologia',
                'descripcion' => 'Smartphones, tablets, laptops y gadgets tecnológicos',
                'icono' => 'fas fa-mobile-alt',
                'color' => '#3B82F6',
                'activa' => true,
                'orden' => 1,
            ],
            [
                'nombre' => 'Gaming',
                'slug' => 'gaming',
                'descripcion' => 'Consolas, videojuegos y accesorios gaming',
                'icono' => 'fas fa-gamepad',
                'color' => '#8B5CF6',
                'activa' => true,
                'orden' => 2,
            ],
            [
                'nombre' => 'Apple',
                'slug' => 'apple',
                'descripcion' => 'Productos del ecosistema Apple: iPhone, iPad, MacBook, Apple Watch',
                'icono' => 'fab fa-apple',
                'color' => '#374151',
                'activa' => true,
                'orden' => 3,
            ],
            [
                'nombre' => 'Electrodomésticos',
                'slug' => 'electrodomesticos',
                'descripcion' => 'Electrodomésticos para el hogar y cocina',
                'icono' => 'fas fa-home',
                'color' => '#10B981',
                'activa' => true,
                'orden' => 4,
            ],
            [
                'nombre' => 'Belleza',
                'slug' => 'belleza',
                'descripcion' => 'Perfumes, maquillaje y productos de belleza',
                'icono' => 'fas fa-heart',
                'color' => '#EC4899',
                'activa' => true,
                'orden' => 5,
            ],
            [
                'nombre' => 'Deportes',
                'slug' => 'deportes',
                'descripcion' => 'Equipamiento deportivo y ropa deportiva',
                'icono' => 'fas fa-dumbbell',
                'color' => '#F59E0B',
                'activa' => true,
                'orden' => 6,
            ],
            [
                'nombre' => 'Automóviles',
                'slug' => 'automoviles',
                'descripcion' => 'Vehículos, motos y accesorios automotrices',
                'icono' => 'fas fa-car',
                'color' => '#EF4444',
                'activa' => true,
                'orden' => 7,
            ],
            [
                'nombre' => 'Viajes',
                'slug' => 'viajes',
                'descripcion' => 'Paquetes turísticos, vuelos y experiencias de viaje',
                'icono' => 'fas fa-plane',
                'color' => '#06B6D4',
                'activa' => true,
                'orden' => 8,
            ],
            [
                'nombre' => 'Hogar y Jardín',
                'slug' => 'hogar-jardin',
                'descripcion' => 'Muebles, decoración y artículos para jardín',
                'icono' => 'fas fa-couch',
                'color' => '#84CC16',
                'activa' => true,
                'orden' => 9,
            ],
            [
                'nombre' => 'Efectivo',
                'slug' => 'efectivo',
                'descripcion' => 'Premios en dinero en efectivo',
                'icono' => 'fas fa-dollar-sign',
                'color' => '#059669',
                'activa' => true,
                'orden' => 10,
            ],
        ];

        foreach ($categorias as $categoria) {
            DB::table('categorias')->updateOrInsert(
                ['slug' => $categoria['slug']],
                array_merge($categoria, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Categorías creadas exitosamente:');
        $this->command->info('- 10 categorías principales para rifas');
        $this->command->info('- Tecnología, Gaming, Apple, Electrodomésticos, etc.');
    }
}
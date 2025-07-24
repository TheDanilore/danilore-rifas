<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los premios
        $premios = DB::table('premios')->get();

        foreach ($premios as $premio) {
            // Determinar tickets necesarios basado en el orden del premio
            $ticketsBase = match ($premio->orden) {
                1 => 100,  // Primer premio: 100 tickets
                2 => 250,  // Segundo premio: 250 tickets
                3 => 500,  // Tercer premio: 500 tickets
                default => 100
            };

            // Nivel 1: Básico
            DB::table('niveles')->insert([
                'premio_id' => $premio->id,
                'codigo' => 'n1',
                'titulo' => 'Nivel Básico',
                'descripcion' => 'Acceso básico al premio con especificaciones estándar.',
                'tickets_necesarios' => $ticketsBase,
                'valor_aproximado' => $this->getValorNivel($premio->codigo, 1),
                'imagen' => "/images/niveles/{$premio->codigo}-n1.jpg",
                'orden' => 1,
                'desbloqueado' => false,
                'es_actual' => true, // El primer nivel siempre es el actual al inicio
                'fecha_desbloqueo' => null,
                'especificaciones' => json_encode($this->getEspecificaciones($premio->codigo, 1)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Nivel 2: Premium
            DB::table('niveles')->insert([
                'premio_id' => $premio->id,
                'codigo' => 'n2',
                'titulo' => 'Nivel Premium',
                'descripcion' => 'Versión mejorada con características adicionales y mayor valor.',
                'tickets_necesarios' => $ticketsBase * 2,
                'valor_aproximado' => $this->getValorNivel($premio->codigo, 2),
                'imagen' => "/images/niveles/{$premio->codigo}-n2.jpg",
                'orden' => 2,
                'desbloqueado' => false,
                'es_actual' => false,
                'fecha_desbloqueo' => null,
                'especificaciones' => json_encode($this->getEspecificaciones($premio->codigo, 2)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Nivel 3: Elite (solo para premios finales)
            if ($premio->orden == 3) {
                DB::table('niveles')->insert([
                    'premio_id' => $premio->id,
                    'codigo' => 'n3',
                    'titulo' => 'Nivel Elite',
                    'descripcion' => 'La versión más exclusiva con todos los accesorios y características premium.',
                    'tickets_necesarios' => $ticketsBase * 3,
                    'valor_aproximado' => $this->getValorNivel($premio->codigo, 3),
                    'imagen' => "/images/niveles/{$premio->codigo}-n3.jpg",
                    'orden' => 3,
                    'desbloqueado' => false,
                    'es_actual' => false,
                    'fecha_desbloqueo' => null,
                    'especificaciones' => json_encode($this->getEspecificaciones($premio->codigo, 3)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        echo "✅ Niveles seeders ejecutados correctamente\n";
        echo "   - 2-3 niveles por cada premio creados\n";
        echo "   - Progresión de tickets: 100/250/500 base según orden del premio\n";
    }

    private function getValorNivel($codigoPremio, $nivel)
    {
        $valores = [
            'p1' => [1 => 179.00, 2 => 249.00, 3 => 299.00], // AirPods/Magic Keyboard
            'p2' => [1 => 599.00, 2 => 799.00, 3 => 999.00], // iPad/Studio Display
            'p3' => [1 => 1199.00, 2 => 1399.00, 3 => 1599.00], // iPhone/MacBook
        ];

        return $valores[$codigoPremio][$nivel] ?? 199.00;
    }

    private function getEspecificaciones($codigoPremio, $nivel)
    {
        $especificaciones = [
            'p1' => [
                1 => ['almacenamiento' => '64GB', 'color' => 'Blanco', 'accesorios' => 'Cable Lightning'],
                2 => ['almacenamiento' => '128GB', 'color' => 'Space Gray', 'accesorios' => 'Cable Lightning + Funda'],
                3 => ['almacenamiento' => '256GB', 'color' => 'A elegir', 'accesorios' => 'Pack completo + AppleCare']
            ],
            'p2' => [
                1 => ['almacenamiento' => '128GB', 'conectividad' => 'Wi-Fi', 'accesorios' => 'Cargador'],
                2 => ['almacenamiento' => '256GB', 'conectividad' => 'Wi-Fi + Cellular', 'accesorios' => 'Cargador + Apple Pencil'],
                3 => ['almacenamiento' => '512GB', 'conectividad' => 'Wi-Fi + Cellular', 'accesorios' => 'Pack completo + Magic Keyboard']
            ],
            'p3' => [
                1 => ['almacenamiento' => '512GB', 'memoria' => '8GB', 'color' => 'Space Black'],
                2 => ['almacenamiento' => '1TB', 'memoria' => '18GB', 'color' => 'A elegir'],
                3 => ['almacenamiento' => '1TB', 'memoria' => '36GB', 'color' => 'A elegir', 'extras' => 'AppleCare+ 3 años']
            ]
        ];

        return $especificaciones[$codigoPremio][$nivel] ?? [];
    }
}

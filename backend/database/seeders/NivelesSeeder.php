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
        // Obtener rifas que tienen premios (las progresivas)
        $rifas = DB::table('rifas')
            ->whereIn('codigo_unico', ['IPHONE15PM001', 'MACBOOKM3001'])
            ->get();
        
        foreach ($rifas as $rifa) {
            // Obtener premios de esta rifa ordenados por orden
            $premios = DB::table('premios')
                ->where('rifa_id', $rifa->id)
                ->orderBy('orden')
                ->get();

            $ticketsAcumulados = 0; // Contador acumulativo para toda la rifa

            foreach ($premios as $premio) {
                // Determinar estado inicial basado en el estado del premio
                $premioCompletado = $premio->estado === 'completado';
                $premioActivo = $premio->estado === 'activo';

                // Calcular tickets para cada nivel de forma secuencial
                $ticketsNivel1 = $ticketsAcumulados + 100; // +100 tickets para nivel 1
                $ticketsNivel2 = $ticketsAcumulados + 200; // +200 tickets para nivel 2

                // Nivel 1: Básico
                $nivel1Desbloqueado = $premioCompletado || $premioActivo;
                DB::table('niveles')->updateOrInsert(
                    [
                        'premio_id' => $premio->id,
                        'codigo' => 'n1'
                    ],
                    [
                        'titulo' => 'Nivel Básico',
                        'descripcion' => 'Acceso básico al premio con especificaciones estándar.',
                        'tickets_necesarios' => $ticketsNivel1,
                        'tickets_acumulados' => $ticketsAcumulados,
                        'valor_aproximado' => $this->getValorNivel($premio->codigo, 1),
                        'imagen' => "/images/niveles/{$premio->codigo}-n1.webp",
                        'media_gallery' => json_encode($this->getMediaGallery($premio->codigo, 1)),
                        'orden' => 1,
                        'desbloqueado' => $nivel1Desbloqueado,
                    'fecha_desbloqueo' => $nivel1Desbloqueado ? now()->subDays(10) : null,
                    'especificaciones' => json_encode($this->getEspecificaciones($premio->codigo, 1)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
                );

                // Nivel 2: Premium
                $nivel2Desbloqueado = $premioCompletado;
                DB::table('niveles')->updateOrInsert(
                    [
                        'premio_id' => $premio->id,
                        'codigo' => 'n2'
                    ],
                    [
                        'titulo' => 'Nivel Premium',
                        'descripcion' => 'Versión mejorada con características adicionales y mayor valor.',
                        'tickets_necesarios' => $ticketsNivel2,
                        'tickets_acumulados' => $ticketsNivel1,
                        'valor_aproximado' => $this->getValorNivel($premio->codigo, 2),
                        'imagen' => "/images/niveles/{$premio->codigo}-n2.jpg",
                        'media_gallery' => json_encode($this->getMediaGallery($premio->codigo, 2)),
                        'orden' => 2,
                        'desbloqueado' => $nivel2Desbloqueado,
                    'fecha_desbloqueo' => $nivel2Desbloqueado ? now()->subDays(5) : null,
                    'especificaciones' => json_encode($this->getEspecificaciones($premio->codigo, 2)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
                );

                // Nivel 3: Elite (solo para premios finales - orden 3)
                if ($premio->orden == 3) {
                    $ticketsNivel3 = $ticketsAcumulados + 300; // +300 tickets para nivel 3
                    DB::table('niveles')->updateOrInsert(
                        [
                            'premio_id' => $premio->id,
                            'codigo' => 'n3'
                        ],
                        [
                            'titulo' => 'Nivel Elite',
                            'descripcion' => 'La versión más exclusiva con todos los accesorios y características premium.',
                            'tickets_necesarios' => $ticketsNivel3,
                            'tickets_acumulados' => $ticketsNivel2,
                            'valor_aproximado' => $this->getValorNivel($premio->codigo, 3),
                            'imagen' => "/images/niveles/{$premio->codigo}-n3.jpg",
                            'media_gallery' => json_encode($this->getMediaGallery($premio->codigo, 3)),
                            'orden' => 3,
                            'desbloqueado' => false,
                        'fecha_desbloqueo' => null,
                        'especificaciones' => json_encode($this->getEspecificaciones($premio->codigo, 3)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                    );
                    // Actualizar acumulado después del nivel 3
                    $ticketsAcumulados += 300;
                } else {
                    // Actualizar acumulado después de los 2 niveles normales
                    $ticketsAcumulados += 200;
                }
            }
        }

        echo "✅ Niveles seeders ejecutados correctamente\n";
        echo "   - Progresión secuencial de tickets por rifa:\n";
        echo "   - Rifa iPhone: Premio 1 (100, 200), Premio 2 (300, 400), Premio 3 (500, 600, 700)\n";
        echo "   - Rifa MacBook: Premio 1 (100, 200), Premio 2 (300, 400), Premio 3 (500, 600, 700)\n";
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

    private function getMediaGallery($codigoPremio, $nivel)
    {
        $galleries = [
            'p1' => [
                1 => [
                    "/images/niveles/{$codigoPremio}-n1-front.webp",
                    "/images/niveles/{$codigoPremio}-n1-back.webp",
                    "/images/niveles/{$codigoPremio}-n1-case.webp"
                ],
                2 => [
                    "/images/niveles/{$codigoPremio}-n2-white.jpg",
                    "/images/niveles/{$codigoPremio}-n2-gray.jpg",
                    "/images/niveles/{$codigoPremio}-n2-accessories.jpg",
                    "/images/niveles/{$codigoPremio}-n2-package.jpg"
                ],
                3 => [
                    "/images/niveles/{$codigoPremio}-n3-gold.jpg",
                    "/images/niveles/{$codigoPremio}-n3-silver.jpg",
                    "/images/niveles/{$codigoPremio}-n3-black.jpg",
                    "/images/niveles/{$codigoPremio}-n3-complete.jpg",
                    "/images/niveles/{$codigoPremio}-n3-premium.jpg"
                ]
            ],
            'p2' => [
                1 => [
                    "/images/niveles/{$codigoPremio}-n1-wifi.webp",
                    "/images/niveles/{$codigoPremio}-n1-screen.webp",
                    "/images/niveles/{$codigoPremio}-n1-basic.webp"
                ],
                2 => [
                    "/images/niveles/{$codigoPremio}-n2-cellular.jpg",
                    "/images/niveles/{$codigoPremio}-n2-pencil.jpg",
                    "/images/niveles/{$codigoPremio}-n2-setup.jpg",
                    "/images/niveles/{$codigoPremio}-n2-colors.jpg"
                ],
                3 => [
                    "/images/niveles/{$codigoPremio}-n3-pro.jpg",
                    "/images/niveles/{$codigoPremio}-n3-keyboard.jpg",
                    "/images/niveles/{$codigoPremio}-n3-stylus.jpg",
                    "/images/niveles/{$codigoPremio}-n3-workspace.jpg",
                    "/images/niveles/{$codigoPremio}-n3-premium.jpg"
                ]
            ],
            'p3' => [
                1 => [
                    "/images/niveles/{$codigoPremio}-n1-titanium.webp",
                    "/images/niveles/{$codigoPremio}-n1-camera.webp",
                    "/images/niveles/{$codigoPremio}-n1-display.webp"
                ],
                2 => [
                    "/images/niveles/{$codigoPremio}-n2-colors.jpg",
                    "/images/niveles/{$codigoPremio}-n2-storage.jpg",
                    "/images/niveles/{$codigoPremio}-n2-performance.jpg",
                    "/images/niveles/{$codigoPremio}-n2-features.jpg"
                ],
                3 => [
                    "/images/niveles/{$codigoPremio}-n3-ultimate.jpg",
                    "/images/niveles/{$codigoPremio}-n3-maxstorage.jpg",
                    "/images/niveles/{$codigoPremio}-n3-allcolors.jpg",
                    "/images/niveles/{$codigoPremio}-n3-applecare.jpg",
                    "/images/niveles/{$codigoPremio}-n3-complete.jpg"
                ]
            ]
        ];

        return $galleries[$codigoPremio][$nivel] ?? [];
    }
}

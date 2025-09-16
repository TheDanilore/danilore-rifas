<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgresoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los premios y sus niveles
        $premios = DB::table('premios')->get();

        foreach ($premios as $premio) {
            $niveles = DB::table('niveles')->where('premio_id', $premio->id)->get();

            foreach ($niveles as $nivel) {
                // Crear registro de progreso inicial para cada nivel
                DB::table('progreso_premios')->updateOrInsert(
                    [
                        'rifa_id' => $premio->rifa_id,
                        'premio_id' => $premio->id,
                        'nivel_id' => $nivel->id
                    ],
                    [
                        'tickets_actuales' => 0,
                        'tickets_objetivo' => $nivel->tickets_necesarios,
                        'porcentaje_completado' => 0.00,
                        'objetivo_alcanzado' => false,
                        'fecha_alcanzado' => null,
                        'ultimo_ticket' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            // ❌ ELIMINADO - No crear registro general del premio
            // El progreso general se calculará dinámicamente desde los niveles
        }

        // Simular progreso realista para la rifa del iPhone
        $rifaIphone = DB::table('rifas')->where('codigo_unico', 'IPHONE15PM001')->first();
        if ($rifaIphone) {
            // ✅ PREMIO 1 (AirPods): COMPLETADO - Ambos niveles
            $premio1 = DB::table('premios')->where('rifa_id', $rifaIphone->id)->where('codigo', 'p1')->first();
            if ($premio1) {
                $niveles1 = DB::table('niveles')->where('premio_id', $premio1->id)->orderBy('orden')->get();
                
                // Nivel 1: COMPLETADO (100/100 tickets)
                if ($niveles1->count() > 0) {
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio1->id)
                        ->where('nivel_id', $niveles1[0]->id)
                        ->update([
                            'tickets_actuales' => $niveles1[0]->tickets_necesarios, // 100/100
                            'porcentaje_completado' => 100.00,
                            'objetivo_alcanzado' => true,
                            'fecha_alcanzado' => now()->subDays(8),
                            'ultimo_ticket' => now()->subDays(8),
                            'updated_at' => now(),
                        ]);
                }
                
                // Nivel 2: COMPLETADO (200/200 tickets)
                if ($niveles1->count() > 1) {
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio1->id)
                        ->where('nivel_id', $niveles1[1]->id)
                        ->update([
                            'tickets_actuales' => $niveles1[1]->tickets_necesarios, // 200/200
                            'porcentaje_completado' => 100.00,
                            'objetivo_alcanzado' => true,
                            'fecha_alcanzado' => now()->subDays(5),
                            'ultimo_ticket' => now()->subDays(5),
                            'updated_at' => now(),
                        ]);
                }
            }

            // ✅ PREMIO 2 (iPad): COMPLETADO - Ambos niveles
            $premio2 = DB::table('premios')->where('rifa_id', $rifaIphone->id)->where('codigo', 'p2')->first();
            if ($premio2) {
                $niveles2 = DB::table('niveles')->where('premio_id', $premio2->id)->orderBy('orden')->get();
                
                // Nivel 1: COMPLETADO (150/150 tickets)
                if ($niveles2->count() > 0) {
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio2->id)
                        ->where('nivel_id', $niveles2[0]->id)
                        ->update([
                            'tickets_actuales' => $niveles2[0]->tickets_necesarios, // 150/150
                            'porcentaje_completado' => 100.00,
                            'objetivo_alcanzado' => true,
                            'fecha_alcanzado' => now()->subDays(4),
                            'ultimo_ticket' => now()->subDays(4),
                            'updated_at' => now(),
                        ]);
                }
                
                // Nivel 2: COMPLETADO (300/300 tickets)
                if ($niveles2->count() > 1) {
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio2->id)
                        ->where('nivel_id', $niveles2[1]->id)
                        ->update([
                            'tickets_actuales' => $niveles2[1]->tickets_necesarios, // 300/300
                            'porcentaje_completado' => 100.00,
                            'objetivo_alcanzado' => true,
                            'fecha_alcanzado' => now()->subDays(2),
                            'ultimo_ticket' => now()->subDays(2),
                            'updated_at' => now(),
                        ]);
                }
            }

            // ✅ PREMIO 3 (iPhone): ACTIVO - En progreso
            $premio3 = DB::table('premios')->where('rifa_id', $rifaIphone->id)->where('codigo', 'p3')->first();
            if ($premio3) {
                $niveles3 = DB::table('niveles')->where('premio_id', $premio3->id)->orderBy('orden')->get();
                
                // Nivel 1: EN PROGRESO (0/250 tickets) - Acaba de empezar
                if ($niveles3->count() > 0) {
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio3->id)
                        ->where('nivel_id', $niveles3[0]->id)
                        ->update([
                            'tickets_actuales' => 0, // Recién empezando
                            'porcentaje_completado' => 0.00,
                            'objetivo_alcanzado' => false,
                            'fecha_alcanzado' => null,
                            'ultimo_ticket' => null,
                            'updated_at' => now(),
                        ]);
                }
                
                // Niveles 2 y 3: SIN PROGRESO (0 tickets)
                for ($i = 1; $i < $niveles3->count(); $i++) {
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio3->id)
                        ->where('nivel_id', $niveles3[$i]->id)
                        ->update([
                            'tickets_actuales' => 0,
                            'porcentaje_completado' => 0.00,
                            'objetivo_alcanzado' => false,
                            'fecha_alcanzado' => null,
                            'ultimo_ticket' => null,
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        echo "✅ Progreso inicial creado correctamente\n";
        echo "   - Registros de progreso para todos los premios y niveles\n";
        echo "   - Premio 1 (AirPods): COMPLETADO - 2/2 niveles (300 tickets total)\n";
        echo "   - Premio 2 (iPad): COMPLETADO - 2/2 niveles (450 tickets total)\n";  
        echo "   - Premio 3 (iPhone): ACTIVO - 0/3 niveles (recién desbloqueado)\n";
        echo "   - Total tickets vendidos en rifa iPhone: 500\n";
    }
}

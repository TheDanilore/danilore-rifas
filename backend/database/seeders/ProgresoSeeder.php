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
                DB::table('progreso_premios')->insert([
                    'premio_id' => $premio->id,
                    'nivel_id' => $nivel->id,
                    'tickets_actuales' => 0,
                    'tickets_objetivo' => $nivel->tickets_necesarios,
                    'porcentaje_completado' => 0.00,
                    'objetivo_alcanzado' => false,
                    'fecha_alcanzado' => null,
                    'ultimo_ticket' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Crear también un registro de progreso general para el premio (sin nivel específico)
            $totalTicketsObjetivo = DB::table('niveles')
                ->where('premio_id', $premio->id)
                ->sum('tickets_necesarios');

            DB::table('progreso_premios')->insert([
                'premio_id' => $premio->id,
                'nivel_id' => null, // Progreso general del premio
                'tickets_actuales' => 0,
                'tickets_objetivo' => $totalTicketsObjetivo,
                'porcentaje_completado' => 0.00,
                'objetivo_alcanzado' => false,
                'fecha_alcanzado' => null,
                'ultimo_ticket' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Simular algunos tickets vendidos para la rifa del iPhone (para mostrar progreso)
        $rifaIphone = DB::table('rifas')->where('codigo_unico', 'IPHONE15PM001')->first();
        if ($rifaIphone) {
            $premio1 = DB::table('premios')->where('rifa_id', $rifaIphone->id)->where('codigo', 'p1')->first();
            if ($premio1) {
                $nivel1 = DB::table('niveles')->where('premio_id', $premio1->id)->where('codigo', 'n1')->first();
                if ($nivel1) {
                    // Simular 35 tickets vendidos para el primer nivel del primer premio
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio1->id)
                        ->where('nivel_id', $nivel1->id)
                        ->update([
                            'tickets_actuales' => 35,
                            'porcentaje_completado' => round((35 / $nivel1->tickets_necesarios) * 100, 2),
                            'ultimo_ticket' => now()->subHours(2),
                            'updated_at' => now(),
                        ]);

                    // Actualizar también el progreso general del premio
                    DB::table('progreso_premios')
                        ->where('premio_id', $premio1->id)
                        ->whereNull('nivel_id')
                        ->update([
                            'tickets_actuales' => 35,
                            'ultimo_ticket' => now()->subHours(2),
                            'updated_at' => now(),
                        ]);
                }
            }

            // Actualizar boletos vendidos en la rifa
            DB::table('rifas')->where('id', $rifaIphone->id)->update([
                'boletos_vendidos' => 35,
                'updated_at' => now(),
            ]);
        }

        echo "✅ Progreso inicial creado correctamente\n";
        echo "   - Registros de progreso para todos los premios y niveles\n";
        echo "   - Simulación de 35 tickets vendidos para demostración\n";
    }
}

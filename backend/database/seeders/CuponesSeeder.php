<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CuponesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuario admin para crear cupones
        $admin = DB::table('users')->where('email', 'admin@danilorerifa.com')->first();
        $adminId = $admin ? $admin->id : 1;

        $cupones = [
            // Cupón de bienvenida - porcentaje
            [
                'codigo' => 'BIENVENIDA2025',
                'nombre' => 'Cupón de Bienvenida',
                'descripcion' => 'Descuento del 15% para nuevos usuarios en su primera compra',
                'tipo_descuento' => 'porcentaje',
                'valor_descuento' => 15.00,
                'descuento_maximo' => 50.00,
                'compra_minima' => 20.00,
                'usos_maximos' => 1000,
                'usos_actuales' => 0,
                'usos_por_usuario' => 1,
                'solo_primera_compra' => true,
                'fecha_inicio' => Carbon::now()->startOfYear(),
                'fecha_fin' => Carbon::now()->endOfYear(),
                'rifas_aplicables' => null, // Aplica a todas las rifas
                'categorias_aplicables' => null,
                'usuarios_aplicables' => null,
                'monto_maximo_descuento' => 50.00,
                'activo' => true,
                'visible_publico' => true,
                'estado' => 'activo',
                'creado_por' => $adminId,
                'notas_admin' => 'Cupón promocional para nuevos usuarios',
                'campana' => 'Bienvenida 2025'
            ],
            // Cupón fijo para rifas premium
            [
                'codigo' => 'PREMIUM10',
                'nombre' => 'Descuento Premium',
                'descripcion' => 'S/ 10 de descuento en rifas premium',
                'tipo_descuento' => 'monto_fijo',
                'valor_descuento' => 10.00,
                'descuento_maximo' => 10.00,
                'compra_minima' => 50.00,
                'usos_maximos' => 500,
                'usos_actuales' => 0,
                'usos_por_usuario' => 3,
                'solo_primera_compra' => false,
                'fecha_inicio' => Carbon::now()->subDays(30),
                'fecha_fin' => Carbon::now()->addDays(60),
                'rifas_aplicables' => json_encode(['IPHONE15PM001', 'TESLA2025001']),
                'categorias_aplicables' => null,
                'usuarios_aplicables' => null,
                'monto_maximo_descuento' => 10.00,
                'activo' => true,
                'visible_publico' => true,
                'estado' => 'activo',
                'creado_por' => $adminId,
                'notas_admin' => 'Cupón para rifas de alta gama',
                'campana' => 'Premium'
            ],
            // Cupón especial del mes - porcentaje alto
            [
                'codigo' => 'ESPECIAL20',
                'nombre' => 'Oferta Especial del Mes',
                'descripcion' => 'Descuento del 20% en cualquier compra',
                'tipo_descuento' => 'porcentaje',
                'valor_descuento' => 20.00,
                'descuento_maximo' => 100.00,
                'compra_minima' => 30.00,
                'usos_maximos' => 200,
                'usos_actuales' => 45, // Ya ha sido usado
                'usos_por_usuario' => 1,
                'solo_primera_compra' => false,
                'fecha_inicio' => Carbon::now()->startOfMonth(),
                'fecha_fin' => Carbon::now()->endOfMonth(),
                'rifas_aplicables' => null,
                'categorias_aplicables' => json_encode(['tecnologia', 'apple']),
                'usuarios_aplicables' => null,
                'monto_maximo_descuento' => 100.00,
                'activo' => true,
                'visible_publico' => true,
                'estado' => 'activo',
                'creado_por' => $adminId,
                'notas_admin' => 'Promoción mensual especial',
                'campana' => 'Oferta Especial'
            ],
            // Cupón VIP - solo usuarios específicos
            [
                'codigo' => 'VIP2025',
                'nombre' => 'Cupón VIP Exclusivo',
                'descripcion' => 'Descuento del 25% exclusivo para usuarios VIP',
                'tipo_descuento' => 'porcentaje',
                'valor_descuento' => 25.00,
                'descuento_maximo' => 200.00,
                'compra_minima' => 100.00,
                'usos_maximos' => 50,
                'usos_actuales' => 5,
                'usos_por_usuario' => 2,
                'solo_primera_compra' => false,
                'fecha_inicio' => Carbon::now()->subDays(10),
                'fecha_fin' => Carbon::now()->addDays(90),
                'rifas_aplicables' => null,
                'categorias_aplicables' => null,
                'usuarios_aplicables' => json_encode(['admin@danilorerifa.com', 'moderador@danilorerifa.com']),
                'monto_maximo_descuento' => 200.00,
                'activo' => true,
                'visible_publico' => false, // No visible públicamente
                'estado' => 'activo',
                'creado_por' => $adminId,
                'notas_admin' => 'Cupón exclusivo para usuarios VIP',
                'campana' => 'VIP Exclusivo'
            ],
            // Cupón de temporada limitada
            [
                'codigo' => 'VERANO2025',
                'nombre' => 'Promoción de Verano',
                'descripcion' => 'S/ 15 de descuento en compras de verano',
                'tipo_descuento' => 'monto_fijo',
                'valor_descuento' => 15.00,
                'descuento_maximo' => 15.00,
                'compra_minima' => 60.00,
                'usos_maximos' => 300,
                'usos_actuales' => 0,
                'usos_por_usuario' => 2,
                'solo_primera_compra' => false,
                'fecha_inicio' => Carbon::create(2025, 1, 1),
                'fecha_fin' => Carbon::create(2025, 3, 31),
                'rifas_aplicables' => null,
                'categorias_aplicables' => null,
                'usuarios_aplicables' => null,
                'monto_maximo_descuento' => 15.00,
                'activo' => true,
                'visible_publico' => true,
                'estado' => 'activo',
                'creado_por' => $adminId,
                'notas_admin' => 'Promoción estacional de verano',
                'campana' => 'Verano 2025'
            ],
            // Cupón expirado para testing
            [
                'codigo' => 'EXPIRADO2024',
                'nombre' => 'Cupón Expirado (Testing)',
                'descripcion' => 'Cupón expirado para pruebas del sistema',
                'tipo_descuento' => 'porcentaje',
                'valor_descuento' => 30.00,
                'descuento_maximo' => 50.00,
                'compra_minima' => 25.00,
                'usos_maximos' => 100,
                'usos_actuales' => 25,
                'usos_por_usuario' => 1,
                'solo_primera_compra' => false,
                'fecha_inicio' => Carbon::now()->subDays(60),
                'fecha_fin' => Carbon::now()->subDays(30), // Expirado
                'rifas_aplicables' => null,
                'categorias_aplicables' => null,
                'usuarios_aplicables' => null,
                'monto_maximo_descuento' => 50.00,
                'activo' => false,
                'visible_publico' => false,
                'estado' => 'expirado',
                'creado_por' => $adminId,
                'notas_admin' => 'Cupón para testing de expiración',
                'campana' => 'Testing'
            ],
            // Cupón agotado para testing
            [
                'codigo' => 'AGOTADO50',
                'nombre' => 'Cupón Agotado (Testing)',
                'descripcion' => 'Cupón agotado para pruebas del sistema',
                'tipo_descuento' => 'monto_fijo',
                'valor_descuento' => 50.00,
                'descuento_maximo' => 50.00,
                'compra_minima' => 100.00,
                'usos_maximos' => 10,
                'usos_actuales' => 10, // Agotado
                'usos_por_usuario' => 1,
                'solo_primera_compra' => false,
                'fecha_inicio' => Carbon::now()->subDays(20),
                'fecha_fin' => Carbon::now()->addDays(30),
                'rifas_aplicables' => null,
                'categorias_aplicables' => null,
                'usuarios_aplicables' => null,
                'monto_maximo_descuento' => 50.00,
                'activo' => true,
                'visible_publico' => true,
                'estado' => 'agotado',
                'creado_por' => $adminId,
                'notas_admin' => 'Cupón para testing de agotamiento',
                'campana' => 'Testing'
            ]
        ];

        foreach ($cupones as $cupon) {
            DB::table('cupones')->updateOrInsert(
                ['codigo' => $cupon['codigo']],
                array_merge($cupon, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        echo "✅ Cupones seeders ejecutados correctamente\n";
        echo "   - 7 cupones de prueba creados\n";
        echo "   - Tipos: porcentaje y fijo\n";
        echo "   - Estados: activo, expirado, agotado\n";
        echo "   - Cupones públicos y privados\n";
        echo "   - Restricciones por usuario, rifa y categoría\n";
        echo "\n📋 Cupones disponibles:\n";
        echo "   - BIENVENIDA2025: 15% descuento para nuevos usuarios\n";
        echo "   - PREMIUM10: S/ 10 descuento en rifas premium\n";
        echo "   - ESPECIAL20: 20% descuento mensual\n";
        echo "   - VIP2025: 25% descuento exclusivo\n";
        echo "   - VERANO2025: S/ 15 descuento estacional\n";
    }
}

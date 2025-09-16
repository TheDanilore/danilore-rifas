<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $rifaIphone = DB::table('rifas')->where('codigo_unico', 'IPHONE15PM001')->first();
        $rifaTesla = DB::table('rifas')->where('codigo_unico', 'TESLA2025001')->first();
        $usuarios = DB::table('users')->whereNot('email', 'superadmin@danilorerifa.com')->limit(10)->get();

        if (!$rifaIphone || !$rifaTesla || $usuarios->isEmpty()) {
            echo "⚠️ No se encontraron rifas o usuarios necesarios para crear ventas\n";
            return;
        }

        $ventasCreadas = 0;

        // Ventas para rifa del iPhone (500 boletos vendidos según progreso actual)
        for ($i = 1; $i <= 500; $i++) {
            $usuario = $usuarios->random();
            $fechaVenta = Carbon::now()->subDays(rand(1, 30))->subHours(rand(0, 23));
            
            $codigoVenta = 'VT' . $fechaVenta->format('Ymd') . strtoupper(substr(md5($i . $usuario->id), 0, 6));
            
            // Crear venta
            DB::table('ventas')->updateOrInsert(
                ['codigo_venta' => $codigoVenta],
                [
                    'user_id' => $usuario->id,
                    'rifa_id' => $rifaIphone->id,
                    'codigo_venta' => $codigoVenta,
                    'cantidad_boletos' => 1,
                    'precio_unitario' => $rifaIphone->precio_boleto,
                    'subtotal' => $rifaIphone->precio_boleto,
                    'descuento' => 0,
                    'impuestos' => 0,
                    'comision' => $rifaIphone->precio_boleto * 0.10, // 10% comisión
                    'total' => $rifaIphone->precio_boleto,
                    'estado' => 'confirmado',
                    'metodo_pago' => collect(['yape', 'plin', 'transferencia_bancaria'])->random(),
                    'fecha_reserva' => $fechaVenta,
                    'fecha_expiracion' => $fechaVenta->copy()->addMinutes(15),
                    'fecha_pago' => $fechaVenta->copy()->addMinutes(rand(2, 10)),
                    'fecha_confirmacion' => $fechaVenta->copy()->addMinutes(rand(10, 20)),
                    'numeros_seleccionados' => json_encode([$i]),
                    'comprador_nombre' => $usuario->nombre . ' ' . $usuario->apellido,
                    'comprador_email' => $usuario->email,
                    'comprador_telefono' => $usuario->telefono ?? '51999000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'comprador_tipo_documento' => 'dni',
                    'comprador_numero_documento' => '10000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'referencia_pago' => 'PAG' . strtoupper(substr(md5($i), 0, 8)),
                    'monto_pagado' => $rifaIphone->precio_boleto,
                    'ip_cliente' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Mobile; DaniloreRifas)',
                    'origen' => 'web',
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta->copy()->addMinutes(20),
                ]
            );

            // Crear boleto correspondiente
            DB::table('boletos')->updateOrInsert(
                [
                    'rifa_id' => $rifaIphone->id,
                    'numero' => (string)$i
                ],
                [
                    'user_id' => $usuario->id,
                    'venta_id' => DB::table('ventas')->where('codigo_venta', $codigoVenta)->first()->id,
                    'numero' => (string)$i,
                    'codigo_verificacion' => 'VER' . strtoupper(substr(md5($i . $usuario->id . $rifaIphone->id . time() . rand()), 0, 8)),
                    'precio_pagado' => $rifaIphone->precio_boleto,
                    'estado' => 'confirmado',
                    'fecha_reserva' => $fechaVenta,
                    'fecha_expiracion_reserva' => $fechaVenta->copy()->addMinutes(15),
                    'fecha_pago' => $fechaVenta->copy()->addMinutes(rand(2, 10)),
                    'fecha_confirmacion' => $fechaVenta->copy()->addMinutes(rand(10, 20)),
                    'es_ganador' => false,
                    'origen' => 'venta_normal',
                    'ip_creacion' => '192.168.1.' . rand(1, 254),
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta->copy()->addMinutes(20),
                ]
            );

            $ventasCreadas++;
        }

        // Algunas ventas para Tesla (50 boletos)
        for ($i = 1; $i <= 50; $i++) {
            $usuario = $usuarios->random();
            $fechaVenta = Carbon::now()->subDays(rand(1, 15))->subHours(rand(0, 23));
            
            $codigoVenta = 'VT' . $fechaVenta->format('Ymd') . 'T' . strtoupper(substr(md5($i . $usuario->id), 0, 5));
            $numeroBoleto = str_pad($i, 6, '0', STR_PAD_LEFT); // Tesla tiene 6 dígitos
            
            // Crear venta para Tesla
            DB::table('ventas')->updateOrInsert(
                ['codigo_venta' => $codigoVenta],
                [
                    'user_id' => $usuario->id,
                    'rifa_id' => $rifaTesla->id,
                    'codigo_venta' => $codigoVenta,
                    'cantidad_boletos' => 1,
                    'precio_unitario' => $rifaTesla->precio_boleto,
                    'subtotal' => $rifaTesla->precio_boleto,
                    'descuento' => 0,
                    'impuestos' => 0,
                    'comision' => $rifaTesla->precio_boleto * 0.10,
                    'total' => $rifaTesla->precio_boleto,
                    'estado' => 'confirmado',
                    'metodo_pago' => collect(['yape', 'plin', 'transferencia_bancaria'])->random(),
                    'fecha_reserva' => $fechaVenta,
                    'fecha_expiracion' => $fechaVenta->copy()->addMinutes(15),
                    'fecha_pago' => $fechaVenta->copy()->addMinutes(rand(2, 10)),
                    'fecha_confirmacion' => $fechaVenta->copy()->addMinutes(rand(10, 20)),
                    'numeros_seleccionados' => json_encode([$numeroBoleto]),
                    'comprador_nombre' => $usuario->nombre . ' ' . $usuario->apellido,
                    'comprador_email' => $usuario->email,
                    'comprador_telefono' => $usuario->telefono ?? '51999100' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'comprador_tipo_documento' => 'dni',
                    'comprador_numero_documento' => '20000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'referencia_pago' => 'TESLA' . strtoupper(substr(md5($i), 0, 6)),
                    'monto_pagado' => $rifaTesla->precio_boleto,
                    'ip_cliente' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Desktop; DaniloreRifas)',
                    'origen' => 'web',
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta->copy()->addMinutes(20),
                ]
            );

            // Crear boleto para Tesla
            DB::table('boletos')->updateOrInsert(
                [
                    'rifa_id' => $rifaTesla->id,
                    'numero' => $numeroBoleto
                ],
                [
                    'user_id' => $usuario->id,
                    'venta_id' => DB::table('ventas')->where('codigo_venta', $codigoVenta)->first()->id,
                    'numero' => $numeroBoleto,
                    'codigo_verificacion' => 'TES' . strtoupper(substr(md5($i . $usuario->id . $rifaTesla->id . time() . rand()), 0, 8)),
                    'precio_pagado' => $rifaTesla->precio_boleto,
                    'estado' => 'confirmado',
                    'fecha_reserva' => $fechaVenta,
                    'fecha_expiracion_reserva' => $fechaVenta->copy()->addMinutes(15),
                    'fecha_pago' => $fechaVenta->copy()->addMinutes(rand(2, 10)),
                    'fecha_confirmacion' => $fechaVenta->copy()->addMinutes(rand(10, 20)),
                    'es_ganador' => false,
                    'origen' => 'venta_normal',
                    'ip_creacion' => '192.168.1.' . rand(1, 254),
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta->copy()->addMinutes(20),
                ]
            );

            $ventasCreadas++;
        }

        // Crear algunas ventas pendientes para mostrar el flujo completo
        for ($i = 1; $i <= 5; $i++) {
            $usuario = $usuarios->random();
            $fechaVenta = Carbon::now()->subMinutes(rand(5, 30));
            
            $codigoVenta = 'VT' . $fechaVenta->format('Ymd') . 'P' . strtoupper(substr(md5($i . $usuario->id), 0, 5));
            $numeroBoleto = (string)(501 + $i); // Números después de los vendidos
            
            DB::table('ventas')->updateOrInsert(
                ['codigo_venta' => $codigoVenta],
                [
                    'user_id' => $usuario->id,
                    'rifa_id' => $rifaIphone->id,
                    'codigo_venta' => $codigoVenta,
                    'cantidad_boletos' => 1,
                    'precio_unitario' => $rifaIphone->precio_boleto,
                    'subtotal' => $rifaIphone->precio_boleto,
                    'descuento' => 0,
                    'impuestos' => 0,
                    'comision' => $rifaIphone->precio_boleto * 0.10,
                    'total' => $rifaIphone->precio_boleto,
                    'estado' => 'pendiente_pago',
                    'metodo_pago' => 'yape',
                    'fecha_reserva' => $fechaVenta,
                    'fecha_expiracion' => $fechaVenta->copy()->addMinutes(15),
                    'numeros_seleccionados' => json_encode([$numeroBoleto]),
                    'comprador_nombre' => $usuario->nombre . ' ' . $usuario->apellido,
                    'comprador_email' => $usuario->email,
                    'comprador_telefono' => $usuario->telefono ?? '51999200' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'comprador_tipo_documento' => 'dni',
                    'comprador_numero_documento' => '30000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'ip_cliente' => '192.168.1.' . rand(1, 254),
                    'user_agent' => 'Mozilla/5.0 (Mobile; DaniloreRifas)',
                    'origen' => 'web',
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta,
                ]
            );

            // Boletos reservados (pendientes de pago)
            DB::table('boletos')->updateOrInsert(
                [
                    'rifa_id' => $rifaIphone->id,
                    'numero' => $numeroBoleto
                ],
                [
                    'user_id' => $usuario->id,
                    'venta_id' => DB::table('ventas')->where('codigo_venta', $codigoVenta)->first()->id,
                    'numero' => $numeroBoleto,
                    'codigo_verificacion' => 'PEN' . strtoupper(substr(md5($i . $usuario->id . time() . rand()), 0, 8)),
                    'precio_pagado' => $rifaIphone->precio_boleto,
                    'estado' => 'reservado',
                    'fecha_reserva' => $fechaVenta,
                    'fecha_expiracion_reserva' => $fechaVenta->copy()->addMinutes(15),
                    'es_ganador' => false,
                    'origen' => 'venta_normal',
                    'ip_creacion' => '192.168.1.' . rand(1, 254),
                    'created_at' => $fechaVenta,
                    'updated_at' => $fechaVenta,
                ]
            );

            $ventasCreadas++;
        }

        echo "✅ Ventas seeders ejecutados correctamente\n";
        echo "   - {$ventasCreadas} ventas creadas en total\n";
        echo "   - Rifa iPhone: 500 boletos vendidos + 5 pendientes\n";
        echo "   - Rifa Tesla: 50 boletos vendidos\n";
        echo "   - Estados: confirmado, pendiente_pago\n";
        echo "   - Métodos de pago: yape, plin, transferencia\n";
    }
}

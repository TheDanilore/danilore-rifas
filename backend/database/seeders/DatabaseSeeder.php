<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        echo "🚀 Iniciando seeders del sistema de rifas...\n\n";

        // Primero crear roles y permisos
        echo "📋 Creando roles y permisos...\n";
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        // Crear usuarios con roles asignados
        echo "👥 Creando usuarios del sistema...\n";
        $this->call([
            UsersSeeder::class,
        ]);

        // Seeders del sistema de rifas progresivas
        echo "🎯 Creando datos del sistema de rifas...\n";
        $this->call([
            ConfiguracionSeeder::class,
            CategoriasSeeder::class,
            RifasSeeder::class,
            PremiosSeeder::class,
            NivelesSeeder::class,
            ProgresoSeeder::class,
        ]);

        // Seeders de transacciones y promociones
        echo "💰 Creando datos de ventas y promociones...\n";
        $this->call([
            CuponesSeeder::class,
            VentasSeeder::class, // También crea boletos automáticamente
        ]);

        echo "\n🎉 Todos los seeders ejecutados correctamente!\n";
        echo "Sistema completo de rifas progresivas listo para usar:\n";
        echo "  ✅ Roles y permisos configurados\n";
        echo "  ✅ Usuarios de prueba creados\n";
        echo "  ✅ Rifas progresivas y simples\n";
        echo "  ✅ Premios y niveles configurados\n";
        echo "  ✅ Progreso inicial simulado\n";
        echo "  ✅ Ventas y boletos de prueba\n";
        echo "  ✅ Cupones de descuento disponibles\n";
    }
}

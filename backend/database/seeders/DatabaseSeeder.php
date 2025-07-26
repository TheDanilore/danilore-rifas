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

        // Crear usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'danilore123'
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@danilorerifas.com',
            'email_verified_at' => now(),
            'password' => 'admin123',
            'rol' => 'admin',
        ]);

        echo "✅ Usuarios creados\n\n";

        // Seeders del sistema de rifas progresivas
        $this->call([
            RifasSeeder::class,
            PremiosSeeder::class,
            NivelesSeeder::class,
            ProgresoSeeder::class,
        ]);

        echo "\n🎉 Todos los seeders ejecutados correctamente!\n";
        echo "Sistema de rifas progresivas listo para usar.\n";
    }
}

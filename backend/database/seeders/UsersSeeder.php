<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Super Admin
        $superAdmin = User::create([
            'name' => 'Super Administrador Sistema',
            'nombre' => 'Super Administrador',
            'apellido' => 'Sistema',
            'email' => 'superadmin@danilorerifa.com',
            'password' => Hash::make('SuperAdmin123!'),
            'telefono' => '51999999999',
            'pais' => 'PE',
            'activo' => true,
            'verificado' => true,
            'email_verified_at' => now(),
            'ultimo_acceso' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Crear Admin
        $admin = User::create([
            'name' => 'Administrador Principal',
            'nombre' => 'Administrador',
            'apellido' => 'Principal',
            'email' => 'admin@danilorerifa.com',
            'password' => Hash::make('Admin123!'),
            'telefono' => '51988888888',
            'pais' => 'PE',
            'activo' => true,
            'verificado' => true,
            'email_verified_at' => now(),
            'ultimo_acceso' => now(),
        ]);
        $admin->assignRole('admin');

        // Crear Moderador
        $moderador = User::create([
            'name' => 'Moderador Sistema',
            'nombre' => 'Moderador',
            'apellido' => 'Sistema',
            'email' => 'moderador@danilorerifa.com',
            'password' => Hash::make('Moderador123!'),
            'telefono' => '51977777777',
            'pais' => 'PE',
            'activo' => true,
            'verificado' => true,
            'email_verified_at' => now(),
            'ultimo_acceso' => now(),
        ]);
        $moderador->assignRole('moderador');

        // Crear Usuario de ejemplo
        $usuario = User::create([
            'name' => 'Usuario Ejemplo',
            'nombre' => 'Usuario',
            'apellido' => 'Ejemplo',
            'email' => 'usuario@ejemplo.com',
            'password' => Hash::make('Usuario123!'),
            'telefono' => '51966666666',
            'pais' => 'PE',
            'activo' => true,
            'verificado' => true,
            'email_verified_at' => now(),
            'ultimo_acceso' => now(),
        ]);
        $usuario->assignRole('usuario');

        // Crear usuarios adicionales para pruebas
        $usuariosAdicionales = [
            ['nombre' => 'María', 'apellido' => 'González', 'email' => 'maria.gonzalez@email.com', 'telefono' => '51965432101'],
            ['nombre' => 'Carlos', 'apellido' => 'Rodríguez', 'email' => 'carlos.rodriguez@email.com', 'telefono' => '51965432102'],
            ['nombre' => 'Ana', 'apellido' => 'Martínez', 'email' => 'ana.martinez@email.com', 'telefono' => '51965432103'],
            ['nombre' => 'Luis', 'apellido' => 'García', 'email' => 'luis.garcia@email.com', 'telefono' => '51965432104'],
            ['nombre' => 'Carmen', 'apellido' => 'López', 'email' => 'carmen.lopez@email.com', 'telefono' => '51965432105'],
            ['nombre' => 'José', 'apellido' => 'Hernández', 'email' => 'jose.hernandez@email.com', 'telefono' => '51965432106'],
            ['nombre' => 'Patricia', 'apellido' => 'Díaz', 'email' => 'patricia.diaz@email.com', 'telefono' => '51965432107'],
            ['nombre' => 'Miguel', 'apellido' => 'Torres', 'email' => 'miguel.torres@email.com', 'telefono' => '51965432108'],
            ['nombre' => 'Rosa', 'apellido' => 'Flores', 'email' => 'rosa.flores@email.com', 'telefono' => '51965432109'],
            ['nombre' => 'David', 'apellido' => 'Vargas', 'email' => 'david.vargas@email.com', 'telefono' => '51965432110'],
        ];

        foreach ($usuariosAdicionales as $userData) {
            $user = User::create([
                'name' => $userData['nombre'] . ' ' . $userData['apellido'],
                'nombre' => $userData['nombre'],
                'apellido' => $userData['apellido'],
                'email' => $userData['email'],
                'password' => Hash::make('Usuario123!'),
                'telefono' => $userData['telefono'],
                'pais' => 'PE',
                'activo' => true,
                'verificado' => true,
                'email_verified_at' => now(),
                'ultimo_acceso' => now(),
            ]);
            $user->assignRole('usuario');
        }

        $this->command->info('Usuarios creados exitosamente:');
        $this->command->info('- Super Admin: superadmin@danilorerifa.com / SuperAdmin123!');
        $this->command->info('- Admin: admin@danilorerifa.com / Admin123!');
        $this->command->info('- Moderador: moderador@danilorerifa.com / Moderador123!');
        $this->command->info('- Usuario: usuario@ejemplo.com / Usuario123!');
        $this->command->info('- + 10 usuarios adicionales para pruebas');
    }
}
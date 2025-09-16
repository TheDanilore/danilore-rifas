<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos para el sistema de rifas
        $permissions = [
            // Gestión de usuarios
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            
            // Gestión de rifas
            'rifas.ver.admin',
            'rifas.crear',
            'rifas.editar',
            'rifas.eliminar',
            'rifas.cambiar.estado',
            'rifas.exportar',
            
            // Gestión de ventas
            'ventas.ver.admin',
            'ventas.reportes',
            'ventas.gestionar',
            
            // Gestión de premios
            'premios.crear',
            'premios.editar',
            'premios.eliminar',
            'premios.subir.imagenes',
            'premios.eliminar.imagenes',
            
            // Gestión de categorías
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',
            
            // Gestión de niveles
            'niveles.crear',
            'niveles.editar',
            'niveles.eliminar',
            
            // Dashboard y estadísticas
            'dashboard.ver',
            'estadisticas.ver',
            'actividad.ver',
            
            // Gestión de media/archivos
            'media.subir',
            'media.eliminar',
            'media.gestionar',
            
            // Permisos de ventas de usuario
            'ventas.crear',
            'ventas.confirmar.pago',
            'ventas.ver.propias',
            
            // Perfil de usuario
            'perfil.ver',
            'perfil.editar',
        ];

        // Crear todos los permisos
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        
        // 1. Super Admin - Acceso total
        $superAdmin = Role::create(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. Admin - Gestión completa excepto usuarios super admin
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.eliminar',
            'rifas.ver.admin',
            'rifas.crear',
            'rifas.editar',
            'rifas.eliminar',
            'rifas.cambiar.estado',
            'rifas.exportar',
            'ventas.ver.admin',
            'ventas.reportes',
            'ventas.gestionar',
            'premios.crear',
            'premios.editar',
            'premios.eliminar',
            'premios.subir.imagenes',
            'premios.eliminar.imagenes',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',
            'niveles.crear',
            'niveles.editar',
            'niveles.eliminar',
            'dashboard.ver',
            'estadisticas.ver',
            'actividad.ver',
            'media.subir',
            'media.eliminar',
            'media.gestionar',
            'perfil.ver',
            'perfil.editar',
        ]);

        // 3. Moderador - Gestión limitada de rifas y ventas
        $moderador = Role::create(['name' => 'moderador']);
        $moderador->givePermissionTo([
            'rifas.ver.admin',
            'rifas.editar',
            'rifas.cambiar.estado',
            'ventas.ver.admin',
            'ventas.gestionar',
            'premios.editar',
            'premios.subir.imagenes',
            'dashboard.ver',
            'estadisticas.ver',
            'actividad.ver',
            'media.subir',
            'perfil.ver',
            'perfil.editar',
        ]);

        // 4. Usuario - Solo funciones básicas
        $usuario = Role::create(['name' => 'usuario']);
        $usuario->givePermissionTo([
            'ventas.crear',
            'ventas.confirmar.pago',
            'ventas.ver.propias',
            'perfil.ver',
            'perfil.editar',
        ]);

        $this->command->info('Roles y permisos creados exitosamente:');
        $this->command->info('- Super Admin: Acceso total al sistema');
        $this->command->info('- Admin: Gestión completa de rifas, usuarios y contenido');
        $this->command->info('- Moderador: Gestión limitada de rifas y ventas');
        $this->command->info('- Usuario: Solo compras y gestión de perfil');
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class ConfiguracionUserSeeder extends Seeder
{
    /**
    * Run the database seeds.
    *
    * Seeder idempotente: puede ejecutarse múltiples veces
    * sin generar duplicados ni errores.
    */
    public function run(): void
    {
        // Limpiar caché de permisos al iniciar (por si había datos previos)
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ============================================================
        // 1. ROLES DEL SISTEMA
        // ============================================================
        $roles = [
            'Administrador', // Administrador del Sistema
            'Oficial',       // Oficial del gremio
            'Sub-Oficial',   // Sub-Oficial del gremio
            'Linhir',        // Integrante del gremio
            'Usuario',       // Usuario general
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // ============================================================
        // 2. PERMISOS DEL SISTEMA
        // ============================================================
        $permissions = [
            // --- Roles y Permisos ---
            'Ver Roles y Permisos',
            'Crear Roles y Permisos',
            'Editar Roles y Permisos',
            'Eliminar Roles y Permisos',

            // --- Usuarios ---
            'Ver Usuarios',
            'Crear Usuarios',
            'Editar Usuarios',
            'Eliminar Usuarios',

            // --- Módulos administrativos ---
            'Ver Linhir',
            'Ver Batallas',
            'Ver Discord',
            'Ver Banco Gremial',

            // --- Bot / Tareas / Puntos ---
            'review_reports',
            'manage_tasks',
            'manage_points',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name'       => $permName,
                'guard_name' => 'web',
            ]);
        }

        // ============================================================
        // 3. ASIGNAR PERMISOS A CADA ROL
        // ============================================================
        // Definimos el mapa completo por rol para poder usar syncPermissions
        // sin miedo a borrar permisos previos.
        $rolePermissions = [
            'Administrador' => $permissions, // Todos los permisos

            'Oficial' => [
                'Ver Usuarios',
                'Crear Usuarios',
                'Editar Usuarios',
                'Eliminar Usuarios',
                'Ver Linhir',
                'Ver Batallas',
                'Ver Discord',
                'Ver Banco Gremial',
                'review_reports',
                'manage_tasks',
                'manage_points',
            ],

            'Sub-Oficial' => [
                'Ver Usuarios',
                'Crear Usuarios',
                'Editar Usuarios',
                'Ver Linhir',
                'Ver Batallas',
                'Ver Discord',
                'review_reports',
            ],

            'Linhir' => [
                'Ver Batallas',
                'Ver Linhir',
            ],

            'Usuario' => [
                // Sin permisos administrativos por defecto
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions($perms);
        }

        // ============================================================
        // 4. USUARIO ADMINISTRADOR INICIAL
        // ============================================================
        // La contraseña puede sobreescribirse vía variable de entorno
        // ADMIN_INITIAL_PASSWORD. Si no se define, se usa '123456789'.
        $adminEmail    = env('ADMIN_INITIAL_EMAIL', 'admin@admin.com');
        $adminPassword = env('ADMIN_INITIAL_PASSWORD', '123456789');

        $adminUser = User::firstOrNew(['email' => $adminEmail]);

        if (!$adminUser->exists) {
            $adminUser->name              = 'Administrador';
            $adminUser->password          = Hash::make($adminPassword);
            $adminUser->email_verified_at = now();
            $adminUser->save();
        } else {
            // Si ya existe, solo actualizamos lo mínimo para no destruir sus datos
            $adminUser->forceFill([
                'name'              => $adminUser->name ?: 'Administrador',
                'email_verified_at' => $adminUser->email_verified_at ?? now(),
            ])->save();
        }

        // Aseguramos el rol sin duplicar
        if (!$adminUser->hasRole('Administrador')) {
            $adminUser->assignRole('Administrador');
        }

        // ============================================================
        // 5. LIMPIAR CACHÉ FINAL
        // ============================================================
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

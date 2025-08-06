<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class ConfiguracionUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Roles del Sistema
        $admin = Role::create(['name' => 'Administrador']); //Administrador del Sistema
        $oficial = Role::create(['name' => 'Oficial']); //Oficial del gremio
        $suboficial = Role::create(['name' => 'Sub-Oficial']); //Oficial del gremio
        $linhir = Role::create(['name' => 'Linhir']); //Integrante de Gremio
        $user = Role::create(['name' => 'Usuario']); //Usuarios generales


        $permission = Permission::create(['name' => 'Ver Roles y Permisos'])->syncRoles([$admin]);
        $permission = Permission::create(['name' => 'Crear Roles y Permisos'])->syncRoles([$admin]);
        $permission = Permission::create(['name' => 'Editar Roles y Permisos'])->syncRoles([$admin]);
        $permission = Permission::create(['name' => 'Eliminar Roles y Permisos'])->syncRoles([$admin]);

        $permission = Permission::create(['name' => 'Ver Usuarios'])->syncRoles([$admin, $oficial, $suboficial, $linhir]);
        $permission = Permission::create(['name' => 'Crear Usuarios'])->syncRoles([$admin, $oficial, $suboficial]);
        $permission = Permission::create(['name' => 'Editar Usuarios'])->syncRoles([$admin, $oficial, $suboficial]);
        $permission = Permission::create(['name' => 'Eliminar Usuarios'])->syncRoles([$admin, $oficial, $suboficial]);
        
        
        $useradmin = User::where('email','kayserenrique@gmail.com')->first();

        if ($useradmin) {
            $useradmin->delete();
        }

        $useradmin = User::create([
            'name' => 'Oliver Gomez',
            'email' => 'kayserenrique@gmail.com',
            'password' => Hash::make('123456789'),
            'email_verified_at' => '2025-08-05 14:14:14'
        ])->assignRole('Administrador')->personajes()->create([
            'Name' => 'kaizerenrique',
            'Id_albion' => 'iPSdBmtiSoSAL1Sp2DX1YQ',			
			'GuildId' => 'iS2Q2Mw3S1asC9GVMC5P2w',
            'miembro' => 'true'
        ]);


    }
}

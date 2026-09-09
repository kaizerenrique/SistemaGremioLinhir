<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncDiscordRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-discord-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los roles de Discord con los roles de la web (Oficial)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isTest = $this->option('test');
        $this->info('🔍 Obteniendo miembros del servidor de Discord...');

        // Obtener todos los miembros con sus roles (usando el trait)
        $members = $this->getGuildAllMembers();

        if (empty($members)) {
            $this->error('No se pudieron obtener los miembros. Verifica el token del bot.');
            return Command::FAILURE;
        }

        // ID del rol de Oficial en Discord (desde config)
        $discordOfficerRoleId = config('services.discord.officer_role_id');

        if (!$discordOfficerRoleId) {
            $this->error('Falta DISCORD_OFFICER_ROLE_ID en el archivo .env');
            return Command::FAILURE;
        }

        // Obtener el rol "Oficial" de la web (debe existir)
        $officerRole = Role::where('name', 'Oficial')->first();

        if (!$officerRole) {
            $this->error('El rol "Oficial" no existe en la base de datos. Ejecuta los seeders primero.');
            return Command::FAILURE;
        }

        $total = count($members);
        $this->info("📊 Procesando {$total} miembros...");

        $assigned = 0;
        $removed = 0;

        foreach ($members as $member) {
            $discordUserId = $member['user_id'];

            // Buscar usuario local que tenga este discord_user_id en auth_providers
            $user = User::whereHas('authProviders', function ($query) use ($discordUserId) {
                $query->where('provider', 'discord')
                      ->where('provider_id', $discordUserId);
            })->first();

            if (!$user) {
                // No está registrado en la web, ignorar
                continue;
            }

            // Verificar si tiene el rol de Oficial en Discord
            $hasDiscordOfficer = in_array($discordOfficerRoleId, $member['roles']);
            $hasWebOfficer = $user->hasRole('Oficial');

            if ($hasDiscordOfficer && !$hasWebOfficer) {
                // Asignar rol
                if ($isTest) {
                    $this->line("[TEST] Se asignaría rol Oficial a {$user->name} (ID: {$discordUserId})");
                } else {
                    $user->assignRole('Oficial');
                    $this->info("✅ Asignado rol Oficial a {$user->name}");
                    $assigned++;
                }
            } elseif (!$hasDiscordOfficer && $hasWebOfficer) {
                // Quitar rol
                if ($isTest) {
                    $this->line("[TEST] Se quitaría rol Oficial a {$user->name} (ID: {$discordUserId})");
                } else {
                    $user->removeRole('Oficial');
                    $this->info("❌ Removido rol Oficial a {$user->name}");
                    $removed++;
                }
            }
        }

        $this->info("✅ Sincronización completada.");
        $this->info("👤 Roles asignados: {$assigned}, roles removidos: {$removed}");

        Log::info('Sincronización de roles de Discord ejecutada', [
            'assigned' => $assigned,
            'removed' => $removed,
            'test_mode' => $isTest,
        ]);

        return Command::SUCCESS;
    }
}

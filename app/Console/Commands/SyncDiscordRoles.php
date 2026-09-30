<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Traits\DiscordComan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncDiscordRoles extends Command
{
    use DiscordComan;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-discord-roles
                            {--test : Ejecuta en modo prueba sin aplicar cambios}
                            {--role=Oficial : Rol web a sincronizar}
                            {--discord-role-id= : ID del rol en Discord (por defecto usa DISCORD_OFFICER_ROLE_ID)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza los roles de Discord con los roles de la web (Oficial)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isTest        = (bool) $this->option('test');
        $roleName      = (string) $this->option('role');
        $discordRoleId = $this->option('discord-role-id')
            ?: config('services.discord.officer_role_id');

        $this->info('🔍 Iniciando sincronización de roles Discord → Web');
        if ($isTest) {
            $this->warn('🧪 MODO PRUEBA: no se aplicará ningún cambio.');
        }

        // ---------------------------------------------------------
        // 1. Validaciones previas
        // ---------------------------------------------------------
        if (empty($discordRoleId)) {
            $this->error('❌ Falta el ID del rol de Discord. Define DISCORD_OFFICER_ROLE_ID en .env o usa --discord-role-id=');
            return Command::FAILURE;
        }

        $webRole = Role::where('name', $roleName)->where('guard_name', 'web')->first();
        if (!$webRole) {
            $this->error("❌ El rol web \"{$roleName}\" no existe en la base de datos. Ejecuta los seeders primero.");
            return Command::FAILURE;
        }

        // ---------------------------------------------------------
        // 2. Obtener miembros del servidor Discord (con caché del trait)
        // ---------------------------------------------------------
        $this->info('📡 Obteniendo miembros del servidor de Discord...');
        $members = $this->getGuildAllMembers();

        if (empty($members)) {
            $this->error('❌ No se pudieron obtener los miembros. Verifica el token del bot y que tenga el intent SERVER MEMBERS habilitado.');
            return Command::FAILURE;
        }

        $total = count($members);
        $this->info("📊 Procesando {$total} miembros del servidor...");
        $this->newLine();

        // ---------------------------------------------------------
        // 3. Recorrer miembros y sincronizar el rol
        // ---------------------------------------------------------
        $assigned  = 0;
        $removed   = 0;
        $skipped   = 0;
        $errors    = 0;

        foreach ($members as $member) {
            $discordUserId = $member['user_id'] ?? null;
            if (!$discordUserId) {
                $skipped++;
                continue;
            }

            // Buscar usuario local por su provider_id de Discord
            $user = User::whereHas('authProviders', function ($query) use ($discordUserId) {
                $query->where('provider', 'discord')
                      ->where('provider_id', $discordUserId);
            })->first();

            if (!$user) {
                // No está registrado en la web → ignorar
                $skipped++;
                continue;
            }

            $hasDiscordRole = in_array($discordRoleId, $member['roles'] ?? [], false);
            $hasWebRole     = $user->hasRole($roleName);

            try {
                if ($hasDiscordRole && !$hasWebRole) {
                    // Asignar
                    if ($isTest) {
                        $this->line("  [TEST] ✔ Se asignaría «{$roleName}» a {$user->name} (Discord: {$discordUserId})");
                    } else {
                        $user->assignRole($roleName);
                        $this->info("  ✅ Asignado «{$roleName}» a {$user->name}");
                        $assigned++;
                    }
                } elseif (!$hasDiscordRole && $hasWebRole) {
                    // Quitar
                    if ($isTest) {
                        $this->line("  [TEST] ✖ Se quitaría «{$roleName}» a {$user->name} (Discord: {$discordUserId})");
                    } else {
                        $user->removeRole($roleName);
                        $this->warn("  ❌ Removido «{$roleName}» a {$user->name}");
                        $removed++;
                    }
                }
                // Si ambos están alineados → nada que hacer
            } catch (\Throwable $e) {
                $errors++;
                $this->error("  ⚠️ Error con {$user->name}: " . $e->getMessage());
                Log::error('Error sincronizando rol de Discord', [
                    'user_id'         => $user->id,
                    'discord_user_id' => $discordUserId,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        // ---------------------------------------------------------
        // 4. Limpiar caché de Spatie (importante tras assign/remove)
        // ---------------------------------------------------------
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ---------------------------------------------------------
        // 5. Resumen
        // ---------------------------------------------------------
        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('✅ Sincronización completada.');
        $this->info("   Rol sincronizado : {$roleName}");
        $this->info("   Discord role ID  : {$discordRoleId}");
        $this->info("   Asignados        : {$assigned}");
        $this->info("   Removidos        : {$removed}");
        $this->info("   Omitidos         : {$skipped}");
        $this->info("   Errores          : {$errors}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        Log::info('Sincronización de roles de Discord ejecutada', [
            'role'        => $roleName,
            'discord_id'  => $discordRoleId,
            'assigned'    => $assigned,
            'removed'     => $removed,
            'skipped'     => $skipped,
            'errors'      => $errors,
            'test_mode'   => $isTest,
        ]);

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
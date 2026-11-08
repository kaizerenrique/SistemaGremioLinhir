<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personaje;
use App\Traits\Albion;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\AlliedGuild;


class IdentityController extends Controller
{
    use Albion;

    /**
     * POST /api/bot/register-character
     *
     * Registra/actualiza el vínculo de un personaje con un usuario de Discord.
     * Devuelve la decisión EFECTIVA del usuario (basada en TODOS sus personajes)
     * y el estado previo del personaje registrado (para auditoría).
     */
    public function registerCharacter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'character_id'            => 'nullable|string|max:64|required_without:character_name',
            'character_name'          => 'nullable|string|max:50|required_without:character_id',
            'discord_user_id'         => 'required|string|max:32',
            'discord_username'        => 'nullable|string|max:100',
            'birthdate'               => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'performed_by_discord_id' => 'nullable|string|max:32',
        ]);

        // ============================================================
        // 1. Resolver el personaje
        // ============================================================
        if (!empty($validated['character_id'])) {
            $character = $this->consultarpersonaje($validated['character_id']);
            if (!$character || !isset($character->Id)) {
                return $this->rejected(
                    'El personaje seleccionado no existe en Albion. '
                    . 'Intenta la búsqueda de nuevo.'
                );
            }
        } else {
            $candidates = $this->buscarpersonajepornombre($validated['character_name']);

            if (empty($candidates)) {
                return $this->rejected(
                    "No se encontró el personaje **{$validated['character_name']}** "
                    . 'en el servidor America (West). Verifica el nombre exacto.'
                );
            }

            $exactMatches = array_values(array_filter(
                $candidates,
                fn ($c) => strcasecmp($c->Name, $validated['character_name']) === 0
            ));

            if (count($exactMatches) > 1) {
                return response()->json([
                    'success'   => false,
                    'ambiguous' => true,
                    'reason'    => "Se encontraron **" . count($exactMatches) . " personajes** "
                                 . "con el nombre **{$validated['character_name']}**. "
                                 . 'Usa `/register search` para elegir el tuyo.',
                ], 200);
            }

            if (count($exactMatches) === 1) {
                $character = $exactMatches[0];
            } else {
                return $this->rejected(
                    "No se encontró coincidencia exacta para **{$validated['character_name']}**. "
                    . 'Verifica mayúsculas y ortografía, o usa `/register search` '
                    . 'para ver todos los resultados.'
                );
            }
        }

        // ============================================================
        // 2. ¿Ya está registrado por OTRO Discord user?
        // ============================================================
        $existing = Personaje::where('Id_albion', $character->Id)->first();

        if (
            $existing
            && !empty($existing->discord_user_id)
            && $existing->discord_user_id !== $validated['discord_user_id']
        ) {
            return $this->rejected(
                "El personaje **{$character->Name}** ya está vinculado a otra cuenta de Discord. "
                . 'Si crees que es un error, contacta a un oficial.'
            );
        }

        // ============================================================
        // 3. Capturar estado previo (para auditoría)
        // ============================================================
        $previousState = $this->capturePreviousState($existing);

        // ============================================================
        // 4. Resolver decisión del personaje actual
        // ============================================================
        $individual = $this->resolveIndividualDecision($character);

        // ============================================================
        // 5. Persistir vínculo
        // ============================================================
        try {
            $payload = [
                'Name'            => $character->Name,
                'GuildId'         => $character->GuildId ?? null,
                'miembro'         => $individual['decision'] === 'member',
                'allied_guild_id' => $individual['allied_guild_id'],
                'discord_user_id' => $validated['discord_user_id'],
            ];

            if (!empty($validated['birthdate'])) {
                $payload['birthdate'] = $validated['birthdate'];
            }

            Personaje::updateOrCreate(
                ['Id_albion' => $character->Id],
                $payload
            );
        } catch (\Throwable $e) {
            Log::error('Error persistiendo personaje en registerCharacter', [
                'character_id' => $character->Id,
                'error'        => $e->getMessage(),
            ]);
            return $this->rejected('Error interno al guardar el registro. Intenta más tarde.');
        }

        // ============================================================
        // 6. Decisión EFECTIVA (considera TODOS los personajes del user)
        // ============================================================
        $effective = $this->resolveEffectiveDecision($validated['discord_user_id']);

        // ============================================================
        // 7. Respuesta
        // ============================================================
        $characterPayload = [
            'name'       => $character->Name,
            'id'         => $character->Id,
            'guild_id'   => $character->GuildId ?? null,
            'guild_name' => $character->GuildName ?? null,
            'birthdate'  => $validated['birthdate'] ?? null,
        ];

        Log::info('Registro de personaje procesado', [
            'discord_user_id'    => $validated['discord_user_id'],
            'character'          => $character->Name,
            'individual_decision' => $individual['decision'],
            'effective_decision' => $effective['decision'],
            'previous_state'     => $previousState,
            'performed_by'       => $validated['performed_by_discord_id'] ?? 'self',
        ]);

        return response()->json([
            'success'              => true,
            'decision'             => $effective['decision'],
            'character_decision'   => $individual['decision'],
            'reason'               => $this->reasonFor($effective['decision'], $character),
            'character'            => $characterPayload,
            'allied_guild'         => $effective['allied_guild'],
            'previous_state'       => $previousState,
            'guild_pending_allied' => $this->isGuildPendingAllied($character->GuildId ?? null),
        ]);
    }

    private function rejected(string $reason): JsonResponse
    {
        return response()->json([
            'success'  => false,
            'decision' => 'rejected',
            'reason'   => $reason,
        ], 200);
    }

    
    /**
     * POST /api/bot/sync-registration
     *
     * Sincroniza el estado de un personaje ya registrado con Albion.
     * Devuelve la decisión EFECTIVA del usuario.
     */
    public function syncRegistration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'discord_user_id' => 'required|string|max:32',
        ]);

        $personaje = Personaje::where('discord_user_id', $validated['discord_user_id'])->first();

        if (!$personaje) {
            return response()->json([
                'success' => false,
                'reason'  => 'Este usuario de Discord no tiene un personaje registrado. '
                    . 'Usa `/register start` primero.',
            ], 200);
        }

        $character = $this->consultarpersonaje($personaje->Id_albion);

        if (!$character || !isset($character->Id)) {
            Log::warning('syncRegistration: personaje no encontrado en Albion', [
                'discord_user_id' => $validated['discord_user_id'],
                'id_albion'       => $personaje->Id_albion,
            ]);
            return response()->json([
                'success' => false,
                'reason'  => 'No se pudo consultar el estado actual del personaje en Albion. '
                    . 'Intenta más tarde.',
            ], 200);
        }

        $previousState = $this->capturePreviousState($personaje);
        $individual    = $this->resolveIndividualDecision($character);

        // Persistir estado actualizado
        $personaje->update([
            'GuildId'         => $character->GuildId ?? null,
            'miembro'         => $individual['decision'] === 'member',
            'allied_guild_id' => $individual['allied_guild_id'],
        ]);

        $effective = $this->resolveEffectiveDecision($validated['discord_user_id']);

        Log::info('syncRegistration ejecutado', [
            'discord_user_id'    => $validated['discord_user_id'],
            'character'          => $character->Name,
            'individual_decision' => $individual['decision'],
            'effective_decision' => $effective['decision'],
            'previous_state'     => $previousState,
        ]);

        return response()->json([
            'success'              => true,
            'decision'             => $effective['decision'],
            'character_decision'   => $individual['decision'],
            'is_member'            => $effective['decision'] === 'member',
            'character'            => [
                'name'       => $character->Name,
                'id'         => $character->Id,
                'guild_id'   => $character->GuildId ?? null,
                'guild_name' => $character->GuildName ?? null,
            ],
            'allied_guild'         => $effective['allied_guild'],
            'previous_state'       => $previousState,
            'guild_pending_allied' => $this->isGuildPendingAllied($character->GuildId ?? null),
        ]);
    }

    /**
     * GET /api/bot/allied-guilds
     *
     * Catálogo de gremios aliados activos y configurados.
     * El bot lo usa para saber qué roles remover cuando un usuario
     * cambia de estado.
     */
    public function alliedGuilds(): JsonResponse
    {
        $guilds = AlliedGuild::where('active', true)
            ->whereNotNull('tag')
            ->whereNotNull('discord_role_id')
            ->orderBy('guild_name')
            ->get(['guild_id', 'guild_name', 'tag', 'discord_role_id']);

        return response()->json([
            'success' => true,
            'guilds'  => $guilds,
        ]);
    }

    /**
     * GET /api/bot/members
     *
     * Roster completo para reporte de integridad + comando /admin rebuild_roles.
     */
    public function listMembers(Request $request): JsonResponse
    {
        $linhirGuildId = config('app.linhir_gremio_id');

        $guildMembers = Personaje::with('alliedGuild:id,guild_id,guild_name,tag,discord_role_id')
            ->where('miembro', true)
            ->orderBy('Name')
            ->get(['Name', 'Id_albion', 'GuildId', 'allied_guild_id', 'discord_user_id']);

        $linkedToDiscord = Personaje::with('alliedGuild:id,guild_id,guild_name,tag,discord_role_id')
            ->whereNotNull('discord_user_id')
            ->orderBy('Name')
            ->get(['Name', 'Id_albion', 'GuildId', 'allied_guild_id', 'miembro', 'discord_user_id']);

        $guildRegistered   = $guildMembers->filter(fn($p) => !empty($p->discord_user_id));
        $guildUnregistered = $guildMembers->filter(fn($p) => empty($p->discord_user_id));

        return response()->json([
            'success'  => true,
            'guild_id' => $linhirGuildId,

            'totals' => [
                'guild_members'      => $guildMembers->count(),
                'linked_total'       => $linkedToDiscord->count(),
                'guild_registered'   => $guildRegistered->count(),
                'guild_unregistered' => $guildUnregistered->count(),
            ],

            'guild_registered' => $guildRegistered->map(fn($p) => [
                'name'            => $p->Name,
                'id_albion'       => $p->Id_albion,
                'discord_user_id' => $p->discord_user_id,
                'allied_guild_id' => $p->allied_guild_id,
                'allied_guild'    => $p->alliedGuild ? [
                    'guild_name'      => $p->alliedGuild->guild_name,
                    'tag'             => $p->alliedGuild->tag,
                    'discord_role_id' => $p->alliedGuild->discord_role_id,
                ] : null,
            ])->values(),

            'guild_unregistered' => $guildUnregistered->map(fn($p) => [
                'name'      => $p->Name,
                'id_albion' => $p->Id_albion,
            ])->values(),

            'all_linked' => $linkedToDiscord->map(fn($p) => [
                'name'            => $p->Name,
                'id_albion'       => $p->Id_albion,
                'miembro'         => (bool) $p->miembro,
                'discord_user_id' => $p->discord_user_id,
                'allied_guild_id' => $p->allied_guild_id,
                'allied_guild'    => $p->alliedGuild ? [
                    'guild_name'      => $p->alliedGuild->guild_name,
                    'tag'             => $p->alliedGuild->tag,
                    'discord_role_id' => $p->alliedGuild->discord_role_id,
                ] : null,
            ])->values(),
        ]);
    }

    public function searchCharacter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'character_name' => 'required|string|max:50',
        ]);

        $candidates = $this->buscarpersonajepornombre($validated['character_name']);

        if (empty($candidates)) {
            return response()->json([
                'success' => false,
                'reason'  => "No se encontró ningún personaje con el nombre **{$validated['character_name']}**.",
            ], 200);
        }

        // Filtrar coincidencias exactas (case-insensitive)
        $exactMatches = array_filter($candidates, function ($c) use ($validated) {
            return strcasecmp($c->Name, $validated['character_name']) === 0;
        });

        // Si no hay coincidencias exactas, devolver todas las parciales
        $results = !empty($exactMatches) ? array_values($exactMatches) : $candidates;

        return response()->json([
            'success' => true,
            'count'   => count($results),
            'players' => array_map(function ($p) {
                return [
                    'id'         => $p->Id,
                    'name'       => $p->Name,
                    'guild_id'   => $p->GuildId,
                    'guild_name' => $p->GuildName,
                    'alliance_id' => $p->AllianceId,
                    'alliance_name' => $p->AllianceName,
                    'kill_fame'  => $p->KillFame,
                    'death_fame' => $p->DeathFame,
                    'avatar'     => $p->Avatar,
                ];
            }, $results),
        ]);
    }

    /**
     * GET /api/bot/members/{discord_user_id}/balance
     *
     * Devuelve el saldo personal del personaje vinculado a un discord_user_id.
     * Un solo query con `withSum` para ingresos/egresos filtrados.
     */
    public function balance(string $discordUserId): JsonResponse
    {
        $personaje = Personaje::where('discord_user_id', $discordUserId)
            ->withSum(
                ['movimientosBancarios as ingresos' => fn ($q) => $q->where('tipo', 'ingreso')],
                'monto'
            )
            ->withSum(
                ['movimientosBancarios as egresos' => fn ($q) => $q->where('tipo', 'egreso')],
                'monto'
            )
            ->first();

        if (!$personaje) {
            return response()->json([
                'success' => false,
                'reason'  => 'Este usuario no tiene un personaje vinculado. '
                    . 'Usa `/register start` primero.',
            ], 200);
        }

        $ingresos = (int) ($personaje->ingresos ?? 0);
        $egresos  = (int) ($personaje->egresos ?? 0);

        return response()->json([
            'success'         => true,
            'discord_user_id' => $discordUserId,
            'personaje_name'  => $personaje->Name,
            'balance'         => $ingresos - $egresos,
            'ingresos'        => $ingresos,
            'egresos'         => $egresos,
        ]);
    }

    // ================================================================
    //                     HELPERS INTERNOS
    // ================================================================

    /**
     * Decisión INDIVIDUAL de un personaje (sin considerar otros personajes
     * del mismo usuario). Devuelve también el allied_guild_id a persistir.
     */
    private function resolveIndividualDecision(object $character): array
    {
        $linhirGuildId = config('app.linhir_gremio_id');
        $guildId       = $character->GuildId ?? null;

        if ($guildId && $guildId === $linhirGuildId) {
            return [
                'decision'        => 'member',
                'allied_guild_id' => null,
            ];
        }

        if ($guildId) {
            $allied = AlliedGuild::where('guild_id', $guildId)
                ->where('active', true)
                ->whereNotNull('tag')
                ->whereNotNull('discord_role_id')
                ->first();

            if ($allied) {
                return [
                    'decision'        => 'allied',
                    'allied_guild_id' => $allied->id,
                ];
            }
        }

        return [
            'decision'        => 'public',
            'allied_guild_id' => null,
        ];
    }

    /**
     * Decisión EFECTIVA del usuario (considerando todos sus personajes).
     *
     * Prioridad: member > allied > public
     */
    private function resolveEffectiveDecision(string $discordUserId): array
    {
        $linhirGuildId = config('app.linhir_gremio_id');

        $personajes = Personaje::with('alliedGuild')
            ->where('discord_user_id', $discordUserId)
            ->get();

        if ($personajes->isEmpty()) {
            return ['decision' => 'public', 'allied_guild' => null];
        }

        // 1. ¿Alguno es miembro de Linhir?
        $hasMember = $personajes->contains(fn($p) => $p->GuildId === $linhirGuildId);
        if ($hasMember) {
            return ['decision' => 'member', 'allied_guild' => null];
        }

        // 2. ¿Alguno está en un aliado activo? Elige el más reciente.
        $bestAllied = $personajes
            ->filter(fn($p) => $p->alliedGuild !== null)
            ->sortByDesc('updated_at')
            ->first();

        if ($bestAllied && $bestAllied->alliedGuild) {
            $g = $bestAllied->alliedGuild;
            return [
                'decision'     => 'allied',
                'allied_guild' => [
                    'guild_id'        => $g->guild_id,
                    'guild_name'      => $g->guild_name,
                    'tag'             => $g->tag,
                    'discord_role_id' => $g->discord_role_id,
                ],
            ];
        }

        return ['decision' => 'public', 'allied_guild' => null];
    }

    /**
     * Captura el estado previo de un personaje para auditoría.
     */
    private function capturePreviousState(?Personaje $previous): array
    {
        $state = [
            'was_member'                 => (bool) ($previous?->miembro ?? false),
            'was_allied'                 => (bool) ($previous?->allied_guild_id ?? false),
            'previous_allied_role_id'    => null,
            'previous_allied_guild_name' => null,
        ];

        if ($previous?->allied_guild_id) {
            $prevAllied = AlliedGuild::find($previous->allied_guild_id);
            if ($prevAllied) {
                $state['previous_allied_role_id']    = $prevAllied->discord_role_id;
                $state['previous_allied_guild_name'] = $prevAllied->guild_name;
            }
        }

        return $state;
    }

    /**
     * ¿El gremio está en allied_guilds pero no configurado/activo?
     */
    private function isGuildPendingAllied(?string $guildId): bool
    {
        if (!$guildId) return false;
        if ($guildId === config('app.linhir_gremio_id')) return false;

        return AlliedGuild::where('guild_id', $guildId)
            ->where(function ($q) {
                $q->where('active', false)
                  ->orWhereNull('tag')
                  ->orWhereNull('discord_role_id');
            })
            ->exists();
    }

    /**
     * Mensaje humano según decisión.
     */
    private function reasonFor(string $decision, object $character): string
    {
        return match ($decision) {
            'member' => "¡Bienvenido al gremio Linhir, {$character->Name}!",
            'allied' => "El personaje {$character->Name} pertenece a un gremio aliado de Linhir.",
            default  => "El personaje {$character->Name} no es integrante actual del gremio Linhir.",
        };
    }

    private function rejected(string $reason): JsonResponse
    {
        return response()->json([
            'success'  => false,
            'decision' => 'rejected',
            'reason'   => $reason,
        ], 200);
    }
    
}

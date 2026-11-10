<?php

namespace App\Http\Controllers\Api\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Personaje;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\AlliedGuild;
use App\Services\Albion\AlbionApiService;

class IdentityController extends Controller
{
    protected AlbionApiService $api;

    public function __construct(AlbionApiService $api)
    {
        $this->api = $api;
    }

    /**
     * POST /api/bot/register-character
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

        $character = null;
        $dataSource = 'api';

        try {
            // 1. Resolver el personaje (Lógica DB-First estricta)
            if (!empty($validated['character_id'])) {
                $character = $this->resolveCharacterById($validated['character_id'], $dataSource);
                
                if (!$character) {
                    return $this->rejected('El personaje no existe en Albion o la API está temporalmente caída.');
                }
            } else {
                $character = $this->resolveCharacterByName($validated['character_name'], $dataSource);
                
                // CORRECCIÓN AQUÍ: Verificamos si es un array antes de buscar la clave 'error'
                if (is_array($character) && isset($character['error'])) {
                    return response()->json($character['error'], 200);
                }
            }

            // 2. ¿Ya está registrado por OTRO Discord user?
            $existing = Personaje::where('Id_albion', $character->Id)->first();

            if ($existing && !empty($existing->discord_user_id) && $existing->discord_user_id !== $validated['discord_user_id']) {
                return $this->rejected("El personaje **{$character->Name}** ya está vinculado a otra cuenta de Discord.");
            }

            $previousState = $this->capturePreviousState($existing);
            $individual = $this->resolveIndividualDecision($character);

            // 3. Persistir vínculo
            $payload = [
                'Name'            => $character->Name,
                'GuildId'         => $character->GuildId ?? null,
                'miembro'         => $individual['decision'] === 'member',
                'allied_guild_id' => $individual['allied_guild_id'],
                'discord_user_id' => $validated['discord_user_id'],
            ];

            // Solo actualizamos la fecha de verificación si de verdad fuimos a la API
            if ($dataSource === 'api') {
                $payload['last_verified_at'] = now();
            }

            if (!empty($validated['birthdate'])) {
                $payload['birthdate'] = $validated['birthdate'];
            }

            Personaje::updateOrCreate(['Id_albion' => $character->Id], $payload);

        } catch (\Throwable $e) {
            Log::error('registerCharacter falló por API/Datos:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->rejected(
                'Ocurrió un error interno procesando los datos. ' 
                . 'Si ya estabas registrado, intenta usar /sync. Si eres nuevo, intenta registrarte más tarde.'
            );
        }

        $effective = $this->resolveEffectiveDecision($validated['discord_user_id']);

        return response()->json([
            'success'              => true,
            'decision'             => $effective['decision'],
            'character_decision'   => $individual['decision'],
            'reason'               => $this->reasonFor($effective['decision'], $character),
            'character'            => [
                'name'       => $character->Name,
                'id'         => $character->Id,
                'guild_id'   => $character->GuildId ?? null,
                'guild_name' => $character->GuildName ?? null,
                'birthdate'  => $validated['birthdate'] ?? null,
            ],
            'allied_guild'         => $effective['allied_guild'],
            'previous_state'       => $previousState,
            'guild_pending_allied' => $this->isGuildPendingAllied($character->GuildId ?? null),
            'data_source'          => $dataSource,
        ]);
    }

    /**
     * POST /api/bot/sync-registration
     */
    public function syncRegistration(Request $request): JsonResponse
    {
        $validated = $request->validate(['discord_user_id' => 'required|string|max:32']);

        $personaje = Personaje::where('discord_user_id', $validated['discord_user_id'])->first();

        if (!$personaje) {
            return $this->rejected('Este usuario de Discord no tiene un personaje registrado. Usa `/register start` primero.');
        }

        $previousState = $this->capturePreviousState($personaje);
        $dataSource = 'api';
        $character = null;
        $individual = null;

        try {
            $apiData = $this->api->getCharacter($personaje->Id_albion);
            
            if ($apiData && isset($apiData->Id)) {
                $character = $apiData;
                $individual = $this->resolveIndividualDecision($character);
                
                $personaje->update([
                    'Name' => $character->Name,
                    'GuildId' => $character->GuildId ?? null,
                    'miembro' => $individual['decision'] === 'member',
                    'allied_guild_id' => $individual['allied_guild_id'],
                    'last_verified_at' => now(),
                ]);
            } else {
                throw new \Exception("API no devolvió datos válidos.");
            }
        } catch (\Throwable $e) {
            Log::warning('syncRegistration: fallo API, usando DB fallback', [
                'discord_user_id' => $validated['discord_user_id'],
                'error' => $e->getMessage()
            ]);
            
            $dataSource = 'db_fallback';
            
            $character = (object) [
                'Id' => $personaje->Id_albion,
                'Name' => $personaje->Name,
                'GuildId' => $personaje->GuildId,
                'GuildName' => null,
            ];
            
            $individual = $this->resolveIndividualDecision($character);
        }

        $effective = $this->resolveEffectiveDecision($validated['discord_user_id']);

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
            'data_source'          => $dataSource,
        ]);
    }

    // ================================================================
    //                     HELPERS DE RESOLUCIÓN (DB-FIRST ESTRICTO)
    // ================================================================

    private function resolveCharacterById(string $id, string &$dataSource): ?object
    {
        $dbChar = Personaje::where('Id_albion', $id)->first();

        if ($dbChar) {
            $dataSource = 'db';
            return (object) [
                'Id' => $dbChar->Id_albion,
                'Name' => $dbChar->Name,
                'GuildId' => $dbChar->GuildId,
                'GuildName' => null,
            ];
        }

        try {
            $apiChar = $this->api->getCharacter($id);
            if ($apiChar) {
                $dataSource = 'api';
                return $apiChar;
            }
        } catch (\Throwable $e) {
            throw $e;
        }

        return null;
    }

    private function resolveCharacterByName(string $name, string &$dataSource): object|array
    {
        // Búsqueda en DB ignorando mayúsculas/minúsculas para evitar fallos
        $dbChar = Personaje::whereRaw('LOWER(Name) = ?', [strtolower(trim($name))])->first();
        
        if ($dbChar) {
            $dataSource = 'db';
            return (object) [
                'Id' => $dbChar->Id_albion,
                'Name' => $dbChar->Name,
                'GuildId' => $dbChar->GuildId,
                'GuildName' => null,
            ];
        }

        try {
            $candidates = $this->api->searchCharacter($name);
            
            if (empty($candidates)) {
                return ['error' => ['success' => false, 'reason' => "No se encontró el personaje **{$name}** en Albion."]];
            }

            $exactMatches = array_values(array_filter($candidates, fn ($c) => strcasecmp($c->Name, $name) === 0));

            if (count($exactMatches) > 1) {
                return ['error' => [
                    'success' => false,
                    'ambiguous' => true,
                    'reason' => "Se encontraron " . count($exactMatches) . " personajes con ese nombre. Usa `/register search`."
                ]];
            }

            if (count($exactMatches) === 1) {
                $dataSource = 'api';
                return $exactMatches[0];
            }

            return ['error' => ['success' => false, 'reason' => "No hay coincidencia exacta para **{$name}**."]];

        } catch (\Throwable $e) {
            throw $e;
        }
    }

    // ================================================================
    //                     HELPERS INTERNOS ORIGINALES
    // ================================================================

    private function rejected(string $reason): JsonResponse
    {
        return response()->json([
            'success'  => false,
            'decision' => 'rejected',
            'reason'   => $reason,
        ], 200);
    }

    private function resolveIndividualDecision(object $character): array
    {
        $linhirGuildId = config('app.linhir_gremio_id');
        $guildId       = $character->GuildId ?? null;

        if ($guildId && $guildId === $linhirGuildId) {
            return ['decision' => 'member', 'allied_guild_id' => null];
        }

        if ($guildId) {
            $allied = AlliedGuild::where('guild_id', $guildId)->where('active', true)->whereNotNull('tag')->whereNotNull('discord_role_id')->first();
            if ($allied) {
                return ['decision' => 'allied', 'allied_guild_id' => $allied->id];
            }
        }

        return ['decision' => 'public', 'allied_guild_id' => null];
    }

    private function resolveEffectiveDecision(string $discordUserId): array
    {
        $linhirGuildId = config('app.linhir_gremio_id');
        $personajes = Personaje::with('alliedGuild')->where('discord_user_id', $discordUserId)->get();

        if ($personajes->isEmpty()) return ['decision' => 'public', 'allied_guild' => null];

        $hasMember = $personajes->contains(fn($p) => $p->GuildId === $linhirGuildId);
        if ($hasMember) return ['decision' => 'member', 'allied_guild' => null];

        $bestAllied = $personajes->filter(fn($p) => $p->alliedGuild !== null)->sortByDesc('updated_at')->first();
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

    private function isGuildPendingAllied(?string $guildId): bool
    {
        if (!$guildId) return false;
        if ($guildId === config('app.linhir_gremio_id')) return false;

        return AlliedGuild::where('guild_id', $guildId)
            ->where(function ($q) {
                $q->where('active', false)->orWhereNull('tag')->orWhereNull('discord_role_id');
            })->exists();
    }

    private function reasonFor(string $decision, object $character): string
    {
        return match ($decision) {
            'member' => "¡Bienvenido al gremio Linhir, {$character->Name}!",
            'allied' => "El personaje {$character->Name} pertenece a un gremio aliado de Linhir.",
            default  => "El personaje {$character->Name} no es integrante actual del gremio Linhir.",
        };
    }
}
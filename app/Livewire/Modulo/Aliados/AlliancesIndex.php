<?php

namespace App\Livewire\Modulo\Aliados;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AlliedGuild;
use App\Services\Albion\AllianceLookupService;
use App\Services\Albion\GuildLookupService;
use App\Services\Discord\DiscordRoleService;

class AlliancesIndex extends Component
{
    use WithPagination;

    public string $buscar = '';

    // Modal crear/editar gremio
    public bool $showModal  = false;
    public ?int $editingId  = null;

    public string $guild_id        = '';
    public string $guild_name      = '';
    public string $tag             = '';
    public string $color           = '#9A8667';
    public string $notes           = '';
    public ?string $discord_role_id = null;
    public bool $roleFound          = false;   // true si detectamos rol existente

    // Confirmación de desactivación
    public bool $showConfirm = false;
    public ?int $confirmId   = null;

    // Sincronización desde Albion
    public bool $showSyncModal = false;
    public array $syncDiff = [];
    public array $syncSelected = [];
    public ?string $syncError = null;

    // Feedback
    public ?string $successMessage = null;
    public ?string $errorMessage   = null;

    public function updatingBuscar(): void { $this->resetPage(); }

    public function render()
    {
        $guilds = AlliedGuild::query()
            ->when($this->buscar, fn($q) =>
                $q->where('guild_name', 'like', "%{$this->buscar}%")
                  ->orWhere('tag', 'like', "%{$this->buscar}%")
            )
            ->orderByRaw('tag IS NULL ASC')   // configurados primero
            ->orderBy('active', 'desc')
            ->orderBy('guild_name')
            ->paginate(15);

        return view('livewire.modulo.aliados.alliances-index', compact('guilds'));
    }

    // ================================================================
    //                    SINCRONIZACIÓN DESDE ALBION
    // ================================================================

    public function openSyncModal(AllianceLookupService $alliances): void
    {
        $this->syncError = null;
        $this->syncDiff  = [];

        $hrl = $alliances->findHrl();
        if (!$hrl) {
            $this->syncError = 'No se pudo consultar la alianza en Albion. '
                             . 'Verifica HRL_ALLIANCE_ID en el .env o tu conexión.';
            $this->showSyncModal = true;
            return;
        }

        $albionGuilds  = collect($hrl['guilds']);
        $albionIds     = $albionGuilds->pluck('id')->all();

        $dbGuilds      = AlliedGuild::all()->keyBy('guild_id');
        $dbIds         = $dbGuilds->keys()->all();

        // 1. En Albion, NO en DB → se pueden importar
        $missing = $albionGuilds
            ->filter(fn($g) => !isset($dbGuilds[$g['id']]))
            ->values()
            ->all();

        // 2. En DB, NO en Albion → advertencia (salió de la alianza)
        $orphan = collect($dbIds)
            ->diff($albionIds)
            ->map(fn($id) => $dbGuilds[$id])
            ->filter(fn($g) => !$g->is_linhir)  // Linhir no se puede "salir" de sí mismo
            ->values()
            ->map(fn($g) => [
                'guild_id'   => $g->guild_id,
                'guild_name' => $g->guild_name,
                'tag'        => $g->tag,
            ])
            ->all();

        // 3. En ambos → ok
        $matched = $albionGuilds
            ->filter(fn($g) => isset($dbGuilds[$g['id']]))
            ->values()
            ->map(fn($g) => [
                'guild_id'    => $g['id'],
                'guild_name'  => $g['name'],
                'tag'         => $dbGuilds[$g['id']]->tag,
                'active'      => $dbGuilds[$g['id']]->active,
            ])
            ->all();

        $this->syncDiff = [
            'alliance' => $hrl,
            'missing'  => $missing,
            'orphan'   => $orphan,
            'matched'  => $matched,
        ];

        // Por defecto, preseleccionar todos los faltantes
        $this->syncSelected = array_column($missing, 'id');

        $this->showSyncModal = true;
    }

    public function toggleSyncSelected(string $guildId): void
    {
        if (in_array($guildId, $this->syncSelected, true)) {
            $this->syncSelected = array_values(
                array_diff($this->syncSelected, [$guildId])
            );
        } else {
            $this->syncSelected[] = $guildId;
        }
    }

    public function importSelected(): void
    {
        if (empty($this->syncSelected)) {
            $this->syncError = 'No hay gremios seleccionados para importar.';
            return;
        }

        $albionGuilds = collect($this->syncDiff['missing'] ?? [])
            ->keyBy('id');

        $imported = 0;
        foreach ($this->syncSelected as $guildId) {
            $g = $albionGuilds->get($guildId);
            if (!$g) continue;

            AlliedGuild::firstOrCreate(
                ['guild_id' => $g['id']],
                [
                    'guild_name'    => $g['name'],
                    'alliance_name' => $this->syncDiff['alliance']['name'] ?? null,
                    'active'        => false,    // ← pendiente de configuración
                    'color'         => '#9A8667',
                ]
            );
            $imported++;
        }

        $this->showSyncModal  = false;
        $this->successMessage = "{$imported} gremio(s) importado(s) desde Albion. "
                              . "Deben configurarse y activarse manualmente.";
    }

    // ================================================================
    //                    CREAR / CONFIGURAR GREMIO
    // ================================================================

    public function openConfigure(int $id, DiscordRoleService $discord): void
    {
        $g = AlliedGuild::findOrFail($id);

        $this->editingId       = $g->id;
        $this->guild_id        = $g->guild_id;
        $this->guild_name      = $g->guild_name;
        $this->tag             = trim((string) $g->tag, '[]');
        $this->color           = $g->color;
        $this->notes           = $g->notes ?? '';
        $this->discord_role_id = $g->discord_role_id;
        $this->roleFound       = false;
        $this->resetValidation();

        // Si hay TAG pero no rol, buscar uno existente por nombre
        if (!empty($this->tag) && empty($this->discord_role_id)) {
            $existing = $discord->findRoleByName($this->tag);
            if ($existing) {
                $this->discord_role_id = $existing['id'];
                $this->roleFound       = true;
            }
        }

        $this->showModal = true;
    }

    public function newGuild(): void
    {
        $this->editingId       = null;
        $this->guild_id        = '';
        $this->guild_name      = '';
        $this->tag             = '';
        $this->color           = '#9A8667';
        $this->notes           = '';
        $this->discord_role_id = null;
        $this->roleFound       = false;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function lookupInAlbion(GuildLookupService $lookup): void
    {
        $this->errorMessage = null;
        $guild = $lookup->findById(trim($this->guild_id));

        if (!$guild) {
            $this->errorMessage = "Gremio con ID «{$this->guild_id}» no encontrado en Albion.";
            return;
        }

        $this->guild_name = $guild['name'] ?? '';
    }

    /**
     * Detecta si existe un rol en Discord con el nombre del TAG.
     * El admin puede aceptarlo (usar ese rol) o rechazarlo (crear uno nuevo).
     */
    public function detectRole(DiscordRoleService $discord): void
    {
        $this->errorMessage = null;
        $this->roleFound    = false;
        $this->discord_role_id = null;

        $tagName = strtoupper(trim($this->tag));
        if ($tagName === '') {
            $this->errorMessage = 'Define el TAG primero.';
            return;
        }

        $existing = $discord->findRoleByName($tagName);
        if ($existing) {
            $this->discord_role_id = $existing['id'];
            $this->roleFound       = true;
        } else {
            $this->successMessage = "No existe rol «{$tagName}» en Discord. Se creará uno nuevo al guardar.";
        }
    }

    public function save(
        GuildLookupService $lookup,
        DiscordRoleService $discord,
    ): void {
        $this->errorMessage = null;
        $this->tag = strtoupper(trim($this->tag));

        $this->validate([
            'guild_id'   => 'required|string|max:64',
            'guild_name' => 'required|string|max:255',
            'tag'        => 'required|string|regex:/^[A-Z0-9]{2,4}$/|unique:allied_guilds,tag,' . ($this->editingId ?? 'NULL'),
            'color'      => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'notes'      => 'nullable|string|max:1000',
        ], [
            'tag.regex'  => 'TAG: 2-4 caracteres alfanuméricos en mayúsculas.',
            'tag.unique' => 'Ya existe un gremio con ese TAG.',
        ]);

        // Verificar en Albion
        $albionGuild = $lookup->findById($this->guild_id);
        if (!$albionGuild) {
            $this->errorMessage = 'Gremio no encontrado en Albion.';
            return;
        }

        // Resolver el rol: usar existente o crear
        $role = null;

        if (!empty($this->discord_role_id)) {
            // El admin ya había detectado un rol existente
            $role = ['id' => $this->discord_role_id];
        } else {
            // findOrCreateRole busca por nombre primero, crea si no existe
            $role = $discord->findOrCreateRole(
                name:  $this->tag,
                color: $this->color,
            );
        }

        if (!$role || empty($role['id'])) {
            $this->errorMessage = 'No se pudo asignar ni crear el rol en Discord. '
                . 'Verifica que el bot tenga "Manage Roles" y que su rol esté '
                . 'por encima en la jerarquía.';
            return;
        }

        $provider = auth()->user()->authProviders()->where('provider', 'discord')->first();

        $data = [
            'guild_id'               => $albionGuild['id'],
            'guild_name'             => $albionGuild['name'],
            'alliance_name'          => $albionGuild['allianceName'] ?? null,
            'tag'                    => "[{$this->tag}]",
            'discord_role_id'        => $role['id'],
            'color'                  => $this->color,
            'notes'                  => $this->notes,
            'verified_at'            => now(),
            'verified_by_discord_id' => $provider?->provider_id,
        ];

        if ($this->editingId) {
            AlliedGuild::findOrFail($this->editingId)->update($data);
            $this->successMessage = "Gremio «{$albionGuild['name']}» actualizado.";
        } else {
            // Al crear, queda inactivo hasta que el admin pulse "Activar"
            $data['active'] = false;
            AlliedGuild::create($data);
            $this->successMessage = "Gremio «{$albionGuild['name']}» guardado. "
                                  . "Está pendiente de activación.";
        }

        $this->showModal = false;
        $this->resetForm();
    }

    // ================================================================
    //                    ACTIVAR / DESACTIVAR
    // ================================================================

    public function activate(int $id): void
    {
        $g = AlliedGuild::findOrFail($id);

        if (!$g->is_configured) {
            $this->errorMessage = "No se puede activar «{$g->guild_name}»: "
                . "falta TAG o rol de Discord. Configúralo primero.";
            return;
        }

        $g->update(['active' => true, 'verified_at' => now()]);
        $this->successMessage = "Gremio «{$g->guild_name}» activado. Sus miembros recibirán @HRL.";
    }

    public function confirmDeactivate(int $id): void
    {
        $this->confirmId   = $id;
        $this->showConfirm = true;
    }

    public function deactivate(): void
    {
        $g = AlliedGuild::findOrFail($this->confirmId);
        $g->update(['active' => false]);

        $this->showConfirm    = false;
        $this->confirmId      = null;
        $this->successMessage = "Gremio «{$g->guild_name}» desactivado.";
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'guild_id', 'guild_name', 'tag', 'notes',
            'discord_role_id', 'roleFound',
        ]);
        $this->color = '#9A8667';
        $this->resetValidation();
    }
}

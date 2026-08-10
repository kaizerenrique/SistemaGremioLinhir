<?php

namespace App\Livewire\Modulo\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\DiscordComan;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscordServerInfo extends Component
{
    use DiscordComan, WithPagination;

    public $search = '';
    public $roleFilter = '';
    public $perPage = 20;

    public $guildInfo;
    public $roles;
    public $channels;
    public $allMembers = [];

    protected $queryString = ['search', 'roleFilter', 'perPage'];

    public function mount()
    {
        $this->guildInfo = $this->getGuildInfo();
        $this->roles = $this->getGuildRoles();
        $this->channels = $this->getGuildChannels();
        $this->allMembers = $this->getGuildAllMembers();
    }

    // Reiniciar página al cambiar filtros o búsqueda
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingRoleFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function getFilteredMembersProperty()
    {
        $collection = collect($this->allMembers);

        if (!empty($this->search)) {
            $collection = $collection->filter(function ($member) {
                return stripos($member['nickname'], $this->search) !== false ||
                       stripos($member['username'], $this->search) !== false;
            });
        }

        if (!empty($this->roleFilter)) {
            $collection = $collection->filter(function ($member) {
                return in_array($this->roleFilter, $member['roles']);
            });
        }

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = (int) $this->perPage;
        $items = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $total = $collection->count();

        return new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    public function getStatsProperty()
    {
        $total = count($this->allMembers);
        $registered = collect($this->allMembers)->filter(function ($m) {
            return $m['is_registered'];
        })->count();
        $channelsText = collect($this->channels)->filter(function ($ch) {
            return $ch['type'] === 0; // text
        })->count();
        $channelsVoice = collect($this->channels)->filter(function ($ch) {
            return $ch['type'] === 2; // voice
        })->count();
        $rolesCount = count($this->roles);

        return [
            'total_members' => $total,
            'registered_web' => $registered,
            'not_registered' => $total - $registered,
            'text_channels' => $channelsText,
            'voice_channels' => $channelsVoice,
            'roles_count' => $rolesCount,
            'guild_name' => $this->guildInfo['name'] ?? 'Desconocido',
            'guild_icon' => $this->guildInfo['icon'] ?? null,
        ];
    }

    public function render()
    {
        $stats = $this->stats;
        $members = $this->filteredMembers;

        return view('livewire.modulo.admin.discord-server-info', [
            'stats' => $stats,
            'members' => $members,
            'roles' => $this->roles,
        ]);
    }
}

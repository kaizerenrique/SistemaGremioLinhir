<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlliedGuild extends Model
{
    protected $fillable = [
        'guild_id', 'guild_name', 'alliance_name', 'tag',
        'discord_role_id', 'color', 'active',
        'verified_at', 'verified_by_discord_id', 'notes',
    ];

    protected $casts = [
        'active'      => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function getIsLinhirAttribute(): bool
    {
        return $this->guild_id === config('app.linhir_gremio_id');
    }

    public function getNicknamePrefixAttribute(): string
    {
        return trim((string) $this->tag, '[]');
    }

    /** Un gremio está configurado si tiene TAG y rol asignados. */
    public function getIsConfiguredAttribute(): bool
    {
        return !empty($this->tag) && !empty($this->discord_role_id);
    }

    public function rolesToApply(): array
    {
        $roles = [
            config('app.hrl_role_id'),
            config('app.publico_role_id'),
            $this->discord_role_id,
        ];

        if ($this->is_linhir) {
            $roles[] = config('app.linhir_role_id');
        }

        return array_values(array_filter($roles));
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    public function scopePendingConfiguration($q)
    {
        return $q->where('active', false)->whereNull('tag');
    }
}

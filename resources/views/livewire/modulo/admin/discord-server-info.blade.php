<div>
    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Miembros totales</div>
            <div class="text-2xl font-bold text-primary">{{ $stats['total_members'] }}</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Registrados en web</div>
            <div class="text-2xl font-bold text-success">{{ $stats['registered_web'] }}</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">No registrados</div>
            <div class="text-2xl font-bold text-error">{{ $stats['not_registered'] }}</div>
        </div>
        <div class="bg-base-300 p-4 rounded-lg border border-base-300">
            <div class="text-content-light text-sm">Canales de voz / texto</div>
            <div class="text-2xl font-bold text-accent">{{ $stats['voice_channels'] }} / {{ $stats['text_channels'] }}</div>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="flex flex-wrap gap-4 mb-4">
        <div class="flex-1 min-w-[200px]">
            <x-input type="search" wire:model.live="search" placeholder="Buscar por nombre o apodo..." />
        </div>
        <div>
            <select wire:model.live="roleFilter" class="bg-base-200 border border-base-300 rounded px-3 py-2 text-content-light">
                <option value="">Todos los roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="perPage" class="bg-base-200 border border-base-300 rounded px-3 py-2 pr-8 text-content-light">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div>
            <button wire:click="$refresh" class="bg-primary hover:bg-hover-primary text-white px-4 py-2 rounded">
                Actualizar
            </button>
        </div>
    </div>

    <!-- Tabla de miembros -->
    <div class="overflow-x-auto bg-base-300 rounded-lg border border-base-300">
        <table class="w-full">
            <thead class="border-b border-primary/30">
                <tr class="text-left text-content-light text-xs uppercase">
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Apodo</th>
                    <th class="px-4 py-3">Roles</th>
                    <th class="px-4 py-3 text-center">Registrado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-base-200">
                @forelse($members as $member)
                    <tr class="hover:bg-base-200/50 transition-colors">
                        <td class="px-4 py-3 text-content-light flex items-center gap-2">
                            <img src="https://cdn.discordapp.com/avatars/{{ $member['user_id'] }}/{{ $member['avatar'] }}.png?size=32" 
                                 alt="{{ $member['username'] }}" 
                                 class="w-8 h-8 rounded-full">
                            {{ $member['username'] }}#{{ $member['discriminator'] }}
                        </td>
                        <td class="px-4 py-3 text-content-light">{{ $member['nickname'] }}</td>
                        <td class="px-4 py-3 text-content-light">
                            <div class="flex flex-wrap gap-1">
                                @foreach($member['roles'] as $roleId)
                                    @php
                                        $roleName = collect($roles)->firstWhere('id', $roleId)['name'] ?? '?';
                                    @endphp
                                    <span class="bg-primary/10 text-primary px-2 py-0.5 rounded-full text-xs">{{ $roleName }}</span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($member['is_registered'])
                                <span class="text-success">✅</span>
                            @else
                                <span class="text-error">❌</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-content-light/70">
                            No se encontraron miembros con esos filtros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $members->links('vendor.pagination.tailwind') }}
    </div>
</div>

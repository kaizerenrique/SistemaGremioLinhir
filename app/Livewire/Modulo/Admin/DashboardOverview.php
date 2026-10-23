<?php

namespace App\Livewire\Modulo\Admin;

use Livewire\Component;
use App\Models\Battle;
use App\Models\GuildTransaction;
use App\Models\Cta;
use App\Models\TaskReport;
use App\Models\WithdrawalRequest;
use App\Models\GoldPrice;
use App\Models\KillEvent;
use App\Models\Personaje;
use App\Models\EspecialidadSemanal;
use Carbon\Carbon;
use App\Traits\DiscordComan;


class DashboardOverview extends Component
{
    use DiscordComan; // 👈 Usamos el trait

    // KPIs
    public $goldPrice;
    public $guildBalance;
    public $weeklyKdr;
    
    // Batallas y CTAs
    public $latestBattles;
    public $nextCta;
    
    // Comunidad (Albion)
    public $totalMembers = 0;
    public $lastJoinedMember;
    public $linkedMembers = 0;
    public $linkPercentage = 0;
    public $topGatherer;

    // Discord
    public $discordGuildInfo = null;
    public $discordMembersCount = 0;

    // Administración
    public $pendingTasks = 0;
    public $pendingWithdrawals = 0;

    public function mount()
    {
        // 1. Datos Económicos
        $this->goldPrice = GoldPrice::latest('id')->first();
        $this->guildBalance = GuildTransaction::balance();

        // 2. K/D Semanal
        $startOfWeek = Carbon::now()->startOfWeek();
        $linhirId = config('app.linhir_gremio_id');
        
        $weeklyStats = KillEvent::where('timestamp', '>=', $startOfWeek)
            ->selectRaw('
                SUM(CASE WHEN killer_guild_id = ? THEN 1 ELSE 0 END) as kills,
                SUM(CASE WHEN victim_guild_id = ? THEN 1 ELSE 0 END) as deaths
            ', [$linhirId, $linhirId])
            ->first();

        $kills = $weeklyStats->kills ?? 0;
        $deaths = $weeklyStats->deaths ?? 0;
        $this->weeklyKdr = $deaths > 0 ? round($kills / $deaths, 2) : $kills;

        // 3. Batallas y CTAs
        $this->latestBattles = Battle::orderBy('start_time', 'desc')->limit(5)->get();
        
        $this->nextCta = Cta::where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->first();

        // 4. Datos de la Comunidad (Albion)
        $this->totalMembers = Personaje::where('miembro', true)->count();
        
        $this->lastJoinedMember = Personaje::where('miembro', true)
            ->orderBy('created_at', 'desc')
            ->first();
            
        $this->linkedMembers = Personaje::where('miembro', true)
            ->whereNotNull('discord_user_id')
            ->count();

        if ($this->totalMembers > 0) {
            $this->linkPercentage = round(($this->linkedMembers / $this->totalMembers) * 100);
        }

        // 5. Datos de Discord
        $this->discordGuildInfo = $this->getGuildInfo();
        $allDiscordMembers = $this->getGuildAllMembers();
        $this->discordMembersCount = is_array($allDiscordMembers) ? count($allDiscordMembers) : 0;

        // 6. Top Recolector
        $semanaInicio = now()->startOfWeek()->toDateString();
        $this->topGatherer = EspecialidadSemanal::selectRaw('personajes.Name, MAX(valor_fin - valor_inicio) as ganancia')
            ->leftJoin('personajes', 'especialidades_semanales.personaje_id', '=', 'personajes.id')
            ->where('personajes.miembro', true)
            ->where('especialidades_semanales.semana_inicio', $semanaInicio)
            ->whereIn('especialidades_semanales.tipo', ['Fiber', 'Hide', 'Ore', 'Rock', 'Wood'])
            ->groupBy('personajes.Name')
            ->orderByDesc('ganancia')
            ->first();

        // 7. Pendientes (Solo Oficiales/Admins)
        if (auth()->user()->can('manage_tasks')) {
            $this->pendingTasks = TaskReport::where('status', 'pending')->count();
            $this->pendingWithdrawals = WithdrawalRequest::where('status', 'pending')->count();
        }
    }

    public function render()
    {
        return view('livewire.modulo.admin.dashboard-overview');
    }
}

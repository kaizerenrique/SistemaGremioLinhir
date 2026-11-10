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
use App\Models\AlliedGuild;
use App\Models\Activity;


class DashboardOverview extends Component
{
    use DiscordComan;

    // KPIs
    public $goldPrice;
    public $guildBalance;
    public $weeklyKdr;
    
    // Batallas y CTAs
    public $latestBattles;
    public $nextCta;
    
    // Comunidad (Albion)
    public $totalMembers = 0;
    public $linkedMembers = 0;
    public $linkPercentage = 0;
    public $topGatherer;
    public $alliedGuildsCount = 0;
    public $alliedMembersCount = 0;

    // Flujo de miembros
    public $recentJoins = [];
    public $recentLeaves = [];

    // Finanzas semanales
    public $weeklyActivitySilver = 0;
    public $weeklyGuildIncome = 0;
    public $weeklyGuildExpense = 0;

    // Discord
    public $discordGuildInfo = null;
    public $discordMembersCount = 0;

    // Administración
    public $pendingTasks = 0;
    public $pendingWithdrawals = 0;

    public function mount()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $linhirId = config('app.linhir_gremio_id');

        // 1. Datos Económicos
        $this->goldPrice = GoldPrice::latest('id')->first();
        $this->guildBalance = GuildTransaction::balance();

        // Finanzas Semanales
        $this->weeklyActivitySilver = Activity::where('created_at', '>=', $startOfWeek)
            ->sum('total_distributed');
            
        $this->weeklyGuildIncome = GuildTransaction::where('type', 'ingreso')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('amount');
            
        $this->weeklyGuildExpense = GuildTransaction::where('type', 'egreso')
            ->where('created_at', '>=', $startOfWeek)
            ->sum('amount');

        // 2. K/D Semanal
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

        // 4. Datos de la Comunidad y Aliados
        $this->totalMembers = Personaje::where('miembro', true)->count();
        $this->linkedMembers = Personaje::where('miembro', true)->whereNotNull('discord_user_id')->count();
        
        if ($this->totalMembers > 0) {
            $this->linkPercentage = round(($this->linkedMembers / $this->totalMembers) * 100);
        }

        $this->alliedGuildsCount = AlliedGuild::where('active', true)->count();
        $this->alliedMembersCount = Personaje::whereNotNull('allied_guild_id')->count();

        // Flujo de miembros (últimos 5)
        $this->recentJoins = Personaje::where('miembro', true)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Para las salidas, buscamos personajes que ya NO son miembros, pero se actualizaron recientemente
        $this->recentLeaves = Personaje::where('miembro', false)
            ->where('updated_at', '>=', now()->subDays(15)) // Solo los últimos 15 días
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // 5. Datos de Discord
        $this->discordGuildInfo = $this->getGuildInfo();
        $allDiscordMembers = $this->getGuildAllMembers();
        $this->discordMembersCount = is_array($allDiscordMembers) ? count($allDiscordMembers) : 0;

        // 6. Top Recolector
        $this->topGatherer = EspecialidadSemanal::selectRaw('personajes.Name, MAX(valor_fin - valor_inicio) as ganancia')
            ->leftJoin('personajes', 'especialidades_semanales.personaje_id', '=', 'personajes.id')
            ->where('personajes.miembro', true)
            ->where('especialidades_semanales.semana_inicio', $startOfWeek->toDateString())
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

<?php

namespace App\Livewire\Modulo\Ctas;

use Livewire\Component;
use App\Models\Cta;
use Livewire\WithPagination;
use Carbon\Carbon;

class CtasIndex extends Component
{
    use WithPagination;

    // ------- Filtros de lista (existentes) -------
    public $filter = 'scheduled';
    public $buscar = '';

    // ------- Modo de vista y semana activa -------
    public string $viewMode  = 'list';   // 'list' | 'calendar'
    public string $weekStart = '';       // Y-m-d del lunes mostrado

    protected $queryString = [
        'filter'    => ['except' => 'scheduled'],
        'buscar'    => ['except' => ''],
        'viewMode'  => ['except' => 'list'],
        'weekStart' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Si la URL no trae un weekStart válido, forzamos el lunes actual
        if (empty($this->weekStart) || !strtotime($this->weekStart)) {
            $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        } else {
            // Normalizar a lunes por si el usuario pasa un día cualquiera
            $this->weekStart = Carbon::parse($this->weekStart)
                ->startOfWeek(Carbon::MONDAY)
                ->format('Y-m-d');
        }
    }

    public function updatingFilter(): void
    {
        $this->resetPage();
    }
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    // =========================================================
    //                    CAMBIO DE MODO
    // =========================================================
    public function setViewMode(string $mode): void
    {
        if (!in_array($mode, ['list', 'calendar'], true)) {
            return;
        }
        $this->viewMode = $mode;
        $this->resetPage();
    }

    // =========================================================
    //                 NAVEGACIÓN SEMANAL
    // =========================================================
    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)
            ->subWeek()
            ->startOfWeek(Carbon::MONDAY)
            ->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)
            ->addWeek()
            ->startOfWeek(Carbon::MONDAY)
            ->format('Y-m-d');
    }

    public function goToCurrentWeek(): void
    {
        $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    // =========================================================
    //        DÍAS DE LA SEMANA CON SUS CTAs AGRUPADAS
    // =========================================================
    public function getWeekDaysProperty(): array
    {
        $start = Carbon::parse($this->weekStart)->startOfWeek(Carbon::MONDAY);
        $end   = $start->copy()->endOfWeek(Carbon::SUNDAY);

        $ctas = Cta::with('composition:id,name')
            ->withCount([
                'participants as confirmed_count' =>
                fn($q) => $q->where('status', 'confirmed'),
            ])
            ->whereBetween('start_time', [$start, $end])
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn($c) => $c->start_time->format('Y-m-d'));

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $start->copy()->addDays($i);
            $key = $day->format('Y-m-d');

            $days[] = [
                'date'    => $day,
                'isToday' => $day->isToday(),
                'ctas'    => $ctas->get($key, collect()),
            ];
        }

        return $days;
    }

    // =========================================================
    //      PRÓXIMAS CTAs (fuera de la semana, panel lateral)
    // =========================================================
    public function getUpcomingCtasProperty()
    {
        return Cta::with('composition:id,name')
            ->withCount([
                'participants as confirmed_count' =>
                fn($q) => $q->where('status', 'confirmed'),
            ])
            ->where('status', 'scheduled')
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->limit(5)
            ->get();
    }

    // =========================================================
    //                        RENDER
    // =========================================================
    public function render()
    {
        $ctas      = null;
        $weekDays  = [];
        $weekStart = $weekEnd = null;
        $upcoming  = collect();

        if ($this->viewMode === 'list') {
            $query = Cta::with('composition:id,name')
                ->withCount([
                    'participants as confirmed_count' =>
                    fn($q) => $q->where('status', 'confirmed'),
                ]);

            if ($this->filter === 'scheduled') {
                $query->where('status', 'scheduled')
                    ->where('start_time', '>', now());
            } elseif ($this->filter === 'past') {
                $query->where('start_time', '<=', now());
            }

            if ($this->buscar) {
                $query->where('title', 'like', "%{$this->buscar}%");
            }

            $ctas = $query->orderBy('start_time', 'desc')->paginate(15);
        } else {
            $weekDays  = $this->weekDays;
            $weekStart = Carbon::parse($this->weekStart);
            $weekEnd   = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
            $upcoming  = $this->upcomingCtas;
        }

        return view('livewire.modulo.ctas.ctas-index', [
            'ctas'             => $ctas,
            'weekDays'         => $weekDays,
            'displayWeekStart' => $weekStart, 
            'displayWeekEnd'   => $weekEnd, 
            'upcoming'         => $upcoming,
        ]);
    }
}

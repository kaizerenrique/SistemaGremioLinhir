<?php

namespace App\Livewire\Modulo\Birthdays;

use Livewire\Component;
use App\Models\Personaje;
use Carbon\Carbon;

class BirthdayCalendar extends Component
{
    public $currentDate;
    public $monthName;
    public $year;
    
    // Matriz que contendrá la estructura del calendario
    public $calendarDays = [];
    
    // Listado plano para mostrar debajo del calendario
    public $birthdaysThisMonth = [];

    public function mount()
    {
        // Iniciar en el mes actual
        $this->currentDate = Carbon::now();
        $this->generateCalendar();
    }

    public function nextMonth()
    {
        $this->currentDate->addMonth();
        $this->generateCalendar();
    }

    public function previousMonth()
    {
        $this->currentDate->subMonth();
        $this->generateCalendar();
    }

    public function goToCurrentMonth()
    {
        $this->currentDate = Carbon::now();
        $this->generateCalendar();
    }

    private function generateCalendar()
    {
        $this->monthName = $this->currentDate->translatedFormat('F'); // Ej: "Noviembre"
        $this->year = $this->currentDate->year;

        // 1. Obtener los cumpleaños del mes actual
        // Filtramos solo miembros activos (miembro = true)
        $birthdays = Personaje::where('miembro', true)
            ->whereNotNull('birthdate')
            ->whereMonth('birthdate', $this->currentDate->month)
            ->orderByRaw('DAY(birthdate) ASC')
            ->get();

        // Agrupar por día del mes
        $birthdaysByDay = $birthdays->groupBy(function ($personaje) {
            return Carbon::parse($personaje->birthdate)->day;
        });

        // Guardar listado plano para la lista inferior
        $this->birthdaysThisMonth = $birthdays;

        // 2. Generar la cuadrícula (Grid)
        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();
        
        // Días totales del mes
        $daysInMonth = $startOfMonth->daysInMonth;
        
        // Día de la semana en que cae el día 1 (1 = Lunes, 7 = Domingo)
        $startDayOfWeek = $startOfMonth->dayOfWeekIso;

        $days = [];

        // Rellenar días vacíos al inicio (para que el día 1 caiga en su columna correcta)
        for ($i = 1; $i < $startDayOfWeek; $i++) {
            $days[] = [
                'day' => null,
                'isToday' => false,
                'birthdays' => [],
            ];
        }

        // Rellenar los días del mes
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = [
                'day' => $i,
                'isToday' => $this->currentDate->isCurrentMonth() && $i === Carbon::now()->day,
                'birthdays' => $birthdaysByDay->get($i, []), // Array de personajes que cumplen años este día
            ];
        }

        $this->calendarDays = $days;
    }

    
    public function render()
    {
        return view('livewire.modulo.birthdays.birthday-calendar');
    }
}

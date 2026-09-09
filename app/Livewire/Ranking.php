<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UserPoint;
use App\Models\Personaje;
use Livewire\WithPagination;

class Ranking extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 15],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Consulta base: obtener puntos con relación a personaje
        $query = UserPoint::query()
            ->leftJoin('personajes', 'user_points.discord_user_id', '=', 'personajes.discord_user_id')
            ->select(
                'user_points.discord_user_id',
                'user_points.total_points',
                'personajes.Name as personaje_name',
                'personajes.miembro'
            )
            ->orderBy('user_points.total_points', 'desc');

        // Aplicar búsqueda por nombre de personaje (si existe)
        if (!empty($this->search)) {
            $query->where('personajes.Name', 'like', '%' . $this->search . '%');
        }

        // Obtener resultados paginados
        $rankings = $query->paginate($this->perPage);

        // Para cada registro, si no tiene nombre de personaje, mostrar su discord_user_id
        $rankings->getCollection()->transform(function ($item) {
            if (empty($item->personaje_name)) {
                // Intentar buscar un personaje con ese discord_user_id (si no se encontró en el join)
                $personaje = Personaje::where('discord_user_id', $item->discord_user_id)
                    ->where('miembro', true) // primero intentar el que sea miembro
                    ->first();
                if (!$personaje) {
                    $personaje = Personaje::where('discord_user_id', $item->discord_user_id)->first();
                }
                $item->personaje_name = $personaje ? $personaje->Name : $item->discord_user_id;
                $item->miembro = $personaje ? $personaje->miembro : false;
            }
            return $item;
        });

        return view('livewire.ranking', [
            'rankings' => $rankings,
        ]);
    }
}

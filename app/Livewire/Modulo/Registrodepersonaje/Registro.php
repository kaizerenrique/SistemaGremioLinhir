<?php

namespace App\Livewire\Modulo\Registrodepersonaje;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Personaje;
use App\Traits\Albion;
use App\Traits\DiscordComan;
use App\Models\AuthProvider;
use Illuminate\Support\Facades\Auth;

class Registro extends Component
{
    use WithPagination; 
    use Albion;
    use DiscordComan;

    public $buscar, $informacio, $titulo, $nombre, $gremio, $idpersonaje, $mensaje;
    public $modalconfirmarperfil = false;
    public $modalmensaje = false;

    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    public function render()
    {

        $informacion = $this->buscarpersonajepornombre($this->buscar);

        
        return view('livewire.modulo.registrodepersonaje.registro',[
            'informacion' => $informacion,
        ]);
    }

    /**
     * Corrige la numeracion de la tabla al realizar 
     * una busqueda
     */
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function buscarperfil($informacio )
    {
        $personaje = $this->consultarpersonaje($informacio);
        
        $this->titulo = "Registro";
        $this->nombre = $personaje->Name;
        $this->idpersonaje = $personaje->Id;
        $this->gremio = $personaje->GuildName;

        $this->modalconfirmarperfil = true;
    }

    public function guardarperfil($idntificador)
    {
        try {
            $personaje = $this->consultarpersonaje($idntificador);
            $this->modalconfirmarperfil = false;

            $confirmar = Personaje::where('Id_albion', $personaje->Id)->first();

            if (!$confirmar) {
                $user = Auth::user();
                $discordProvider = $user->authProviders()
                    ->where('provider', 'discord')
                    ->first();

                $datosPersonaje = [
                    'Name' => $personaje->Name,
                    'Id_albion' => $personaje->Id,
                    'GuildId' => $personaje->GuildId ?? null, // También seguro para GuildId
                ];

                // Manejo seguro de discord_user_id
                if ($discordProvider && isset($discordProvider->provider_id)) {
                    $datosPersonaje['discord_user_id'] = $discordProvider->provider_id;
                }

                $persona = auth()->user()->personajes()->create($datosPersonaje);

                $this->modalmensaje = true;
                $this->titulo = "Registrado Correctamente";
                $this->mensaje = "El personaje fue agregado correctamente";
            } else {
                $this->modalmensaje = true;
                $this->titulo = "Este personaje ya está registrado";
                $this->mensaje = "El personaje ya fue registrado por otro usuario ";
            }
        } catch (\Exception $e) {
            $this->modalmensaje = true;
            $this->titulo = "Error al registrar: " . $e->getMessage();
        }
    }

    public function redirectToDashboard()
    {
        $this->modalmensaje = false; // Cerrar el modal
        return redirect()->route('dashboard');
    }
}

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

        $user = Auth::user();
        $discordProvider = $user->authProviders()
            ->where('provider', 'discord')
            ->first();

        // Buscar si el personaje ya existe
        $personajeExistente = Personaje::where('Id_albion', $personaje->Id)->first();

        if (!$personajeExistente) {
            // CASO 1: Personaje no registrado - Registrar nuevo
            $datosPersonaje = [
                'Name' => $personaje->Name,
                'Id_albion' => $personaje->Id,
                'GuildId' => $personaje->GuildId ?? null,
            ];

            if ($discordProvider && isset($discordProvider->provider_id)) {
                $datosPersonaje['discord_user_id'] = $discordProvider->provider_id;
            }

            $persona = auth()->user()->personajes()->create($datosPersonaje);

            $this->modalmensaje = true;
            $this->titulo = "Registrado Correctamente";
            $this->mensaje = "El personaje fue agregado correctamente";

        } else {
            // Personaje ya existe, verificar el usuario asignado
            if (is_null($personajeExistente->user_id)) {
                // CASO 2: Personaje existe pero sin usuario - Asignar al usuario actual
                $datosActualizacion = ['user_id' => $user->id];

                // Actualizar discord_user_id si el usuario tiene uno
                if ($discordProvider && isset($discordProvider->provider_id)) {
                    $datosActualizacion['discord_user_id'] = $discordProvider->provider_id;
                }

                $personajeExistente->update($datosActualizacion);

                $this->modalmensaje = true;
                $this->titulo = "Personaje Asignado";
                $this->mensaje = "El personaje ha sido asignado a tu cuenta correctamente";

            } else if ($personajeExistente->user_id === $user->id) {
                // CASO 3a: Personaje ya pertenece al usuario actual
                $this->modalmensaje = true;
                $this->titulo = "Personaje Ya Registrado";
                $this->mensaje = "Este personaje ya está registrado en tu cuenta";

            } else {
                // CASO 3b: Personaje pertenece a otro usuario
                $this->modalmensaje = true;
                $this->titulo = "Personaje No Disponible";
                $this->mensaje = "Este personaje ya está registrado por otro usuario";
            }
        }

    } catch (\Exception $e) {
        $this->modalmensaje = true;
        $this->titulo = "Error al registrar";
        $this->mensaje = "Ocurrió un error: " . $e->getMessage();
    }
}

    public function redirectToDashboard()
    {
        $this->modalmensaje = false; // Cerrar el modal
        return redirect()->route('dashboard');
    }
}

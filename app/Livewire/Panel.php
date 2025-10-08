<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\DiscordComan;
use App\Traits\AlbionEconomia;
use App\Traits\Albion;
use Livewire\WithPagination;
use App\Models\GoldPrice;
use App\Models\Personaje;


class Panel extends Component
{
    use DiscordComan;
    use AlbionEconomia;
    use Albion;
    use WithPagination; 

    public $buscar, $nombre, $identificador, $titulo, $Id_albion, $mensaje;
    public $confirmarEliminar = false;
    public $modalmensaje = false;
    

    protected $queryString = [
        'buscar' => ['except' => '']
    ];

    public function render()
    {
        //revisar si el usuario esta en el servidor de discord
        $linhir_servidor = $this->checkDiscordMembership();
        //revisar el valor del oro
        $oro = GoldPrice::latest('id')->first();

        //comprobar perfiles
        $perfiles = auth()->user()->personajes()->where('Name', 'like', '%'.$this->buscar.'%')->paginate(6);
        //contar perfiles
        $perfilesno = auth()->user()->personajes;
        $num = count($perfilesno);  

        // Verificar si ALGÚN perfil tiene miembro = true (1)
        $algunoEsMiembro = $perfiles->contains('miembro', true);
        

        return view('livewire.panel',[
            'linhir_servidor' => $linhir_servidor, 
            'oro' => $oro, 
            'num' => $num, 
            'perfiles' => $perfiles, 
            'algunoEsMiembro' => $algunoEsMiembro,
        ]);
    }

    //Actualizar tabla para corregir falla de busqueda
    public function updatingBuscar()
    {
        $this->resetPage();
    }

    /**
     * Pregunta si quiere desvincular un personaje
     */
    public function consultaeliminapersonaje(Personaje $perfilID)
    {
        $this->titulo = "¿Seguro que desea desvincular este personaje?";        
        $this->nombre = $perfilID->Name;
        $this->identificador = $perfilID->id;
        $this->Id_albion = $perfilID->Id_albion;
        $this->confirmarEliminar = true;
    }

    /**
     * Desvincular un personaje del usuario actual
     * (No elimina el registro, solo remueve la relación)
     */
    public function eliminarPersonaje(Personaje $identificador)
    {
        try {
            $this->confirmarEliminar = false;
            
            // Verificar que el personaje pertenece al usuario actual
            if ($identificador->user_id !== auth()->id()) {
                $this->modalmensaje = true;
                $this->titulo = "Error";
                $this->mensaje = "No tienes permisos para desvincular este personaje.";
                return;
            }
            
            // Desvincular el personaje (establecer user_id y discord_user_id a null)
            $identificador->update([
                'user_id' => null,
                'discord_user_id' => null
            ]);
            
            $this->modalmensaje = true;
            $this->titulo = "Personaje Desvinculado";
            $this->mensaje = "El personaje '{$identificador->Name}' ha sido desvinculado de tu cuenta correctamente.";
            
        } catch (\Exception $e) {
            $this->modalmensaje = true;
            $this->titulo = "Error al desvincular";
            $this->mensaje = "Ocurrió un error: " . $e->getMessage();
        }
    }
}

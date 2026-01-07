<?php

namespace App\Livewire\Modulo\Calculadoras;

use Livewire\Component;

class Calculadoradecultivos extends Component
{
    // Variables de configuración
    public $premium = false;
    public $foco = false;
    public $bono = false;
    public $parcelas = 1;

    // Cultivos regulares
    public $carrotseed = 0, $carrot = 0;
    public $beanseed = 0, $bean = 0;
    public $wheatseed = 0, $wheat = 0;
    public $turnipseed = 0, $turnip = 0;
    public $cabbageseed = 0, $cabbage = 0;
    public $potatoseed = 0, $potato = 0;
    public $cornseed = 0, $corn = 0;
    public $punpkinseed = 0, $punpkin = 0;

    // Cultivos especiales
    public $agaricseed = 0, $agaric = 0;
    public $comfreyseed = 0, $comfrey = 0;
    public $burdockseed = 0, $burdock = 0;
    public $teaselseed = 0, $teasel = 0;
    public $foxgloveseed = 0, $foxglove = 0;
    public $mulleinseed = 0, $mullein = 0;
    public $yarrowseed = 0, $yarrow = 0;


    // Configuración de cultivos
    private $cultivosConfig = [
        'carrot' => ['retorno_foco' => 200, 'retorno_normal' => 0, 'ciudad' => 'Lymhurst'],
        'bean' => ['retorno_foco' => 166.3, 'retorno_normal' => 33.3, 'ciudad' => 'Bridgewatch'],
        'wheat' => ['retorno_foco' => 140, 'retorno_normal' => 60, 'ciudad' => 'Martlock'],
        'turnip' => ['retorno_foco' => 126, 'retorno_normal' => 73.3, 'ciudad' => 'Fort Sterling'],
        'cabbage' => ['retorno_foco' => 120, 'retorno_normal' => 80, 'ciudad' => 'Thetford'],
        'potato' => ['retorno_foco' => 113.7, 'retorno_normal' => 86.7, 'ciudad' => 'Thetford'],
        'corn' => ['retorno_foco' => 109.1, 'retorno_normal' => 91.1, 'ciudad' => 'Bridgewatch'],
        'punpkin' => ['retorno_foco' => 106.3, 'retorno_normal' => 93.3, 'ciudad' => 'Lymhurst'],
        'agaric' => ['retorno_foco' => 166.3, 'retorno_normal' => 33.3, 'ciudad' => 'Thetford'],
        'comfrey' => ['retorno_foco' => 140, 'retorno_normal' => 60, 'ciudad' => 'Caerleon'],
        'burdock' => ['retorno_foco' => 126, 'retorno_normal' => 73.3, 'ciudad' => 'Lymhurst'],
        'teasel' => ['retorno_foco' => 120, 'retorno_normal' => 80, 'ciudad' => 'Bridgewatch'],
        'foxglove' => ['retorno_foco' => 113.7, 'retorno_normal' => 86.7, 'ciudad' => 'Martlock'],
        'mullein' => ['retorno_foco' => 109.1, 'retorno_normal' => 91.1, 'ciudad' => 'Thetford'],
        'yarrow' => ['retorno_foco' => 106.3, 'retorno_normal' => 93.3, 'ciudad' => 'Fort Sterling'],
    ];

    public function render()
    {
        $imp = $this->impuestos();
        $resultados = [];

        foreach ($this->cultivosConfig as $cultivo => $config) {
            $resultados['r_' . $cultivo] = $this->calcularCultivo($cultivo, $config, $imp);
        }

        // Calcular profit total
        $totalProfit = 0;
        foreach ($resultados as $resultado) {
            $totalProfit += $resultado['profit'];
        }

        return view('livewire.modulo.calculadoras.calculadoradecultivos', array_merge([
            'imp' => $imp,
            'totalProfit' => $totalProfit
        ], $resultados));
    }

    /**
     * Calcula los impuestos según premium
     */
    public function impuestos()
    {
        return $this->premium ? 6.5 : 10.5;
    }

    /**
     * Función genérica para calcular cualquier cultivo
     */
    private function calcularCultivo($cultivo, $config, $imp)
    {
        $semilla = $cultivo . 'seed';
        $producto = $cultivo;

        // Calcular retorno de semillas
        $retorno = $this->foco ? $config['retorno_foco'] : $config['retorno_normal'];
        $semillasRegresadas = round($this->parcelas * 9 * $retorno / 100);

        // Calcular profit
        $precioBase = $this->premium ? 9 : 4.5;
        $precioBono = $this->bono ? 0.9 : 0;

        $ingresos = ($precioBase + $precioBono) * $this->parcelas * 9 * $this->$producto * (1 - ($imp / 100));
        $ingresos += $this->$semilla * $semillasRegresadas;
        
        $costos = $this->$semilla * 9 * $this->parcelas;
        
        $profit = round($ingresos - $costos);

        return [
            'retorno' => $retorno,
            'r_semillas' => $semillasRegresadas,
            'profit' => $profit,
            'ciudad' => $config['ciudad']
        ];
    }

    /**
     * Resetear todos los campos
     */
    public function resetear()
    {
        $this->premium = false;
        $this->foco = false;
        $this->bono = false;
        $this->parcelas = 1;
        
        // Resetear todos los cultivos
        foreach ($this->cultivosConfig as $cultivo => $config) {
            $this->{$cultivo . 'seed'} = 0;
            $this->$cultivo = 0;
        }
    }

    /**
     * Método para inicializar valores por defecto
     */
    public function mount()
    {
        // Inicializar valores por defecto si es necesario
        $this->parcelas = 1;
    }
}

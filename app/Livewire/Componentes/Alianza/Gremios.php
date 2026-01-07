<?php

namespace App\Livewire\Componentes\Alianza;

use Livewire\Component;
use App\Traits\Albion;
use Illuminate\Support\Facades\Cache;

class Gremios extends Component
{
    use Albion;

    public $guilds = [];

    // Mapeo de enlaces de Discord para cada gremio
    protected $discordLinks = [
        'iS2Q2Mw3S1asC9GVMC5P2w' => 'https://discord.gg/VYpsPZMRf5', //Linhir
        'E4k0czn4TS-4aG_0LcDJbg' => 'https://discord.gg/5SWfqPAKCS', //The New-Order
        '71HB_IECTNuFF-Pme-1gMA' => 'https://discord.gg/nightshift', //NigthShift 
        'hkssSH4MSbuSm3JM-KaMIg' => 'https://discord.gg/VQfKSpW947', //OBLIVIOM
        'C_NL6QnWQimzcUQVXlknrw' => 'https://discord.gg/ghwve3v6dD', //LORDS DAEDRAS
        'bImVFxgcROWzC1cA-BNXqA' => 'https://discord.gg/YyBzZqAKTy' //Redemptio Aeterna
    ];

    public function mount()
    {
        $this->loadGuildsData();
    }

    private function loadGuildsData()
    {
        $baseGuilds = [
            ["Id" => "iS2Q2Mw3S1asC9GVMC5P2w", "Name" => "Linhir"],
            ["Id" => "E4k0czn4TS-4aG_0LcDJbg", "Name" => "The New-Order"],
            ["Id" => "71HB_IECTNuFF-Pme-1gMA", "Name" => "NigthShift"],
            ["Id" => "hkssSH4MSbuSm3JM-KaMIg", "Name" => "OBLIVIOM"],
            ["Id" => "C_NL6QnWQimzcUQVXlknrw", "Name" => "LORDS DAEDRAS"],
            ["Id" => "bImVFxgcROWzC1cA-BNXqA", "Name" => "Redemptio Aeterna"]
        ];

        foreach ($baseGuilds as $guild) {
            $guildId = $guild['Id'];
            
            // Usar cache para evitar múltiples llamadas a la API
            $guildData = Cache::remember("guild_data_{$guildId}", 3600, function () use ($guildId) {
                return $this->consultargremio($guildId);
            });

            if ($guildData) {
                // Convertir a array si es objeto
                if (is_object($guildData)) {
                    $guildData = json_decode(json_encode($guildData), true);
                }

                // Extraer datos de la estructura anidada
                $guildInfo = $guildData['guild'] ?? [];
                $basicInfo = $guildData['basic'] ?? [];
                
                $this->guilds[] = [
                    'id' => $guildId,
                    'name' => $guildInfo['Name'] ?? $guild['Name'],
                    'members' => $basicInfo['memberCount'] ?? 0,
                    'discord_url' => $this->discordLinks[$guildId] ?? null
                ];

                //dd($this->guilds);
            } else {
                // Fallback si no hay datos de la API
                $this->guilds[] = [
                    'id' => $guildId,
                    'name' => $guild['Name'],
                    'members' => 0,
                    'discord_url' => $this->discordLinks[$guildId] ?? null
                ];
            }
        }
    }

    // Función para formatear números grandes
    public function formatNumber($number)
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }
        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }
        return $number;
    }

    // Método para formatear fechas
    public function formatDate($dateString)
    {
        if (!$dateString) return null;
        
        try {
            return \Carbon\Carbon::parse($dateString)->format('M Y');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function render()
    {
        $prueba = $this->consultargremio("iS2Q2Mw3S1asC9GVMC5P2w");

        
        return view('livewire.componentes.alianza.gremios');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FamaSemanal extends Model
{
    use HasFactory;

    protected $table = 'fama_semanal';
    
    protected $fillable = [
        'personaje_id',
        'semana_inicio',
        'fama_pve_inicio',
        'fama_pvp_inicio',
        'fama_pve_fin',
        'fama_pvp_fin'
    ];

    // Relación con el personaje
    public function personaje()
    {
        return $this->belongsTo(Personaje::class);
    }
    
    // Calcula la diferencia de fama PvE
    public function getDiferenciaPveAttribute()
    {
        return $this->fama_pve_fin - $this->fama_pve_inicio;
    }
    
    // Calcula la diferencia de fama PvP
    public function getDiferenciaPvpAttribute()
    {
        return $this->fama_pvp_fin - $this->fama_pvp_inicio;
    }
}

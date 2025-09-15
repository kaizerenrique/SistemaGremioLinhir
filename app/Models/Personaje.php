<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personaje extends Model
{
    protected $fillable = [
        'user_id',
        'discord_user_id',        
        'Name',
        'Id_albion',
        'GuildId',
        'miembro'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function lifetimeStatistics()
    {
        return $this->hasOne(LifetimeStatistics::class, 'personaje_id');
    }

    public function gatheringStatistics()
    {
        return $this->hasOne(GatheringStatistics::class, 'personaje_id');
    }

    public function movimientosBancarios()
    {
        return $this->hasMany(BancoGremial::class);
    }

    public function saldoActual()
    {
        // Si ya se cargaron los sums con withSum, úsalos
        if (isset($this->attributes['ingresos']) && isset($this->attributes['egresos'])) {
            return (int) $this->attributes['ingresos'] - (int) $this->attributes['egresos'];
        }
        
        // Si no, calcula el saldo con una consulta optimizada
        $ingresos = $this->movimientosBancarios()->where('tipo', 'ingreso')->sum('monto');
        $egresos = $this->movimientosBancarios()->where('tipo', 'egreso')->sum('monto');
        
        return $ingresos - $egresos;
    }
    
}

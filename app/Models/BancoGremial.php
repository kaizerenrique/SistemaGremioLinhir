<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BancoGremial extends Model
{
    protected $fillable = [
        'personaje_id',
        'tipo',
        'monto',
        'concepto',
        'referencia',
        'autorizado_por'
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function personaje(): BelongsTo
    {
        return $this->belongsTo(Personaje::class);
    }

    // Scope para filtrar por tipo de operación
    public function scopeTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // Scope para filtrar por personaje
    public function scopeDePersonaje($query, $personaje_id)
    {
        return $query->where('personaje_id', $personaje_id);
    }

    // Calcular saldo actual de un personaje
    public static function saldoPersonaje($personaje_id)
    {
        $ingresos = self::where('personaje_id', $personaje_id)
                        ->where('tipo', 'ingreso')
                        ->sum('monto');
        
        $egresos = self::where('personaje_id', $personaje_id)
                       ->where('tipo', 'egreso')
                       ->sum('monto');

        return $ingresos - $egresos;
    }
}

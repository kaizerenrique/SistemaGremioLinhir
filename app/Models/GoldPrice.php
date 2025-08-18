<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldPrice extends Model
{
    protected $fillable = ['price', 'timestamp'];
    
    // Convertir automáticamente el timestamp
    protected $casts = [
        'timestamp' => 'datetime'
    ];
}

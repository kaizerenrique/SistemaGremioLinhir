<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
    * Casts para que `is_system` sea siempre boolean
    * sin importar el driver de base de datos.
    */
    protected $casts = [
        'is_system' => 'boolean',
    ];
}

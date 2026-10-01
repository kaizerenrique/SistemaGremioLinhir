<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Añade el cast de is_system al modelo base de Spatie.
     * El resto de la funcionalidad (guard_name, permissions(), users(), etc.)
     * se hereda intacta.
    */
    protected $casts = [
        'is_system' => 'boolean',
    ];
}

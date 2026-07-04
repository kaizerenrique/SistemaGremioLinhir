<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propiedades extends Model
{
    protected $fillable = [
        'name',
        'type',        
        'Name',
        'description',
        'is_active',
    ];
}

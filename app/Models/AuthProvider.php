<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthProvider extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'login_at' => 'datetime',
    ];
}

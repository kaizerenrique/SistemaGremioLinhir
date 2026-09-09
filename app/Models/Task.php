<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'points',
        'repeatable',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'repeatable' => 'boolean',
        'active' => 'boolean',
        'points' => 'integer',
    ];

    /**
     * Get the reports for this task.
     */
    public function reports()
    {
        return $this->hasMany(TaskReport::class);
    }
}

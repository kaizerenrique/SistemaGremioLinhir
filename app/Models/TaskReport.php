<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'discord_user_id',
        'evidence',
        'status',
        'submitted_at',
        'reviewed_by_discord_id',
        'reviewed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the task that this report belongs to.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the personaje (character) associated with the reporter.
     */
    public function personaje()
    {
        return $this->hasOne(Personaje::class, 'discord_user_id', 'discord_user_id');
    }

    /**
     * Get the display name of the reporter.
     */
    public function getReporterNameAttribute()
    {
        if ($this->relationLoaded('personaje') && $this->personaje) {
            return $this->personaje->Name;
        }
        return $this->discord_user_id;
    }

    /**
     * Scope a query to only include pending reports.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved reports.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected reports.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}

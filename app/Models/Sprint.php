<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sprint extends Model
{
    protected $fillable = ['project_id', 'name', 'goal', 'starts_at', 'ends_at', 'velocity', 'state'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /** Scope for planning/active sprints only. */
    public function scopeOpen($query)
    {
        return $query->whereIn('state', ['planning', 'active']);
    }

    /** Close this sprint — move unfinished (open/in_progress) issues back to backlog. */
    public function complete(): void
    {
        // ponytail: only move issues still in incomplete statuses
        $completeKeys = $this->project->statuses()->where('is_closed', true)->pluck('key');
        $completeKeys = $completeKeys->isEmpty() ? ['done', 'closed', 'resolved'] : $completeKeys;

        $this->issues()
            ->whereNotIn('status', $completeKeys)
            ->update(['sprint_id' => null]); // move to backlog

        $this->update(['state' => 'completed']);
    }
}

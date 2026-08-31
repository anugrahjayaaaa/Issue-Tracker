<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Activity;

class Issue extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_BUG = 'bug';
    public const TYPE_FEATURE = 'feature';
    public const TYPE_TASK = 'task';
    public const TYPE_EPIC = 'epic';

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DONE = 'done';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'project_id', 'code', 'title', 'description', 'type', 'status',
        'priority', 'reporter_id', 'assignee_id', 'parent_id', 'due_date', 'order',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date'];
    }

    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = sanitizeRichText($value);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Issue::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Issue::class, 'parent_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Project members who may act on issues (lead/member). Viewers excluded. */
    public static function scopeForMember($query, Project $project, User $user)
    {
        // viewer+ may read; this scope is for write actions (lead/member).
        return ProjectMember::hasRole($user, $project, [ProjectMember::ROLE_LEAD, ProjectMember::ROLE_MEMBER]);
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class);
    }

    /**
     * Aggregated activity timeline: issue events + events on its comments.
     * Reuses the existing activity_log (written by observers) — no new schema.
     */
    public function activityTimeline(): Collection
    {
        $commentIds = $this->comments()->pluck('id');

        return Activity::query()
            ->with('causer')
            ->where(function ($q) use ($commentIds) {
                $q->where('subject_type', self::class)->where('subject_id', $this->id);
                if ($commentIds->isNotEmpty()) {
                    $q->orWhere(function ($q2) use ($commentIds) {
                        $q2->where('subject_type', Comment::class)->whereIn('subject_id', $commentIds);
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->get();
    }
}

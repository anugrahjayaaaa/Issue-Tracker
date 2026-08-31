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

    // Priorities are a fixed global enum (no per-project config) — keep as constants.
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'project_id', 'code', 'title', 'description', 'type', 'status',
        'priority', 'reporter_id', 'assignee_id', 'parent_id', 'due_date', 'order',
    ];

    /**
     * `type` / `status` store the STABLE key slug of the project's issue_type / status.
     * (Renaming a label keeps the key, so issues stay attached — see migration.)
     * These accessors resolve the human label for display.
     */
    public function typeName(): ?string
    {
        return $this->project->issueTypes()->where('key', $this->type)->value('name');
    }

    public function statusName(): ?string
    {
        return $this->project->statuses()->where('key', $this->status)->value('name');
    }

    public function statusColor(): ?string
    {
        return $this->project->statuses()->where('key', $this->status)->value('color');
    }

    public function typeColor(): ?string
    {
        return $this->project->issueTypes()->where('key', $this->type)->value('color');
    }

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

    /**
     * Whether a status change to $toKey (status key slug) is allowed by the workflow.
     * ponytail: empty transition table = free transitions (no scheme yet). Compares
     * stable `key` slugs, not human `name` — renaming a status keeps issues valid.
     */
    public function canTransitionTo(string $toKey): bool
    {
        $transitions = $this->project->statusTransitions;
        if ($transitions->isEmpty()) {
            return true;
        }
        $from = $this->project->statuses()->where('key', $this->status)->first();
        if (! $from) {
            return true;
        }

        return $transitions->contains(
            fn ($t) => $t->from_status_id === $from->id
                && $t->to->key === $toKey
        );
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

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
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

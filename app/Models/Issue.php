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
        'priority', 'reporter_id', 'assignee_id', 'parent_id', 'due_date', 'order', 'sprint_id',
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

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'component_issue');
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

    public function statusLink(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status', 'key');
    }

    public function automationLogs(): HasMany
    {
        return $this->hasMany(AutomationRuleLog::class);
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
     * If a transition has `required_role`, the acting user must satisfy it.
     */
    public function canTransitionTo(string $toKey, ?User $user = null): bool
    {
        $transitions = $this->project->statusTransitions;
        if ($transitions->isEmpty()) {
            return true;
        }
        $from = $this->project->statuses()->where('key', $this->status)->first();
        if (! $from) {
            return true;
        }

        $transition = $transitions->first(
            fn ($t) => $t->from_status_id === $from->id && $t->to->key === $toKey
        );

        if (! $transition) {
            return false;
        }

        // Phase D: role-aware transitions — if transition defines required_role, user must have it.
        if ($transition->required_role && $user) {
            return ProjectMember::hasRole($user, $this->project, [$transition->required_role])
                || $user->can('project.manage');
        }

        return true;
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

    public function watchers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'issue_watchers')->withTimestamps();
    }

    public function sprint(): BelongsTo
    {
        return $this->belongsTo(Sprint::class);
    }

    /** Auto-subscribe reporter + assignee + commenter (decision #3). */
    public function syncWatchers(array $userIds): void
    {
        $ids = collect($userIds)->filter()->unique()->all();
        $this->watchers()->syncWithoutDetaching($ids);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /** True if $candidateParentId is this issue or one of its descendants (cycle guard). */
    public function wouldCreateCycle(?int $candidateParentId): bool
    {
        if (! $candidateParentId) {
            return false;
        }
        if ($candidateParentId === $this->id) {
            return true;
        }
        // ponytail: bounded BFS over children; depth-1 only in v1 so cheap.
        $seen = collect([$this->id]);
        $queue = $this->children()->pluck('id')->all();
        while ($queue) {
            $id = array_shift($queue);
            if ($id === $candidateParentId) {
                return true;
            }
            if ($seen->contains($id)) {
                continue;
            }
            $seen->push($id);
            $queue = array_merge($queue, Issue::where('parent_id', $id)->pluck('id')->all());
        }

        return false;
    }

    /** Sub-task rollup: n of m children in a closed status. */
    public function subtaskProgress(): array
    {
        $children = $this->children()->with('statusLink')->get();
        $total = $children->count();
        if ($total === 0) {
            return ['done' => 0, 'total' => 0];
        }
        $done = $children->filter(fn ($c) => optional($c->statusLink)->is_closed)->count();

        return ['done' => $done, 'total' => $total];
    }

    public function activityTimeline(): Collection
    {
        $commentIds = $this->comments()->pluck('id');

        $timeline = Activity::query()
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

        return $timeline;
    }

    /** Phase D: fire automation rules for the given event on this issue. */
    public function fireAutomationRules(string $event): void
    {
        // ponytail: rules are per-project; only run if enabled. Minimal evaluator.
        $rules = AutomationRule::where('project_id', $this->project_id)
            ->where('event', $event)
            ->where('enabled', true)
            ->get();

        foreach ($rules as $rule) {
            if ($rule->matchesIssue($this)) {
                $rule->executeOn($this);
            }
        }
    }
}

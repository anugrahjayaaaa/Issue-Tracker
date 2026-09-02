<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRule extends Model
{
    protected $fillable = ['project_id', 'name', 'event', 'conditions', 'actions', 'enabled'];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'actions' => 'array',
            'enabled' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationRuleLog::class);
    }

    /** ponytail: minimal rule evaluator — field=value on issue. Only supports issue: status, type, assignee_id. */
    public function matchesIssue(Issue $issue): bool
    {
        foreach ($this->conditions ?? [] as $condition) {
            $field = $condition['field'] ?? null;
            $value = $condition['value'] ?? null;
            if ($field && $issue->{$field} != $value) {
                return false;
            }
        }
        return true;
    }

    /** Execute actions on the issue. ponytail: supports assign, change_status, add_label, add_component, notify_watchers. */
    public function executeOn(Issue $issue): void
    {
        // ponytail: use saveQuietly() to avoid re-triggering IssueObserver + infinite loop.
        foreach ($this->actions as $action) {
            $type = $action['type'] ?? null;
            $value = $action['value'] ?? null;

            match ($type) {
                'assign' => $issue->fill(['assignee_id' => $value])->saveQuietly(),
                'change_status' => $issue->fill(['status' => $value])->saveQuietly(),
                'add_label' => $issue->labels()->syncWithoutDetaching([$value]),
                'add_component' => $issue->components()->syncWithoutDetaching([$value]),
                'notify_watchers' => null, // placeholder: emit notification event
                default => null,
            };
        }

        $this->logs()->create(['issue_id' => $issue->id, 'status' => 'success']);
    }
}

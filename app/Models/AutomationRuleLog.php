<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRuleLog extends Model
{
    protected $fillable = ['automation_rule_id', 'issue_id', 'status', 'error_message'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class, 'automation_rule_id', 'id');
    }

    /** Ponytail: fallback name so the timeline never renders `-` when the rule was deleted. */
    public function getRuleNameAttribute(): ?string
    {
        return $this->rule?->name;
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}

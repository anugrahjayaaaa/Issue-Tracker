<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRuleLog extends Model
{
    protected $fillable = ['automation_rule_id', 'issue_id', 'status', 'error_message'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(AutomationRule::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}

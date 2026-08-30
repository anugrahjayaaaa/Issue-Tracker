<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    protected $fillable = [
        'plan_slug', 'user_id', 'license_key', 'type', 'status', 'issued_to',
        'expires_at', 'snapshot', 'revoke_reason',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'snapshot' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Scope: licenses that are currently active and not expired. */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** The license is non-transferable: it is bound to the instance it was activated on. */
    public function isActiveAndValid(): bool
    {
        return $this->status === 'active'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}

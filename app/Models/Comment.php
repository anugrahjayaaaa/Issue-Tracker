<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['issue_id', 'user_id', 'body'];

    // ponytail: minimal allowlist strip for rich-text; add mews/purifier if richer
    // HTML is needed later. Blocks scripts/events while keeping basic formatting.
    public function setBodyAttribute($value): void
    {
        $allowed = '<p><br><strong><em><u><s><ul><ol><li><a><blockquote><code><pre><h3><h4><span><img>';
        $this->attributes['body'] = $value === null ? null : strip_tags($value, $allowed);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }
}

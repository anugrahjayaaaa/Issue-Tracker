<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Issue;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['key', 'name', 'slug', 'description', 'owner_id', 'issue_seq'];

    protected static function boot(): void
    {
        parent::boot();
        // slug is auto from name unless explicitly provided (mirrors Plan pattern).
        static::creating(function (Project $p) {
            if (empty($p->slug)) {
                $p->slug = Str::slug($p->name);
            }
        });
    }

    /** Folder-safe slug used for scoped upload paths. */
    public function folder(): string
    {
        return $this->slug ?: Str::slug($this->name);
    }

    protected function casts(): array
    {
        return ['issue_seq' => 'integer'];
    }

    // ponytail: shared sanitizeRichText() single source of truth for all rich-text.
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = sanitizeRichText($value);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /** Next issue code, e.g. HEL-12. Increments issue_seq atomically-ish. */
    public function nextIssueCode(): string
    {
        $seq = $this->issue_seq + 1;
        $this->issue_seq = $seq;
        $this->saveQuietly();

        return $this->key.'-'.$seq;
    }
}

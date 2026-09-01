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

    protected $fillable = ['key', 'name', 'slug', 'description', 'owner_id', 'issue_seq', 'subtask_rollup'];

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
        return ['issue_seq' => 'integer', 'subtask_rollup' => 'boolean'];
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

    public function issueTypes(): HasMany
    {
        return $this->hasMany(IssueType::class)->orderBy('order');
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(Status::class)->orderBy('order');
    }

    public function statusTransitions(): HasMany
    {
        return $this->hasMany(StatusTransition::class);
    }

    /** Default issue types + statuses copied into a new project (Jira-like start). */
    public function seedDefaultFields(): void
    {
        if ($this->issueTypes()->exists() || $this->statuses()->exists()) {
            return;
        }
        $types = [
            ['name' => 'Bug', 'color' => '#dc3545', 'icon' => 'bi-bug', 'description' => 'Something is broken.', 'order' => 1],
            ['name' => 'Feature', 'color' => '#0d6efd', 'icon' => 'bi-star', 'description' => 'New capability.', 'order' => 2],
            ['name' => 'Task', 'color' => '#6c757d', 'icon' => 'bi-check2-square', 'description' => 'Work to do.', 'order' => 3],
            ['name' => 'Epic', 'color' => '#6f42c1', 'icon' => 'bi-collection', 'description' => 'Large body of work.', 'order' => 4],
        ];
        foreach ($types as $t) {
            $this->issueTypes()->create($t);
        }
        $statuses = [
            ['name' => 'Open', 'color' => '#0dcaf0', 'is_closed' => false, 'order' => 1],
            ['name' => 'In Progress', 'color' => '#ffc107', 'is_closed' => false, 'order' => 2],
            ['name' => 'Blocked', 'color' => '#dc3545', 'is_closed' => false, 'order' => 3],
            ['name' => 'Done', 'color' => '#198754', 'is_closed' => true, 'order' => 4],
        ];
        foreach ($statuses as $s) {
            $this->statuses()->create($s);
        }
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function sprints(): HasMany
    {
        return $this->hasMany(Sprint::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['key', 'name', 'description', 'owner_id', 'issue_seq'];

    protected function casts(): array
    {
        return ['issue_seq' => 'integer'];
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

    /** Next issue code, e.g. HEL-12. Increments issue_seq atomically-ish. */
    public function nextIssueCode(): string
    {
        $seq = $this->issue_seq + 1;
        $this->issue_seq = $seq;
        $this->saveQuietly();

        return $this->key.'-'.$seq;
    }
}

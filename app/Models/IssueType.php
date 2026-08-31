<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class IssueType extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'key', 'name', 'color', 'icon', 'description', 'order'];

    protected static function boot(): void
    {
        parent::boot();
        // ponytail: stable key slug from name; rename keeps `name`, `key` stays.
        static::creating(function (IssueType $t) {
            if (empty($t->key)) {
                $t->key = Str::slug($t->name);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'type', 'key');
    }
}

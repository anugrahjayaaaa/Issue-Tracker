<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'key', 'name', 'color', 'is_closed', 'order'];

    protected static function boot(): void
    {
        parent::boot();
        // ponytail: stable key slug from name; rename keeps `name`, `key` stays.
        static::creating(function (Status $s) {
            if (empty($s->key)) {
                $s->key = Str::slug($s->name);
            }
        });
    }

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function outgoingTransitions(): HasMany
    {
        return $this->hasMany(StatusTransition::class, 'from_status_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'status', 'key');
    }
}

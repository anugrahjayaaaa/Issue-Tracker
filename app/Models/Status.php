<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'color', 'is_closed', 'order'];

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
}

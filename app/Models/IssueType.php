<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueType extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'name', 'color', 'icon', 'description', 'order'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

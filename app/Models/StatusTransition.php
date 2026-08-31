<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusTransition extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'from_status_id', 'to_status_id'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'from_status_id');
    }

    public function to(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'to_status_id');
    }
}

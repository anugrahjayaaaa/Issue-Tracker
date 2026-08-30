<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'user_id', 'role'];

    public const ROLE_LEAD = 'lead';
    public const ROLE_MEMBER = 'member';
    public const ROLE_VIEWER = 'viewer';

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Centralized project-scope gate (the only place membership is checked).
     * A user passes if they hold any of $roles on $project.
     */
    public static function hasRole(User $user, Project $project, array $roles): bool
    {
        return self::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereIn('role', $roles)
            ->exists();
    }

    public static function isLead(User $user, Project $project): bool
    {
        return self::hasRole($user, $project, [self::ROLE_LEAD]);
    }
}

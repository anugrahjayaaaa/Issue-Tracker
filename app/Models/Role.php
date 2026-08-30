<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Application Role model.
 *
 * Extends spatie's Role and adds SoftDeletes so roles can be trashed/restored.
 * Referenced by config/permission.php (models.role) so all role queries/mutations
 * route through this class (not the spatie base) — required for the RBAC observers
 * (App\Observers\RoleObserver) to fire.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $deleted_at (SoftDeletes)
 */
class Role extends SpatieRole
{
    use SoftDeletes;
}

<?php

namespace App\Observers;

use App\Models\Permission;
use Illuminate\Support\Facades\Request;

class PermissionObserver
{
    public function created(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_created');
    }

    public function updated($permission)
    {
        $dirty = $permission->getDirty();
        unset($dirty['password'], $dirty['remember_token']); // ponytail: never log secrets
        $old = [];
        foreach ($dirty as $k => $v) {
            $old[$k] = $permission->getOriginal($k);
        }
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'old' => $old, 'new' => $dirty,
        ])->performedOn($permission)->log('permission_updated');
    }

    public function deleted(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_deleted');
    }

    public function restored(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_restored');
    }

    /**
     * Log a permanent (hard) delete of a permission to the audit trail.
     *
     * @return void
     *
     * @details Writes a 'permission_force_deleted' row into DB table `activity_log`
     * (spatie/activitylog). Unrecoverable, unlike `deleted()` (soft delete).
     */
    public function forceDeleted(Permission $permission)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($permission)->log('permission_force_deleted');
    }
}

<?php

namespace App\Observers;

use App\Models\Role;
use Illuminate\Support\Facades\Request;

class RoleObserver
{
    public function created(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_created');
    }

    public function updated($role)
    {
        $dirty = $role->getDirty();
        unset($dirty['password'], $dirty['remember_token']); // ponytail: never log secrets
        $old = [];
        foreach ($dirty as $k => $v) {
            $old[$k] = $role->getOriginal($k);
        }
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
            'old' => $old, 'new' => $dirty,
        ])->performedOn($role)->log('role_updated');
    }

    public function deleted(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_deleted');
    }

    public function restored(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_restored');
    }

    /**
     * Log a permanent (hard) delete of a role to the audit trail.
     *
     * @return void
     *
     * @details Writes a 'role_force_deleted' row into DB table `activity_log`
     * (spatie/activitylog). Unrecoverable, unlike `deleted()` (soft delete).
     */
    public function forceDeleted(Role $role)
    {
        activity()->causedBy(auth()->user())->withProperties([
            'ip' => Request::ip(), 'user_agent' => Request::userAgent(),
        ])->performedOn($role)->log('role_force_deleted');
    }
}

<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared soft/force bulk-delete loop for RBAC + user models.
 *
 * The loop was copy-pasted across UserController / RoleController /
 * PermissionController; only the skip-guard and permission prefix differed.
 *
 * @param  class-string<Model>  $model
 * @param  array<int|string>  $ids
 * @param  callable(Model): bool  $skip  return true to skip an item (e.g. self / super-admin)
 */
final class BulkDeleteService
{
    public function run(string $model, array $ids, bool $force, string $permissionPrefix, ?callable $skip = null): int
    {
        $ability = $force ? "{$permissionPrefix}.force-delete" : "{$permissionPrefix}.delete";
        $done = 0;

        foreach ($ids as $id) {
            $item = $model::withTrashed()->find($id);
            if (! $item || ($skip && $skip($item))) {
                continue;
            }
            if (! auth()->user()->can($ability, $item)) {
                continue;
            }
            $force ? $item->forceDelete() : $item->delete();
            $done++;
        }

        return $done;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rbac\RoleStoreRequest;
use App\Http\Requests\Rbac\RoleUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Roles
 *
 * Role management (admin).
 */
class RoleApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $roles = Role::withTrashed()->with('permissions')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')->paginate(10);

        return response()->json(RoleResource::collection($roles)->response()->getData(true));
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json(new RoleResource($role->load('permissions')));
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions(array_map('intval', $data['permissions'] ?? []));

        return response()->json(new RoleResource($role->load('permissions')), 201);
    }

    /**
     * Update a role.
     *
     * @authenticated
     */
    public function update(RoleUpdateRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();
        $role->update(['name' => $data['name']]);
        $role->syncPermissions(array_map('intval', $data['permissions'] ?? []));

        return response()->json(new RoleResource($role->load('permissions')));
    }

    public function destroy(Role $role): JsonResponse
    {
        abort_if($role->name === 'super-admin', 403, __('messages.cannot_delete_super_admin'));
        $role->delete();

        return response()->json(['message' => __('messages.role_deleted')]);
    }

    public function restore(int $id): JsonResponse
    {
        Role::withTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => __('messages.role_restored')]);
    }

    public function forceDelete(int $id): JsonResponse
    {
        abort_if(Role::withTrashed()->findOrFail($id)->name === 'super-admin', 403, __('messages.cannot_permanently_delete_super_admin'));
        Role::withTrashed()->findOrFail($id)->forceDelete();

        return response()->json(['message' => __('messages.role_permanently_deleted')]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

/**
 * @group Users
 *
 * User management (admin). All actions require the matching `user.*` permission.
 */
class UserApiController extends Controller
{
    public function __construct(private UserService $users) {}

    /** List users (paginated, optional ?q= search). */
    public function index(Request $request): JsonResponse
    {
        $users = User::withTrashed()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                    ->orWhere('username', 'like', "%{$request->q}%")
                    ->orWhere('email', 'like', "%{$request->q}%");
            }))
            ->orderBy('name')->paginate(10);

        return response()->json(UserResource::collection($users)->response()->getData(true));
    }

    /** Show a single user. */
    public function show(User $user): JsonResponse
    {
        return response()->json(new UserResource($user->load('roles')));
    }

    /** Create a user. */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $user = $this->users->create($request->validated());

        return response()->json(new UserResource($user->load('roles')), 201);
    }

    /** Update a user.
     * @authenticated
     */
    public function update(UserUpdateRequest $request, User $user): JsonResponse
    {
        $this->users->update($user, $request->validated());

        return response()->json(new UserResource($user->load('roles')));
    }

    /** Soft-delete a user. */
    public function destroy(User $user): JsonResponse
    {
        abort_if($user->id === auth()->id(), 403, __('messages.cannot_delete_self'));
        $user->delete();

        return response()->json(['message' => __('messages.user_deleted')]);
    }

    /** Restore a soft-deleted user. */
    public function restore(int $id): JsonResponse
    {
        User::withTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => __('messages.user_restored')]);
    }

    /** Permanently delete a user. */
    public function forceDelete(int $id): JsonResponse
    {
        abort_if($id === auth()->id(), 403, __('messages.cannot_delete_self_permanently'));
        User::withTrashed()->findOrFail($id)->forceDelete();

        return response()->json(['message' => __('messages.user_permanently_deleted')]);
    }

    /** Permanently lock an account. */
    public function lock(int $id): JsonResponse
    {
        abort_if($id === auth()->id(), 403, __('messages.cannot_lock_self'));
        $this->users->lock(User::withTrashed()->findOrFail($id));

        return response()->json(['message' => __('messages.user_locked')]);
    }

    /** Unlock a locked account. */
    public function unlock(int $id): JsonResponse
    {
        $this->users->unlock(User::withTrashed()->findOrFail($id));

        return response()->json(['message' => __('messages.user_unlocked')]);
    }

    /** Send a password reset link to the user's email. */
    public function sendResetPassword(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $status = $this->users->sendResetPassword($user);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __('messages.reset_link_sent_simple')])
            : response()->json(['message' => __($status)], 422);
    }
}

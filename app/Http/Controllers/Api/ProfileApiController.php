<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\PasswordChangeRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Profile
 *
 * Authenticated user's own profile.
 */
class ProfileApiController extends Controller
{
    /** Show own profile. */
    public function show(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()->load('roles')));
    }

    /** Update own profile (name, username, email, phone).
     * @authenticated
     */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return response()->json(new UserResource($request->user()->load('roles')));
    }

    /** Change own password (revokes all tokens + other devices). */
    public function changePassword(PasswordChangeRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update(['password' => bcrypt($request->validated()['password'])]);
        $user->tokens()->delete();
        auth()->logoutOtherDevices($request->validated()['password']);

        return response()->json(['message' => __('messages.password_changed')]);
    }
}

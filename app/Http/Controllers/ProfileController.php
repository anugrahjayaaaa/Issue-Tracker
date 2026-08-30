<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\PasswordChangeRequest;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show', ['user' => auth()->user()]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update($request->validated());

        return redirect()->route('profile.show')->with('success', __('messages.profile_updated'));
    }

    public function changePassword(PasswordChangeRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $user->update(['password' => bcrypt($request->validated()['password'])]);
        // revoke other sessions
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
        auth()->logoutOtherDevices($request->validated()['password']);

        return redirect()->route('profile.show')->with('success', __('messages.password_changed'));
    }
}

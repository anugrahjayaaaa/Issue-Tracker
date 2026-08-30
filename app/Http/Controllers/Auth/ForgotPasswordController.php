<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PasswordEmailRequest;
use App\Http\Requests\Auth\PasswordResetRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Show the "forgot password" form.
     *
     * @return View auth.forgot-password
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the given email.
     *
     * @param  PasswordEmailRequest  $request  Validated: email (valid email)
     * @return RedirectResponse Back with 'status' on success, or back with 'email' error.
     *
     * @throws ValidationException If the email is not found / broker error
     *
     * @details Uses the 'users' password broker (config('auth.passwords.users')).
     * The reset token is stored in DB table `password_reset_tokens` (keyed by email).
     * The email itself is sent via the default ResetPassword notification
     * (requires MAIL_* config to actually deliver).
     */
    public function store(PasswordEmailRequest $request): RedirectResponse
    {
        $status = Password::broker('users')->sendResetLink(
            $request->validated()
        );

        if ($status === Password::RESET_LINK_SENT) {
            // ponytail: broker does not fire an event for "link requested"; log it so audit trail is complete
            activity()
                ->withProperties(['ip' => $request->ip(), 'user_agent' => $request->userAgent(), 'email' => $request->email])
                ->log('password_reset_request');

            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    /**
     * Show the password reset form.
     *
     * @param  string  $token  Reset token from the email link
     * @return View auth.reset-password with $token and $email (from query)
     */
    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email ?? '']);
    }

    /**
     * Reset the user's password.
     *
     * @param  PasswordResetRequest  $request  Validated: token, email, password (min:8), password_confirmation
     * @return RedirectResponse Redirect to login with 'status' on success, or back with 'email' error.
     *
     * @throws ValidationException On invalid/expired token or mismatch
     *
     * @details Verifies token against DB table `password_reset_tokens` via the 'users' broker,
     * then updates the user's password and clears the token row.
     */
    public function update(PasswordResetRequest $request): RedirectResponse
    {
        $status = Password::broker('users')->reset(
            $request->validated(),
            function ($user, $password) {
                $user->password = $password;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }
}

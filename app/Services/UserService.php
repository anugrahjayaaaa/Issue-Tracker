<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Password;

/**
 * User lifecycle operations shared by the web and API controllers
 * (create / update / lock / unlock / reset-link). Keeps the domain logic
 * in one place so the two controllers don't drift apart.
 */
final class UserService
{
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => bcrypt($data['password']),
        ]);
        $user->syncRoles(array_map('intval', $data['roles'] ?? []));

        return $user;
    }

    public function update(User $user, array $data): void
    {
        // ponytail: single update() call — avoids firing the `updated` observer twice
        // (password would otherwise trigger a second observer event after name/email).
        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];
        if (! empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }
        $user->update($payload);
        $user->syncRoles(array_map('intval', $data['roles'] ?? []));
    }

    public function lock(User $user): void
    {
        $user->update(['locked_until' => null, 'locked_permanently' => true]);
    }

    public function unlock(User $user): void
    {
        $user->update(['locked_until' => null, 'locked_permanently' => false]);
    }

    /**
     * @return string One of the Password::* status constants.
     */
    public function sendResetPassword(User $user): string
    {
        return Password::broker('users')->sendResetLink(['email' => $user->email]);
    }
}

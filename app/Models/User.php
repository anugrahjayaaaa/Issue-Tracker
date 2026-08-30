<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'phone', 'password', 'locked_until', 'locked_permanently', 'last_login_at', 'last_login_ip'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $with = []; // ponytail: avoid N+1 on lists; load relations explicitly

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    /** True when this user holds an active, non-expired license (subscriber). */
    public function hasActiveLicense(): bool
    {
        return $this->licenses()->active()->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'locked_until' => 'datetime',
            'locked_permanently' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * True when the account is temporarily locked (locked_until is in the future).
     */
    public function isLocked(): bool
    {
        return $this->locked_permanently || ($this->locked_until !== null && $this->locked_until->isFuture());
    }

    /** True when locked permanently by an admin (not the 15m auto-lock from failed logins). */
    public function isPermanentlyLocked(): bool
    {
        return (bool) $this->locked_permanently;
    }
}

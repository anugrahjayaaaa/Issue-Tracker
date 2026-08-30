<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $action,
        public ?string $ip = null,
    ) {}

    /** Human-readable label for the auth event. */
    public function label(): string
    {
        return match ($this->action) {
            'login_success' => 'Login successful',
            'logout' => 'Logged out',
            'login_failed' => 'Login failed',
            'password_reset' => 'Password reset requested',
            'email_verified' => 'Email verified',
            default => str_replace('_', ' ', ucfirst($this->action)),
        };
    }

    /** @return array<int,string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string,mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->action,
            'label' => $this->label(),
            'ip' => $this->ip,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->line("Auth event: {$this->action}");
    }
}

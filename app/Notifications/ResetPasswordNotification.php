<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification 
{
    use Queueable;

    public function __construct(
        private readonly string $token
    ) {
    }

    /**
     * Notification delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the reset password email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim(
            config('app.frontend_url', 'http://localhost:5173'),
            '/'
        );

        $resetUrl = $frontendUrl
            . '/reset-password/'
            . urlencode($this->token)
            . '?email='
            . urlencode($notifiable->getEmailForPasswordReset());

        return (new MailMessage)
            ->subject('Reset Your PortfolioHub Password')
            ->greeting('Hello ' . ($notifiable->fullName ?? $notifiable->name ?? 'PortfolioHub User') . ',')
            ->line('We received a request to reset your PortfolioHub password.')
            ->action('Reset Password', $resetUrl)
            ->line('This password reset link will expire in '
                . config('auth.passwords.users.expire', 60)
                . ' minutes.')
            ->line('If you did not request a password reset, you can safely ignore this email.')
            ->salutation('Regards, PortfolioHub Team');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
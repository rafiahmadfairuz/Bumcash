<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class BumcashResetPassword extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Atur ulang kata sandi akun Bumcash')
            ->view('emails.reset-password', [
                'user' => $notifiable,
                'resetUrl' => $url,
                'expireMinutes' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]);
    }
}

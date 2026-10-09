<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public string $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Restablecer Contraseña - MotaCastAudio')
            ->greeting('¡Hola, '.($notifiable->name ?? 'Usuario').'!')
            ->line('Recibiste este correo porque solicitaste restablecer la contraseña de tu cuenta en MotaCastAudio.')
            ->action('Restablecer Contraseña', $resetUrl)
            ->line('Este enlace de restablecimiento expirará en 60 minutos.')
            ->line('Si no solicitaste este cambio, no es necesario realizar ninguna acción; tu contraseña actual permanecerá intacta.')
            ->salutation('Atentamente, el equipo de MotaCastAudio.');
    }
}

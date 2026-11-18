<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpirationReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Subscription $subscription,
        public int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Recordatorio de vencimiento de suscripción')
            ->greeting("Hola {$notifiable->name}")
            ->line("Tu prueba gratuita vence en {$this->daysRemaining} días.")
            ->line('Revisa tu suscripción para evitar la suspensión de la tienda.')
            ->action('Revisar suscripción', url('/dashboard'));
    }
}

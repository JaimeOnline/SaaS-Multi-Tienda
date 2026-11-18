<?php

namespace Tests\Unit;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpirationReminder;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class SubscriptionExpirationReminderTest extends TestCase
{
    public function test_notification_uses_mail_channel_and_contains_expiration_details(): void
    {
        $subscription = new Subscription();

        $notification = new SubscriptionExpirationReminder($subscription, 3);

        $this->assertSame(['mail'], $notification->via(new \stdClass()));

        $mail = $notification->toMail((object) ['name' => 'Jaime']);

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame(
            'Recordatorio de vencimiento de suscripción',
            $mail->subject
        );
        $this->assertSame(3, $notification->daysRemaining);
    }
}
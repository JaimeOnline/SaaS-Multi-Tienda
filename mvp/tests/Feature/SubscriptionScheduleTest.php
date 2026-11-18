<?php

namespace Tests\Feature;

use Tests\TestCase;

class SubscriptionScheduleTest extends TestCase
{
    public function test_subscription_expiration_command_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('subscriptions:expire')
            ->assertExitCode(0);
    }
}

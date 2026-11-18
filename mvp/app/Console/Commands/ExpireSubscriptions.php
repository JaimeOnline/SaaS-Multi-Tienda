<?php

namespace App\Console\Commands;

use App\Services\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Procesa las transiciones de suscripciones vencidas';

    public function handle(SubscriptionLifecycleService $lifecycleService): int
    {
        $expiredCount = $lifecycleService->expireTrials();
        $gracePeriodCount = $lifecycleService->startGracePeriods();
        $suspendedCount = $lifecycleService->suspendFinishedGracePeriods();

        $this->info("Suscripciones expiradas: {$expiredCount}");
        $this->info("Periodos de gracia iniciados: {$gracePeriodCount}");
        $this->info("Suscripciones suspendidas: {$suspendedCount}");

        return self::SUCCESS;
    }
}

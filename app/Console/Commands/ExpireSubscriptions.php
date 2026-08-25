<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark subscriptions past their end date as expired and drop those businesses back to the Free plan';

    public function handle(SubscriptionService $subscriptions): int
    {
        $count = $subscriptions->expireOverdueSubscriptions();

        $this->info("Expired {$count} subscription(s).");

        return self::SUCCESS;
    }
}

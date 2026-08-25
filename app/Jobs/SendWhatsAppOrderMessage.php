<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\SubscriptionService;
use App\Services\WhatsAppCloudApiService;
use App\Services\WhatsAppMessageFormatter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sends an automated status-update message to the customer via the
 * business's own WhatsApp Cloud API credentials. Always queued — an
 * external API call must never block the request that triggered it
 * (a staff member updating an order, or a payment webhook).
 *
 * A business without Cloud API credentials configured is an expected,
 * common state (see WhatsAppCloudApiService::sendTextMessage) — this
 * job simply no-ops in that case rather than failing.
 */
class SendWhatsAppOrderMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 60, 300];

    public function __construct(
        public readonly int $orderId,
        public readonly string $event,
    ) {}

    public function handle(WhatsAppCloudApiService $whatsapp, WhatsAppMessageFormatter $formatter, SubscriptionService $subscriptions): void
    {
        $order = Order::withoutGlobalScopes()->with(['business', 'customer'])->find($this->orderId);

        if (! $order || ! $order->business->hasWhatsAppCloudApi()) {
            return;
        }

        if (! $subscriptions->hasFeature($order->business, 'whatsapp_cloud_api')) {
            return;
        }

        $message = $formatter->statusUpdateMessage($order, $this->event);

        $whatsapp->sendTextMessage($order->business, $order->customer->phone, $message);
    }
}

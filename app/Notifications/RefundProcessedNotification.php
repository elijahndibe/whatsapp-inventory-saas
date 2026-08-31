<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order, public readonly bool $isFullRefund) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! method_exists($notifiable, 'wantsEmailNotification') || $notifiable->wantsEmailNotification('refund_processed')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $symbol = $this->order->currencySymbol();
        $label = $this->isFullRefund ? 'Refund' : 'Partial refund';

        return (new MailMessage)
            ->subject("{$label} processed for order #{$this->order->order_number}")
            ->greeting("{$label} processed")
            ->line("Order #{$this->order->order_number} — {$symbol}".number_format($this->order->total, 2).' has had a refund recorded.')
            ->action('View Order', route('orders.show', $this->order));
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->isFullRefund ? 'Refund' : 'Partial refund';

        return [
            'type' => 'refund_processed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'currency' => $this->order->currency,
            'message' => "{$label} processed for order #{$this->order->order_number}",
            'url' => route('orders.show', $this->order),
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! method_exists($notifiable, 'wantsEmailNotification') || $notifiable->wantsEmailNotification('payment_received')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $symbol = $this->order->currencySymbol();

        return (new MailMessage)
            ->subject("Payment received for order #{$this->order->order_number}")
            ->greeting('Payment received')
            ->line("Order #{$this->order->order_number} — {$symbol}".number_format($this->order->total, 2).' has been paid.')
            ->action('View Order', route('orders.show', $this->order));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payment_received',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'currency' => $this->order->currency,
            'message' => "Payment received for order #{$this->order->order_number}",
            'url' => route('orders.show', $this->order),
        ];
    }
}

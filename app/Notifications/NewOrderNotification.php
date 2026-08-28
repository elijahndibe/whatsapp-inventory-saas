<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Order $order) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! method_exists($notifiable, 'wantsEmailNotification') || $notifiable->wantsEmailNotification('new_order')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $symbol = $this->order->currencySymbol();

        return (new MailMessage)
            ->subject("New order #{$this->order->order_number}")
            ->greeting("New order from {$this->order->customer->name}")
            ->line("Order #{$this->order->order_number} — {$symbol}".number_format($this->order->total, 2))
            ->line('Placed via '.ucfirst($this->order->payment_method ?? 'whatsapp').'.')
            ->action('View Order', route('orders.show', $this->order))
            ->line('Thank you for using our platform!');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->customer->name,
            'total' => $this->order->total,
            'currency' => $this->order->currency,
            'message' => "New order #{$this->order->order_number} from {$this->order->customer->name}",
            'url' => route('orders.show', $this->order),
        ];
    }
}

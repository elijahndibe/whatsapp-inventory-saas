<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Product $product) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $level = $this->product->stock_quantity <= 0 ? 'out of stock' : 'running low';

        return (new MailMessage)
            ->subject("Stock alert: {$this->product->name}")
            ->greeting("\"{$this->product->name}\" is {$level}")
            ->line("Current stock: {$this->product->stock_quantity} (threshold: {$this->product->low_stock_threshold})")
            ->action('View Product', route('products.edit', $this->product));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock_quantity' => $this->product->stock_quantity,
            'message' => "\"{$this->product->name}\" is ".($this->product->stock_quantity <= 0 ? 'out of stock' : 'running low')." ({$this->product->stock_quantity} left)",
            'url' => route('products.edit', $this->product),
        ];
    }
}

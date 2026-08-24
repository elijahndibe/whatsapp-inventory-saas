<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Business;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Scopes\BusinessScope;
use App\Notifications\LowStockNotification;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * The single place stock is ever mutated. Every change is applied under a
 * row lock (SELECT ... FOR UPDATE) so two concurrent requests can never
 * both read stale stock and push it negative, and every change is logged
 * to inventory_transactions so "why did this number change" always has
 * an answer.
 */
class InventoryService
{
    /**
     * Increase stock — purchases, returns from customers, etc.
     */
    public function increase(Product $product, int $quantity, string $type = 'purchase', array $options = []): InventoryTransaction
    {
        $this->assertPositiveQuantity($quantity);

        return $this->applyChange($product, $type, $options, fn (int $previous) => $previous + $quantity);
    }

    /**
     * Decrease stock — sales, damage, etc. Throws InsufficientStockException
     * unless the business has explicitly enabled overselling.
     */
    public function decrease(Product $product, int $quantity, string $type = 'sale', array $options = []): InventoryTransaction
    {
        $this->assertPositiveQuantity($quantity);

        return $this->applyChange($product, $type, $options, fn (int $previous) => $previous - $quantity);
    }

    /**
     * Set stock to an explicit absolute value (a manual recount). Always
     * logged as an 'adjustment' regardless of whether it moved stock up
     * or down.
     */
    public function adjustTo(Product $product, int $newQuantity, array $options = []): InventoryTransaction
    {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('Stock quantity cannot be adjusted to a negative value.');
        }

        return $this->applyChange($product, 'adjustment', $options, fn () => $newQuantity);
    }

    private function applyChange(Product $product, string $type, array $options, Closure $resolveNewQuantity): InventoryTransaction
    {
        $transaction = DB::transaction(function () use ($product, $type, $options, $resolveNewQuantity) {
            $locked = Product::withoutGlobalScope(BusinessScope::class)
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $previousQuantity = $locked->stock_quantity;
            $newQuantity = $resolveNewQuantity($previousQuantity);
            $delta = $newQuantity - $previousQuantity;

            if ($delta === 0) {
                throw new InvalidArgumentException('Inventory transaction results in no stock change.');
            }

            if ($newQuantity < 0 && ! $locked->business->allow_overselling) {
                throw new InsufficientStockException($locked, $previousQuantity, abs($delta));
            }

            $locked->forceFill(['stock_quantity' => $newQuantity])->save();

            $transaction = InventoryTransaction::create([
                'business_id' => $locked->business_id,
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => $delta,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $newQuantity,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'notes' => $options['notes'] ?? null,
                'created_by' => $options['created_by'] ?? Auth::id(),
            ]);

            // Keep the caller's in-memory instance consistent with what was persisted.
            $product->setRawAttributes($locked->getAttributes());

            return $transaction;
        });

        $this->notifyIfCrossedIntoLowStock($product, $transaction);

        return $transaction;
    }

    /**
     * Fires once when a decrease pushes stock from healthy into low/out-of
     * -stock territory — not on every subsequent sale while it stays low,
     * which would spam staff with a notification per order.
     */
    private function notifyIfCrossedIntoLowStock(Product $product, InventoryTransaction $transaction): void
    {
        if ($transaction->quantity >= 0) {
            return; // only decreases can cross into low stock
        }

        $wasHealthy = $transaction->previous_quantity > $product->low_stock_threshold;
        $isLowNow = $product->stock_quantity <= $product->low_stock_threshold;

        if (! ($wasHealthy && $isLowNow)) {
            return;
        }

        $business = $product->business ?? Business::withoutGlobalScopes()->find($product->business_id);
        Notification::send($business->staffWithPermission('view inventory'), new LowStockNotification($product));
    }

    private function assertPositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }
    }
}

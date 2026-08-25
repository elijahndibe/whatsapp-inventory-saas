<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable audit-log row: every stock change, ever, with the reason.
 * Rows are never updated or deleted through the app — only inserted by
 * InventoryService.
 */
class InventoryTransaction extends Model
{
    use BelongsToTenant;

    public const UPDATED_AT = null;

    protected $fillable = [
        'business_id',
        'product_id',
        'type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reference_type',
        'reference_id',
        'from_location_id',
        'to_location_id',
        'notes',
        'created_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(BusinessLocation::class, 'to_location_id');
    }
}

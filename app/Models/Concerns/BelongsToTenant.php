<?php

namespace App\Models\Concerns;

use App\Models\Business;
use App\Models\Scopes\BusinessScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Apply to every tenant-owned model (Product, Order, Customer, ...).
 *
 * - Auto-scopes all queries to the current user's business (BusinessScope).
 * - Auto-fills business_id when creating new records so callers never have
 *   to remember to set it (and can't forget to, either).
 *
 * Background jobs / webhook handlers that run without an authenticated user
 * must use Model::withoutGlobalScope(BusinessScope::class) explicitly and set
 * business_id themselves — this makes the "no ambient tenant" case visible
 * in the code rather than silently scoped to nothing.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new BusinessScope);

        static::creating(function ($model) {
            if (empty($model->business_id) && Auth::check()) {
                $model->business_id = Auth::user()->business_id;
            }
        });
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function scopeForBusiness(Builder $query, int $businessId): Builder
    {
        return $query->withoutGlobalScope(BusinessScope::class)
            ->where($this->getTable().'.business_id', $businessId);
    }
}

<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically constrains every query on a tenant-owned model to the
 * authenticated user's business. This is the primary defense against
 * cross-tenant data leaks: it applies even if a controller forgets to
 * scope a query explicitly.
 */
class BusinessScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && Auth::user()->business_id) {
            $builder->where($model->getTable().'.business_id', Auth::user()->business_id);
        }
    }
}

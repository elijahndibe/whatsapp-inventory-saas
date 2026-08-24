<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'address',
        'city',
        'state',
        'notes',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

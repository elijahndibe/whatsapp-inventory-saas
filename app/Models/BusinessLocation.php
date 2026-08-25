<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessLocation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'business_locations';

    protected $fillable = [
        'business_id',
        'name',
        'address',
        'phone',
        'status',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function stock(): HasMany
    {
        return $this->hasMany(ProductLocationStock::class, 'location_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'location_user', 'location_id', 'user_id');
    }
}

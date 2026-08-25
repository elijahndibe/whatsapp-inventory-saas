<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_LIMIT = 'limit';

    protected $fillable = [
        'key',
        'name',
        'description',
        'type',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }
}

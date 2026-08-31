<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'name',
        'slug',
        'description',
        'image',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::generateUniqueSlug($category->name, $category->business_id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $businessId): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (static::withoutGlobalScopes()->where('business_id', $businessId)->where('slug', $slug)->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * The curated department => [names] library sellers pick from when
     * adding a product (config/product_categories.php) — not a table,
     * just names a business's own Category rows get find-or-created from.
     *
     * @return array<string, list<string>>
     */
    public static function suggestedByDepartment(): array
    {
        return config('product_categories', []);
    }

    /**
     * @return list<string>
     */
    public static function suggestedNames(): array
    {
        $byDepartment = static::suggestedByDepartment();

        return $byDepartment === [] ? [] : array_merge(...array_values($byDepartment));
    }

    public static function isSuggestedName(string $name): bool
    {
        return in_array($name, static::suggestedNames(), true);
    }
}

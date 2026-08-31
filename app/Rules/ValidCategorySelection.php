<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts everything products/_form.blade.php's category <select> can
 * submit: blank ("Uncategorized"), 'new' (paired with new_category_name —
 * see ProductController::resolveCategoryId()), 'suggested:<name>' (one of
 * the curated names from Category::suggestedNames()), or a real category
 * id belonging to this business. Shared by Store/UpdateProductRequest so
 * the two can't drift out of sync with each other.
 */
class ValidCategorySelection implements ValidationRule
{
    public function __construct(private readonly int $businessId) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value) || $value === 'new') {
            return;
        }

        if (is_string($value) && str_starts_with($value, 'suggested:')) {
            $name = substr($value, strlen('suggested:'));

            if (! Category::isSuggestedName($name)) {
                $fail('The selected category is invalid.');
            }

            return;
        }

        if (! Category::where('business_id', $this->businessId)->where('id', $value)->exists()) {
            $fail('The selected category is invalid.');
        }
    }
}

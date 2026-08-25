<?php

namespace App\Http\Requests\Staff;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage staff');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', 'in:Admin,Staff'],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(RolesAndPermissionsSeeder::PERMISSIONS)],
            'locations' => ['array'],
            'locations.*' => [
                Rule::exists('business_locations', 'id')->where('business_id', $this->user()->business_id),
            ],
        ];
    }
}

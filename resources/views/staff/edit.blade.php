@php
    $currentRole = $user->roles->pluck('name')->first() ?? 'Staff';
    $currentPermissions = $user->getDirectPermissionNames()->all();
    $currentLocations = $user->locations->pluck('id')->all();
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[28px] leading-8 font-semibold text-ink dark:text-gray-100">{{ __('Edit') }}: {{ $user->name }}</h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-card p-6">
                <form method="POST" action="{{ route('staff.update', $user) }}" x-data="{ role: '{{ old('role', $currentRole) }}' }">
                    @csrf
                    @method('PUT')

                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $user->email }}</div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" x-model="role" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm">
                            <option value="Admin" @selected(old('role', $currentRole) === 'Admin')>{{ __('Admin — full access except staff, settings and payments') }}</option>
                            <option value="Staff" @selected(old('role', $currentRole) === 'Staff')>{{ __('Staff — choose exactly what they can access') }}</option>
                        </select>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm">
                            <option value="active" @selected(old('status', $user->status) === 'active')>{{ __('Active') }}</option>
                            <option value="inactive" @selected(old('status', $user->status) === 'inactive')>{{ __('Inactive (blocked from signing in)') }}</option>
                        </select>
                    </div>

                    <div class="mt-4" x-show="role === 'Staff'" x-cloak>
                        <x-input-label :value="__('Permissions')" />
                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach (\Database\Seeders\RolesAndPermissionsSeeder::PERMISSIONS as $permission)
                                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', $currentPermissions)))
                                           class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                                    {{ ucfirst($permission) }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    @if ($locations->isNotEmpty())
                        <div class="mt-4">
                            <x-input-label :value="__('Assigned Locations')" />
                            <div class="mt-2 space-y-1">
                                @foreach ($locations as $location)
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="locations[]" value="{{ $location->id }}" @checked(in_array($location->id, old('locations', $currentLocations)))
                                               class="rounded border-gray-300 dark:border-gray-700 text-brand-600 shadow-sm focus:ring-brand-500" />
                                        {{ $location->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-end mt-6 gap-3">
                        <a href="{{ route('staff.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

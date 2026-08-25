<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\BusinessLocation;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private readonly FeatureService $features) {}

    public function index(Request $request): View
    {
        $this->authorize('manage staff');

        $staff = $request->user()->business->users()->with(['roles', 'locations'])->orderBy('name')->get();

        return view('staff.index', compact('staff'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('manage staff');

        if (! $this->features->withinLimit($request->user()->business, 'staff', $request->user()->business->users()->count())) {
            return redirect()->route('staff.index')
                ->with('error', 'You have reached your plan\'s staff limit. Upgrade to add more team members.');
        }

        $locations = $request->user()->business->locations()->get();

        return view('staff.create', compact('locations'));
    }

    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $business = $request->user()->business;

        if (! $this->features->withinLimit($business, 'staff', $business->users()->count())) {
            return redirect()->route('staff.index')
                ->with('error', 'You have reached your plan\'s staff limit. Upgrade to add more team members.');
        }

        $data = $request->validated();
        $password = Str::password(12);

        $user = User::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
        ]);

        $user->assignRole($data['role']);

        if ($data['role'] === 'Staff') {
            $user->syncPermissions($data['permissions'] ?? []);
        }

        if (! empty($data['locations'])) {
            $user->locations()->sync($data['locations']);
        }

        return redirect()->route('staff.index')->with('status',
            "Staff member added. Share these sign-in details securely — the password won't be shown again: {$data['email']} / {$password}"
        );
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorize('manage staff');
        $this->guardCanManage($request, $user);

        $locations = $user->business->locations()->get();

        return view('staff.edit', compact('user', 'locations'));
    }

    public function update(UpdateStaffRequest $request, User $user): RedirectResponse
    {
        $this->guardCanManage($request, $user);

        $data = $request->validated();

        $user->update(['status' => $data['status']]);
        $user->syncRoles([$data['role']]);
        $user->syncPermissions($data['role'] === 'Staff' ? ($data['permissions'] ?? []) : []);
        $user->locations()->sync($data['locations'] ?? []);

        return redirect()->route('staff.index')->with('status', 'Staff member updated.');
    }

    /**
     * User isn't tenant-scoped by BusinessScope (it can't be — it's the
     * tenant boundary itself), so route-model-bound {user} could resolve
     * to a user from a completely different business. This is the check
     * that actually enforces "only your own staff", and it must run
     * before the Owner check below.
     */
    private function guardCanManage(Request $request, User $user): void
    {
        abort_unless($user->business_id === $request->user()->business_id, 404);
        abort_if($user->hasRole('Owner'), 403, 'The business owner cannot be managed from this screen.');
    }
}

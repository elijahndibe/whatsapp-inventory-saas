<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function index(Request $request): View
    {
        $businesses = Business::withCount('users')
            ->with(['subscriptions' => fn ($q) => $q->where('status', 'active')->with('plan')->latest('id')->limit(1)])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.businesses.index', compact('businesses'));
    }

    public function show(Business $business): View
    {
        $business->load(['users', 'subscriptions' => fn ($q) => $q->with('plan')->latest('id')]);
        $stats = [
            'products' => $business->products()->count(),
            'orders' => $business->orders()->count(),
            'customers' => $business->customers()->count(),
        ];

        return view('admin.businesses.show', compact('business', 'stats'));
    }

    public function suspend(Business $business): RedirectResponse
    {
        $business->update(['status' => 'suspended']);

        return back()->with('status', "{$business->name} has been suspended.");
    }

    public function activate(Business $business): RedirectResponse
    {
        $business->update(['status' => 'active']);

        return back()->with('status', "{$business->name} has been activated.");
    }

    public function updateCommission(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $old = $business->commission_rate;
        $new = $data['commission_rate'] ?? null;

        $business->update(['commission_rate' => $new]);
        $this->audit->record($request->user(), "business.commission_rate.changed:{$business->id}", $old, $new);

        return back()->with('status', $new === null
            ? "{$business->name} now uses the default platform commission."
            : "{$business->name} now has a custom commission rate of {$new}%.");
    }
}

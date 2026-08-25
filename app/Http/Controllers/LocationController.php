<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Requests\Location\UpdateLocationRequest;
use App\Models\BusinessLocation;
use App\Services\FeatureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function __construct(private readonly FeatureService $features) {}

    public function index(Request $request): View
    {
        $this->authorize('manage settings');

        $locations = $request->user()->business->locations()->withCount('stock')->orderByDesc('is_default')->get();

        return view('locations.index', compact('locations'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('manage settings');

        if (! $this->features->withinLimit($request->user()->business, 'locations', $request->user()->business->locations()->count())) {
            return redirect()->route('locations.index')
                ->with('error', 'You have reached your plan\'s location limit. Upgrade to add more branches.');
        }

        return view('locations.create');
    }

    public function store(StoreLocationRequest $request): RedirectResponse
    {
        $business = $request->user()->business;

        if (! $this->features->withinLimit($business, 'locations', $business->locations()->count())) {
            return redirect()->route('locations.index')
                ->with('error', 'You have reached your plan\'s location limit. Upgrade to add more branches.');
        }

        $data = $request->validated();
        $data['is_default'] = ! $business->locations()->exists();

        $business->locations()->create($data);

        return redirect()->route('locations.index')->with('status', 'Location added.');
    }

    public function edit(BusinessLocation $location): View
    {
        $this->authorize('manage settings');

        return view('locations.edit', compact('location'));
    }

    public function update(UpdateLocationRequest $request, BusinessLocation $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()->route('locations.index')->with('status', 'Location updated.');
    }

    public function destroy(BusinessLocation $location): RedirectResponse
    {
        $this->authorize('manage settings');

        if ($location->is_default) {
            return back()->with('error', 'Cannot delete the default location.');
        }

        if ($location->stock()->where('quantity', '>', 0)->exists()) {
            return back()->with('error', 'Transfer out all stock allocated to this location before deleting it.');
        }

        $location->delete();

        return redirect()->route('locations.index')->with('status', 'Location deleted.');
    }
}

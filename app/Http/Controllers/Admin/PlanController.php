<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->orderBy('sort_order')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        Plan::create($this->normalizeFeatures($request->validated()));

        return redirect()->route('admin.plans.index')->with('status', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->normalizeFeatures($request->validated()));

        return redirect()->route('admin.plans.index')->with('status', 'Plan updated.');
    }

    /**
     * Unchecked checkboxes simply aren't present in the request, so make
     * every known feature explicit (true or false) rather than leaving
     * previously-enabled features stuck on because their key was missing.
     */
    private function normalizeFeatures(array $data): array
    {
        $submitted = $data['features'] ?? [];
        $data['features'] = collect(array_keys(Plan::FEATURES))
            ->mapWithKeys(fn ($key) => [$key => (bool) ($submitted[$key] ?? false)])
            ->all();

        return $data;
    }
}

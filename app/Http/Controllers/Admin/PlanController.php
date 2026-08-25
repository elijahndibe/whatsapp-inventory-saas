<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Manages plan identity (name/price/duration) only. Feature access and
 * numeric limits per plan are managed separately on Admin > Features,
 * which edits the plan_features matrix directly — see FeatureController.
 */
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
        $plan = Plan::create($request->validated());
        $this->ensureSingleDefault($plan);

        return redirect()->route('admin.plans.index')->with('status', 'Plan created.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());
        $this->ensureSingleDefault($plan);

        return redirect()->route('admin.plans.index')->with('status', 'Plan updated.');
    }

    private function ensureSingleDefault(Plan $plan): void
    {
        if ($plan->is_default) {
            DB::table('plans')->where('id', '!=', $plan->id)->update(['is_default' => false]);
        }
    }
}

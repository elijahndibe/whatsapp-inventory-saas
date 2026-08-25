<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The admin-controlled Feature x Plan matrix (Admin > Features). This is
 * the only place feature availability/limits are configured — nothing
 * about them is hardcoded in application code; see FeatureService for how
 * these rows are consumed.
 */
class FeatureController extends Controller
{
    public function __construct(private readonly AuditLogService $audit) {}

    public function index(): View
    {
        $features = Feature::orderBy('name')->get();
        $plans = Plan::orderBy('sort_order')->get();
        $planFeatures = PlanFeature::all()->groupBy(fn (PlanFeature $pf) => "{$pf->plan_id}-{$pf->feature_id}");

        return view('admin.features.index', compact('features', 'plans', 'planFeatures'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:features,key'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:boolean,limit'],
        ]);

        $feature = Feature::create($data + ['is_enabled' => true]);

        $this->audit->record($request->user(), 'feature.created', null, $feature->only(['key', 'name', 'type']));

        return redirect()->route('admin.features.index')->with('status', 'Feature added.');
    }

    public function update(Request $request): RedirectResponse
    {
        $submitted = $request->input('features', []);
        $features = Feature::all()->keyBy('id');

        foreach ($submitted as $featureId => $row) {
            $feature = $features->get($featureId);

            if (! $feature) {
                continue;
            }

            $globalEnabled = (bool) ($row['global_enabled'] ?? false);
            if ($globalEnabled !== $feature->is_enabled) {
                $this->audit->record($request->user(), 'feature.global_enabled.changed', $feature->is_enabled, $globalEnabled);
                $feature->update(['is_enabled' => $globalEnabled]);
            }

            foreach ($row['plans'] ?? [] as $planId => $planRow) {
                $enabled = $feature->type === Feature::TYPE_LIMIT
                    ? true // a limit row is "on" as long as it has a value (0 = blocked, blank = unlimited)
                    : (bool) ($planRow['enabled'] ?? false);

                $value = $feature->type === Feature::TYPE_LIMIT
                    ? (is_numeric($planRow['value'] ?? null) ? (int) $planRow['value'] : null)
                    : null;

                $existing = PlanFeature::where('plan_id', $planId)->where('feature_id', $feature->id)->first();
                $old = $existing ? ['enabled' => $existing->enabled, 'value' => $existing->value] : null;
                $new = ['enabled' => $enabled, 'value' => $value];

                if ($old !== $new) {
                    PlanFeature::updateOrCreate(
                        ['plan_id' => $planId, 'feature_id' => $feature->id],
                        $new,
                    );
                    $this->audit->record($request->user(), "plan_feature.changed:{$feature->key}:plan-{$planId}", $old, $new);
                }
            }
        }

        return redirect()->route('admin.features.index')->with('status', 'Feature matrix updated.');
    }
}

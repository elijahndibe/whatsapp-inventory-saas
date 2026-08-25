<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\PlatformSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin > Monetization: the single screen a super admin uses to switch
 * the platform between commission-only and commission + subscription
 * modes, and to tune commission without ever touching code — see
 * PlatformSettingsService for how these values are read everywhere else.
 */
class MonetizationController extends Controller
{
    public function __construct(
        private readonly PlatformSettingsService $settings,
        private readonly AuditLogService $audit,
    ) {}

    public function index(): View
    {
        $paystackConfigured = filled(config('services.paystack.secret_key'));

        return view('admin.monetization.index', [
            'commissionEnabled' => $this->settings->commissionEnabled(),
            'commissionType' => $this->settings->commissionType(),
            'commissionRate' => $this->settings->commissionRate(),
            'commissionMin' => $this->settings->commissionMin(),
            'commissionMax' => $this->settings->commissionMax(),
            'subscriptionEnabled' => $this->settings->subscriptionSystemEnabled(),
            'paystackConfigured' => $paystackConfigured,
        ]);
    }

    public function updateCommission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commission_enabled' => ['boolean'],
            'commission_type' => ['required', 'in:percentage,fixed'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'commission_min' => ['nullable', 'numeric', 'min:0'],
            'commission_max' => ['nullable', 'numeric', 'min:0'],
        ]);

        $admin = $request->user();

        $this->settings->set('commission.enabled', (bool) ($data['commission_enabled'] ?? false), $admin, $this->audit);
        $this->settings->set('commission.type', $data['commission_type'], $admin, $this->audit);
        $this->settings->set('commission.rate', (float) $data['commission_rate'], $admin, $this->audit);
        $this->settings->set('commission.min', isset($data['commission_min']) ? (float) $data['commission_min'] : null, $admin, $this->audit);
        $this->settings->set('commission.max', isset($data['commission_max']) ? (float) $data['commission_max'] : null, $admin, $this->audit);

        return redirect()->route('admin.monetization.index')->with('status', 'Commission settings updated.');
    }

    public function updateSubscriptionSystem(Request $request): RedirectResponse
    {
        $data = $request->validate(['subscription_enabled' => ['boolean']]);

        $this->settings->set('subscription.enabled', (bool) ($data['subscription_enabled'] ?? false), $request->user(), $this->audit);

        return redirect()->route('admin.monetization.index')->with('status', 'Subscription system setting updated.');
    }
}

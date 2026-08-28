<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /**
     * A post-registration checklist, not a gate — every step here links to
     * a real, already-existing screen (Products, Settings > Payments,
     * WhatsApp) rather than duplicating any of their logic. "Add products"
     * and "Connect Paystack" are read directly off existing data, so
     * there's nothing new to keep in sync if a business does either from
     * outside this page.
     */
    public function show(Request $request): View
    {
        $business = $request->user()->business;

        return view('onboarding.show', [
            'business' => $business,
            'hasProducts' => $business->products()->exists(),
            'hasPaystack' => $business->hasPaystackSubaccount(),
        ]);
    }

    /**
     * "Skip" and "Finish" are the same action — the checklist is a helpful
     * default landing spot right after signup, never a mandatory gate (the
     * same "no forced steps" spirit as storefront checkout elsewhere).
     */
    public function finish(Request $request): RedirectResponse
    {
        $business = $request->user()->business;

        if (! $business->onboarding_completed_at) {
            $business->update(['onboarding_completed_at' => now()]);
        }

        return redirect()->route('dashboard');
    }
}

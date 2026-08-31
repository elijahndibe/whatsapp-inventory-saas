<?php

namespace App\Http\Controllers;

use App\Exceptions\PaystackException;
use App\Http\Requests\UpdateBusinessSettingsRequest;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessSettingsController extends Controller
{
    public function __construct(private readonly PaystackService $paystack) {}

    public function edit(Request $request): View
    {
        $this->authorize('manage settings');

        $business = $request->user()->business;

        return view('settings.edit', [
            'business' => $business,
            // Only fetched for a business that still needs to connect a
            // payout account — no point calling out for a list nobody on
            // this page load will see. Falls back to null (the form shows
            // a plain bank-code field instead of a name dropdown) rather
            // than breaking the whole Settings page over an unrelated
            // outage — connecting payouts isn't required to use Zwenko.
            'banks' => $business->hasPaystackSubaccount() ? null : $this->fetchBanks(),
        ]);
    }

    private function fetchBanks(): ?array
    {
        try {
            return $this->paystack->listBanks();
        } catch (PaystackException $e) {
            Log::warning('Could not load the payout bank list for Settings', ['message' => $e->getMessage()]);

            return null;
        }
    }

    public function update(UpdateBusinessSettingsRequest $request): RedirectResponse
    {
        $business = $request->user()->business;
        $data = $request->validated();

        // Never overwrite the stored access token with blank just because
        // the field was left empty in the form — the current value is
        // deliberately never rendered back into the input.
        if (blank($data['whatsapp_access_token'] ?? null)) {
            unset($data['whatsapp_access_token']);
        }

        if ($request->hasFile('logo')) {
            if ($business->logo) {
                Storage::disk('public')->delete($business->logo);
            }
            $data['logo'] = $request->file('logo')->store('businesses', 'public');
        } else {
            unset($data['logo']);
        }

        $business->update($data);

        return redirect()->route('settings.edit')->with('status', 'Settings updated.');
    }
}

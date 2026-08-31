<?php

namespace App\Http\Controllers;

use App\Exceptions\PaystackException;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Lets a seller connect their own bank account so Paystack can split each
 * transaction automatically at settlement — see PaymentService for how
 * the resulting subaccount_code is used per-transaction.
 */
class PaystackConnectController extends Controller
{
    public function __construct(private readonly PaystackService $paystack) {}

    public function connect(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');

        $data = $request->validate([
            'settlement_bank' => ['required', 'string'],
            'account_number' => ['required', 'string'],
        ]);

        $business = $request->user()->business;

        try {
            $result = $this->paystack->createSubaccount([
                'business_name' => $business->name,
                'settlement_bank' => $data['settlement_bank'],
                'account_number' => $data['account_number'],
            ]);
        } catch (PaystackException $e) {
            // The underlying message is our payment processor's own wording
            // (e.g. "Paystack failed to create the subaccount: ...") — fine
            // to log, but a seller shouldn't see that processor's name or
            // its raw API error text.
            Log::warning('Failed to connect a seller bank account', ['business_id' => $business->id, 'message' => $e->getMessage()]);

            return back()->with('error', 'Unable to connect your bank account. Please double-check your details and try again.');
        }

        $business->update([
            'paystack_subaccount_code' => $result['subaccount_code'],
            'paystack_bank_code' => $data['settlement_bank'],
            'paystack_account_number' => $data['account_number'],
            'paystack_account_name' => $result['account_name'] ?? null,
        ]);

        return redirect()->route('settings.edit')->with('status', 'Bank account connected.');
    }
}

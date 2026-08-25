<?php

namespace App\Http\Controllers;

use App\Exceptions\WhatsAppEmbeddedSignupException;
use App\Http\Requests\ConnectWhatsAppRequest;
use App\Models\Business;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * The server-side half of "Connect WhatsApp" — see
 * resources/views/settings/edit.blade.php for the FB.login()/postMessage
 * JS that gets a business owner here, and WhatsAppCloudApiService for the
 * actual Graph API calls this orchestrates. WHATSAPP_SETUP.md documents
 * the one-time Meta Developer Dashboard configuration this whole flow
 * depends on.
 */
class WhatsAppConnectController extends Controller
{
    public function __construct(private readonly WhatsAppCloudApiService $whatsapp) {}

    public function connect(ConnectWhatsAppRequest $request): RedirectResponse
    {
        $business = $request->user()->business;
        $data = $request->validated();

        // The hard multi-tenant guarantee: this exact number must not
        // already belong to a different business. The database-level
        // unique index is the real backstop (see the 2026_08_26 migration)
        // — this check exists purely to turn that into a clear, friendly
        // error instead of a raw constraint-violation 500.
        $alreadyConnected = Business::where('whatsapp_phone_number_id', $data['phone_number_id'])
            ->where('id', '!=', $business->id)
            ->exists();

        if ($alreadyConnected) {
            return back()->with('error', 'This WhatsApp number is already connected to another store. Disconnect it there first, or choose a different number.');
        }

        $appId = config('services.whatsapp.app_id');
        $appSecret = config('services.whatsapp.app_secret');

        if (! $appId || ! $appSecret || ! config('services.whatsapp.embedded_signup_config_id')) {
            Log::error('WhatsApp Embedded Signup attempted but the platform Meta app is not configured.');

            return back()->with('error', 'WhatsApp connection is not available right now. Please contact support.');
        }

        try {
            $token = $this->whatsapp->exchangeCodeForToken($data['code'], $appId, $appSecret);
            $this->whatsapp->subscribeAppToWaba($data['waba_id'], $token);
            $this->whatsapp->registerPhoneNumber($data['phone_number_id'], $token);
            $details = $this->whatsapp->fetchPhoneNumberDetails($data['phone_number_id'], $token);
        } catch (WhatsAppEmbeddedSignupException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            // Anything else — a Meta API timeout, DNS failure, etc. — is
            // still a connection failure from the store owner's point of
            // view and must never surface as a raw error page.
            Log::error('WhatsApp Embedded Signup failed unexpectedly.', ['message' => $e->getMessage()]);

            return back()->with('error', 'We couldn\'t complete the WhatsApp connection right now. Please try again in a moment.');
        }

        $business->update([
            'whatsapp_phone_number_id' => $data['phone_number_id'],
            'whatsapp_business_account_id' => $data['waba_id'],
            'whatsapp_access_token' => null, // Embedded Signup uses the shared platform token — see Business::whatsappAccessToken().
            'whatsapp_connected_via' => 'embedded_signup',
            'whatsapp_display_phone_number' => $details['display_phone_number'],
            'whatsapp_connected_at' => now(),
            // wa.me click-to-chat already works off this field; keep it in
            // sync so the storefront's "Order via WhatsApp" link points at
            // the same number that was just connected.
            'whatsapp_number' => $details['display_phone_number'] ?? $business->whatsapp_number,
        ]);

        return redirect()->route('settings.edit')->with('status', 'WhatsApp connected.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $this->authorize('manage settings');

        $business = $request->user()->business;

        if ($business->isWhatsAppConnectedViaEmbeddedSignup() && $business->whatsapp_business_account_id) {
            $token = $business->whatsappAccessToken();

            if ($token) {
                $this->whatsapp->unsubscribeAppFromWaba($business->whatsapp_business_account_id, $token);
            }
        }

        $business->update([
            'whatsapp_phone_number_id' => null,
            'whatsapp_business_account_id' => null,
            'whatsapp_access_token' => null,
            'whatsapp_connected_via' => null,
            'whatsapp_display_phone_number' => null,
            'whatsapp_connected_at' => null,
        ]);

        return redirect()->route('settings.edit')->with('status', 'WhatsApp disconnected.');
    }
}

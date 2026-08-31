# WhatsApp Embedded Signup — Meta Developer Dashboard Setup

This app now connects stores to WhatsApp via Meta's **Embedded Signup** flow (a
"Connect WhatsApp" button — see `resources/views/settings/edit.blade.php` and
`WhatsAppConnectController`) instead of asking each store owner to paste in
API credentials. That flow depends on **one Meta app that the platform owns**
— stores never create their own Meta app. Everything below is a one-time
setup performed by whoever operates this SaaS, in Meta's dashboards, not in
code.

## HTTPS is required — including for local testing

Confirmed empirically against this app: Meta's SDK refuses to open the
Embedded Signup dialog at all on a plain-HTTP page, **with no exception for
`localhost`** — it fails client-side (`FB.login() can no longer be called
from http pages`) before any request even reaches Meta. The settings page
now detects this and shows a clear warning instead of a dead button, but the
underlying limitation is Meta's, not something this codebase can work around.

Production should already be HTTPS-only, so this is a non-issue there. To
test the real flow locally (an XAMPP/`http://localhost` setup won't work),
put the app behind HTTPS first — e.g. `ngrok http 80` and use the `https://`
forwarding URL it gives you (also update `APP_URL` and Meta's App Domains to
match while testing that way), or a local dev tool that provisions a trusted
cert (Laravel Herd/Valet, `mkcert` + a virtual host, etc).

## 1. Create the platform's Meta app

1. Go to [developers.facebook.com](https://developers.facebook.com/) → **My Apps** → **Create App** → type **Business**.
2. In the app, **Add Product** → **WhatsApp** → **Set up**.
3. App Dashboard → **Settings → Basic**:
   - Copy the **App ID** → `WHATSAPP_APP_ID`
   - Copy the **App Secret** → `WHATSAPP_APP_SECRET`
   - Add the platform's domain under **App Domains**.

## 2. Configure Embedded Signup

App Dashboard → **WhatsApp → Configuration** (Meta sometimes surfaces this
under "Embedded Signup" in the WhatsApp product's Quickstart/Configuration
section — the exact label has moved around in Meta's UI, but it's always
under the WhatsApp product for the app you created in step 1):

1. Create a **Configuration**. This ties the signup flow to your app and the
   permissions it requests (`whatsapp_business_management`,
   `whatsapp_business_messaging`).
2. Copy the generated **Configuration ID** → `WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID`.

This is the ID the frontend passes to `FB.login({ config_id: ... })` — see
the script in `resources/views/settings/edit.blade.php`.

## 3. Configure the webhook

Same **WhatsApp → Configuration** page, "Webhook" section:

- **Callback URL**: `https://your-domain/webhooks/whatsapp`
- **Verify Token**: any string you choose — put the same value in
  `WHATSAPP_WEBHOOK_VERIFY_TOKEN`. Meta calls the verify handshake against
  `WhatsAppWebhookController::verify()` using exactly this value.
- **Webhook fields**: subscribe to at least `messages`. Add
  `message_template_status_update` and `account_update` if you want those
  events too — `WhatsAppWebhookController::handle()` already accepts and logs
  arbitrary `entry[].changes[]` payloads.

This webhook URL is shared by every connected store (Meta only supports one
callback URL per app) — that's why `WhatsAppConnectController::connect()`
calls `subscribeAppToWaba()` for each business individually: subscribing a
WABA to the app is what makes Meta actually deliver that business's events
to this one shared URL. The webhook handler then routes each incoming event
to the right store by matching `phone_number_id` (see
`WhatsAppWebhookController::processChange()`), which is why
`whatsapp_phone_number_id` has a database-level **unique** constraint — two
stores can never be routed to the same inbox.

## 4. Create the platform System User token

This is the token the app uses to actually send messages on behalf of
*every* connected store — see `Business::whatsappAccessToken()`. One token
covers every business once they've completed Embedded Signup, because that
flow shares the store's WABA with your Business, not with an individual
person.

1. [business.facebook.com](https://business.facebook.com/) → **Business Settings → Users → System Users**.
2. **Add** → create a System User with the **Admin** role.
3. Assign the System User to the app from step 1, and to the WhatsApp
   product, with `whatsapp_business_management` and
   `whatsapp_business_messaging` permissions.
4. **Generate New Token** → select the app, select those two permissions,
   choose **Never Expires** if offered (otherwise plan to rotate it before
   it lapses) → copy the token → `WHATSAPP_SYSTEM_USER_TOKEN`.

## 5. App Review (required to onboard real, non-test numbers)

Everything above works immediately in **Development Mode**, but only for
people added to the app as Admins/Developers/Testers, and only against
Meta's free test numbers. To let real store owners connect their real
WhatsApp numbers:

1. Complete **Business Verification** for the Business Manager that owns
   this app (Business Settings → Security Center).
2. Submit the app for **App Review**, requesting **Advanced Access** to
   `whatsapp_business_management` and `whatsapp_business_messaging`.
3. Meta will typically ask for a short screen-recording of the Embedded
   Signup flow working end-to-end — do this against a test WABA first.

Until this is approved, the "Connect WhatsApp" button will work for
Meta-registered testers on the app but not for the general public — this is
a Meta policy requirement, not something fixable in this codebase.

## 6. Phone verification (Registration + Settings)

Separate from Embedded Signup — this is the **platform's own** WhatsApp
number, used only to send a one-time code when someone registers or
changes their phone number in Settings. See `PhoneVerificationService`,
`WhatsAppCloudApiService::sendOtpTemplate()`, and
`resources/js/geo.js`/`components/geo-fields.blade.php` for the send/verify
UI. **Optional**: if left unconfigured, verification is simply not
enforced (`PhoneIsVerified` passes everything) — registrations and Settings
saves still work exactly as before, just without a verified phone number.

1. Get (or use an existing) real phone number for the platform itself and
   add it under App Dashboard → **WhatsApp → API Setup** → **Add phone
   number**. This is a genuine business phone number Meta will text/call to
   verify — it cannot be a tenant's number, and it's separate from any
   store's connected number.
2. Copy that number's **Phone Number ID** → `WHATSAPP_PLATFORM_PHONE_NUMBER_ID`.
3. App Dashboard → **WhatsApp → Message Templates** → **Create Template**:
   - Category: **Authentication**.
   - Add a body with a single variable, e.g. `*{{1}}* is your Zwenko
     verification code. It expires in 10 minutes.`
   - Submit for approval (Authentication templates are typically approved
     quickly, often within minutes, but can take longer).
   - Once approved, put the template's name → `WHATSAPP_OTP_TEMPLATE_NAME`
     (defaults to `phone_verification` if you name it that).
4. This reuses `WHATSAPP_SYSTEM_USER_TOKEN` from step 4 above — no separate
   token needed, as long as that System User also has messaging permission
   on the platform's own WABA (it does automatically, since it's the same
   Business that owns the number).

Sending to a number outside an open 24-hour conversation **must** use an
approved template — Meta rejects free-text messages otherwise. This is why
`sendOtpTemplate()` sends a `type: "template"` message, not `type: "text"`.

## Environment variables this depends on

All of these are read in `config/services.php` under `services.whatsapp.*`
and are documented inline in `.env.example`:

| Variable | From |
|---|---|
| `WHATSAPP_APP_ID` | Settings → Basic → App ID |
| `WHATSAPP_APP_SECRET` | Settings → Basic → App Secret |
| `WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID` | WhatsApp → Configuration → Embedded Signup Configuration ID |
| `WHATSAPP_WEBHOOK_VERIFY_TOKEN` | Any string you choose; must match what you enter in the webhook's Verify Token field |
| `WHATSAPP_SYSTEM_USER_TOKEN` | Business Settings → System Users → generated token |
| `WHATSAPP_PLATFORM_PHONE_NUMBER_ID` | Optional — WhatsApp → API Setup → your platform number's Phone Number ID (phone verification) |
| `WHATSAPP_OTP_TEMPLATE_NAME` | Optional — your approved Authentication template's name (phone verification) |
| `WHATSAPP_API_VERSION` | Optional, defaults to `v20.0` — bump as Meta deprecates old versions |

Nothing above is ever sent to the browser or shown to a store owner — the
frontend only ever receives Meta's own popup UI, an authorization `code`,
and the `waba_id`/`phone_number_id` Meta returns via `postMessage`, all of
which are single-use/non-sensitive by the time they reach our backend.

## What was NOT changed

Stores that already have their own manually-entered WhatsApp Cloud API
credentials (the pre-existing flow) keep working exactly as before — that
option still exists, collapsed under **Advanced: connect WhatsApp manually**
on the settings page, for anyone who already has their own Meta Cloud API
setup. `Business::whatsappAccessToken()` prefers a business's own stored
token when present and only falls back to the shared platform token
otherwise, so the two connection methods never conflict.

<x-legal-layout title="Privacy Policy">

    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        {{ __('This is a working draft, not a substitute for legal advice. Have a lawyer review it — including the [OPERATOR LEGAL NAME] placeholder below — before treating it as binding.') }}
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('1. Who this policy covers') }}</h2>
        <p class="mb-3">{{ __('This policy explains how Zwenko, operated by [OPERATOR LEGAL NAME], collects and uses personal data through the website, dashboard, and storefronts (the "Service"). It covers two kinds of people differently: sellers who run a store on Zwenko, and the customers who buy from those stores.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('2. What we collect') }}</h2>
        <p class="mb-3">{{ __('From a seller: your name, email, phone number, and business details (name, address, bank/payout details) when you register and use Settings. From a customer buying through a Zwenko storefront: whatever the seller\'s checkout asks for — typically name, phone number, and delivery address — entered directly by the customer or on their behalf. We also automatically log basic technical data (IP address, browser, timestamps) for security and troubleshooting.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('3. Who\'s responsible for customer data') }}</h2>
        <p class="mb-3">{{ __('When a customer buys from a Zwenko storefront, the seller is the one who decides what happens with that customer\'s information — Zwenko is the infrastructure the seller uses to store and process it, similar to how a landlord isn\'t responsible for what a tenant keeps inside their shop. If you\'re a customer with a question about how your data was used by a specific seller, that seller is who to ask; if you can\'t reach them, contact us and we\'ll help where we can.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('4. How we use it') }}</h2>
        <ul class="list-disc pl-5 mb-3 space-y-1.5">
            <li>{{ __('To run the Service: create your store, process orders, send order and WhatsApp notifications, calculate commission, and route payments.') }}</li>
            <li>{{ __('To verify a phone number belongs to the person registering or updating it, via a WhatsApp code.') }}</li>
            <li>{{ __('To keep the Service secure — detecting fraud, abuse, or unauthorized access.') }}</li>
            <li>{{ __('To send you service-related emails (order alerts, low stock, refunds, password resets); you can turn off the optional ones in Settings.') }}</li>
        </ul>
        <p class="mb-3">{{ __('We don\'t sell personal data, and we don\'t use it for third-party advertising.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('5. Who we share it with') }}</h2>
        <p class="mb-3">{{ __('We share what\'s necessary to run the Service, and nothing beyond that:') }}</p>
        <ul class="list-disc pl-5 mb-3 space-y-1.5">
            <li>{{ __('WhatsApp (Meta) — to deliver order messages and verification codes when a store uses WhatsApp ordering.') }}</li>
            <li>{{ __('Paystack — to process payments and payouts. Card and bank details are entered directly on Paystack\'s own page and never reach Zwenko\'s servers.') }}</li>
            <li>{{ __('Legal or regulatory bodies, only when required by law.') }}</li>
        </ul>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('6. Security') }}</h2>
        <p class="mb-3">{{ __('Sensitive credentials (like a store\'s WhatsApp API access token) are encrypted at rest. Access to seller and customer data within Zwenko is limited to what a given account\'s role actually needs — a staff member only sees what their permissions allow. No system is perfectly secure, and we can\'t guarantee absolute security, but we take it seriously and will tell you if something goes wrong that affects your data.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('7. How long we keep it') }}</h2>
        <p class="mb-3">{{ __('We keep your data while your account is active. If you\'re the sole person on a business and delete your account, the business is closed rather than erased — its historical orders and payments are kept for accounting and legal purposes, but the storefront and dashboard become permanently inaccessible. If you\'d like your personal data removed beyond what we\'re required to keep for those purposes, contact us and we\'ll act on it.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('8. Your choices') }}</h2>
        <p class="mb-3">{{ __('You can review and correct your own account information at any time from Settings or Profile. You can turn off optional email notifications in Settings. To request a copy of your data or ask us to delete it, email us — we\'ll respond and explain what we can and can\'t remove (for example, financial records we\'re legally required to retain).') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('9. Cookies') }}</h2>
        <p class="mb-3">{{ __('We use one cookie to keep you signed in and to protect forms from cross-site request forgery — nothing beyond what\'s needed to run the Service. We don\'t run advertising or analytics trackers on Zwenko.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('10. Children') }}</h2>
        <p class="mb-3">{{ __('Zwenko is a business tool and isn\'t directed at children. You must be old enough to legally enter into an agreement in your country to create a seller account.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('11. Changes to this policy') }}</h2>
        <p class="mb-3">{{ __('We may update this policy as the Service evolves. We\'ll post the updated version here with a new "Last updated" date, and try to notify account Owners directly for material changes.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('12. Contact') }}</h2>
        <p class="mb-3">{{ __('Questions about this policy, or a request about your data? Reach us at :email.', ['email' => 'support@zwenko.com']) }}</p>
    </div>

</x-legal-layout>

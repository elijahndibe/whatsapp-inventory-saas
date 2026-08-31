<x-legal-layout title="Terms of Service">

    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        {{ __('This is a working draft, not a substitute for legal advice. Have a lawyer review it — including the [OPERATOR LEGAL NAME] placeholder below — before treating it as binding.') }}
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('1. Who these Terms are with') }}</h2>
        <p class="mb-3">{{ __('Zwenko ("Zwenko", "we", "us") is operated by [OPERATOR LEGAL NAME]. These Terms govern your access to and use of Zwenko\'s website, dashboard, storefronts, and related services (the "Service"). By creating a Zwenko account you agree to these Terms; if you\'re creating an account on behalf of a business, you\'re confirming you have the authority to bind that business to them.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('2. What Zwenko is') }}</h2>
        <p class="mb-3">{{ __('Zwenko lets a business create an online storefront, manage products and inventory, take orders through that storefront or directly via WhatsApp, track customers, and accept online payments. We are the software that runs the store — we are not a party to the sale between you and your customers, and we don\'t take title to, inspect, or guarantee anything you sell.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('3. Your account') }}</h2>
        <p class="mb-3">{{ __('You\'re responsible for the accuracy of the information you give us about your business, and for anything that happens under your account — including actions taken by staff members you invite. Keep your password confidential and tell us immediately if you suspect unauthorized access.') }}</p>
        <p class="mb-3">{{ __('The person who registers a business becomes its Owner. An Owner can invite staff, assign roles and permissions, and transfer ownership to another active staff member at any time from the Staff page. If you\'re the sole person on a business and delete your account, the business is closed rather than deleted — its historical orders and payments are kept, but no one can sign in to it again. If other staff remain, you\'ll need to transfer ownership or remove them first.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('4. Fees and payments') }}</h2>
        <p class="mb-3">{{ __('Creating a store and using its core features — storefront, inventory, orders, and WhatsApp ordering — is free. Zwenko earns a commission on successful payments taken through the Service; the current rate for your account is shown in your dashboard and may be adjusted from time to time (we\'ll always show you the rate that applied to a given transaction in your Payments history, and a rate change is never applied retroactively to a transaction that already happened). Optional paid plans, if and when we offer them, will always state their price clearly before you\'re charged.') }}</p>
        <p class="mb-3">{{ __('Card and bank payments from your customers are processed by Paystack, not by us — your customer\'s payment details are entered directly on Paystack\'s own payment page and never pass through Zwenko\'s servers. Payouts to your own bank account depend on Paystack\'s settlement schedule and the accuracy of the bank details you provide us; we\'re not responsible for delays or errors caused by incorrect account details or by Paystack\'s own processes.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('5. What you can sell and how you can use Zwenko') }}</h2>
        <p class="mb-3">{{ __('You agree not to use Zwenko to list or sell anything illegal, stolen, counterfeit, or that infringes someone else\'s intellectual property; to defraud a customer or another business; to send unsolicited bulk messages; or to attempt to disrupt, probe, or gain unauthorized access to the Service. We may suspend or close an account that violates this section, with or without notice depending on severity.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('6. Your content') }}</h2>
        <p class="mb-3">{{ __('You keep ownership of the product listings, photos, descriptions, and other content you upload. By uploading it, you give Zwenko permission to display, store, and transmit it as needed to run your storefront and process your orders — nothing more.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('7. Third-party services') }}</h2>
        <p class="mb-3">{{ __('Zwenko integrates with WhatsApp (via Meta) and Paystack to provide messaging and payment features. Your use of those integrations is also subject to Meta\'s and Paystack\'s own terms, and we don\'t control their availability, pricing, or policies. If either service changes or becomes unavailable, the related Zwenko feature may be affected.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('8. Disclaimers and limitation of liability') }}</h2>
        <p class="mb-3">{{ __('The Service is provided "as is." We work to keep it reliable and secure, but we don\'t guarantee it will be uninterrupted or error-free. To the fullest extent the law allows, Zwenko isn\'t liable for indirect, incidental, or consequential damages (like lost profits or lost sales) arising from your use of the Service, and our total liability for any claim is limited to the commission we earned from your account in the three months before the claim arose.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('9. Changes to these Terms') }}</h2>
        <p class="mb-3">{{ __('We may update these Terms as the Service evolves. We\'ll post the updated version here with a new "Last updated" date; continuing to use Zwenko after a change takes effect means you accept it. For material changes, we\'ll also try to notify account Owners directly.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('10. Governing law') }}</h2>
        <p class="mb-3">{{ __('These Terms are governed by the laws of the Federal Republic of Nigeria, without regard to conflict-of-law principles.') }}</p>
    </div>

    <div>
        <h2 class="text-xl font-semibold text-ink mb-3">{{ __('11. Contact') }}</h2>
        <p class="mb-3">{{ __('Questions about these Terms? Reach us at :email.', ['email' => 'support@zwenko.com']) }}</p>
    </div>

</x-legal-layout>

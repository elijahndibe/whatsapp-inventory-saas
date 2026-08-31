/**
 * Alpine component powering the Country / Currency / Timezone / Phone
 * dial-code pickers on Registration and Settings. Auto-detects from the
 * browser (no network calls, no IP-geolocation service — just
 * Intl.DateTimeFormat, which every modern browser has) and always leaves
 * everything as an editable <select>, never a silent, unconfirmable guess.
 *
 * The country list itself lives in config/countries.php (PHP is the
 * single source of truth) and is injected into this component via
 * @json(...) from the Blade view — see auth/register.blade.php and
 * settings/edit.blade.php for the x-data="geoPicker(...)" call sites.
 */
export default function geoPicker({
    countries,
    country = '',
    currency = '',
    timezone = '',
    // A single raw phone string, however it's stored/typed — e.g. an
    // existing business's saved "+2348012345678", or a re-submitted
    // old('phone') after a validation error. Split into a dial code and
    // local number by matching the longest known dial code prefix, since
    // dial codes vary from 1 to 4 digits and there's no delimiter stored.
    phone = '',
    // Full URLs (route('phone-verification.send') etc.), not hardcoded
    // paths — this app doesn't always run at the domain root (e.g. a
    // /public subdirectory under XAMPP), so a root-relative '/phone-...'
    // path would 404 there.
    sendCodeUrl = '/phone-verification/send',
    verifyCodeUrl = '/phone-verification/verify',
}) {
    return {
        countries,
        country,
        currency,
        timezone,
        dialCode: '',
        phoneNumber: '',
        timezoneOptions: [],
        autoDetected: false,
        sendCodeUrl,
        verifyCodeUrl,

        // Phone verification (WhatsApp one-time code) — see
        // PhoneVerificationController/PhoneVerificationService.
        originalPhone: phone,
        verified: false,
        codeSent: false,
        codeInput: '',
        sending: false,
        verifying: false,
        feedback: '',
        feedbackIsError: false,
        resendIn: 0,

        init() {
            this.timezoneOptions = this.buildTimezoneOptions();
            this.splitPhone(phone);

            // A number already on file (Settings loading an existing
            // business) is trusted as-is — only a number that's actually
            // changed from what was saved needs a fresh code. Registration
            // starts with an empty originalPhone, so anything typed there
            // counts as "changed" and needs verifying.
            this.verified = phone !== '' && phone === this.originalPhone;

            this.$watch('fullPhone', (value) => {
                // Matches the original value again (e.g. the user typed
                // something else and backspaced back) — still trusted,
                // exactly as it was on load. Anything else needs a fresh
                // code.
                this.verified = value !== '' && value === this.originalPhone;
                this.codeSent = false;
                this.codeInput = '';
                this.feedback = '';
            });

            // Never override a value that's already set — that's either a
            // value the user already typed this submission, or (on
            // Settings) the business's existing saved setting. Detection
            // only ever fills in genuinely blank fields. A phone number
            // that was already split into a dial code counts as "already
            // set" too — detect() would otherwise happily overwrite the
            // dial code with a browser-detected one that doesn't match the
            // number actually on file.
            if (!this.country && !this.currency && !this.timezone && !this.dialCode) {
                this.detect();
            }
        },

        splitPhone(raw) {
            if (!raw) {
                return;
            }
            const digits = raw.replace(/[^\d+]/g, '');

            // Longest dial code first — "+1" is a prefix of "+1876" etc.,
            // so a short-to-long search could match the wrong country.
            const byDialLength = [...this.countries].sort((a, b) => b.dial.length - a.dial.length);
            const match = byDialLength.find((c) => digits.startsWith(c.dial));

            if (match) {
                this.dialCode = match.dial;
                this.phoneNumber = digits.slice(match.dial.length);
            } else {
                // Unrecognised prefix (or no + at all) — keep the number
                // visible rather than silently dropping it.
                this.phoneNumber = digits.replace(/^\+/, '');
            }
        },

        // The full IANA list from the browser itself when available (every
        // current browser supports this) — far more complete than
        // anything we'd hand-maintain. Falls back to just the zones our
        // curated country list knows about for the rare browser without it.
        buildTimezoneOptions() {
            try {
                if (typeof Intl.supportedValuesOf === 'function') {
                    return Intl.supportedValuesOf('timeZone');
                }
            } catch (e) {
                // fall through to the fallback below
            }

            const set = new Set();
            this.countries.forEach((c) => {
                set.add(c.tz);
                (c.tzs || []).forEach((tz) => set.add(tz));
            });
            return [...set].sort();
        },

        // Reverse-maps the browser's own timezone to one of our countries
        // and prefills country/currency/dial-code/timezone from that
        // match. If the browser's zone isn't one we recognise, we still
        // honestly set the timezone we DID detect rather than guessing a
        // country for it.
        detect() {
            let browserTz;
            try {
                browserTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            } catch (e) {
                return;
            }
            if (!browserTz) {
                return;
            }

            const match = this.countries.find(
                (c) => c.tz === browserTz || (c.tzs || []).includes(browserTz)
            );

            if (match) {
                this.country = match.name;
                this.currency = match.currency;
                this.dialCode = match.dial;
                this.timezone = browserTz;
                this.autoDetected = true;
            } else {
                this.timezone = browserTz;
            }
        },

        // Called when the user picks a country by hand — refreshes
        // currency/dial-code/timezone to that country's defaults. Still
        // just a prefill: the currency and timezone selects are
        // independent fields the user can change afterward.
        countryChanged() {
            const match = this.countries.find((c) => c.name === this.country);
            if (!match) {
                return;
            }
            this.currency = match.currency;
            this.dialCode = match.dial;
            this.timezone = match.tz;
            this.autoDetected = false;
        },

        // A leading 0 on the local part (e.g. "08012345678") is the normal
        // way people write a number domestically — drop exactly one so it
        // doesn't end up double-zeroed once the dial code is prepended.
        get fullPhone() {
            if (!this.phoneNumber) {
                return '';
            }
            return this.dialCode + this.phoneNumber.replace(/^0/, '');
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        async sendVerificationCode() {
            if (!this.fullPhone || this.sending || this.resendIn > 0) {
                return;
            }
            this.sending = true;
            this.feedback = '';

            try {
                const response = await fetch(this.sendCodeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ phone: this.fullPhone }),
                });
                const data = await response.json();

                this.feedback = data.message;
                this.feedbackIsError = !response.ok;
                this.codeSent = response.ok;

                if (response.ok) {
                    this.startResendCooldown();
                }
            } catch (e) {
                this.feedback = 'Something went wrong sending the code. Please try again.';
                this.feedbackIsError = true;
            } finally {
                this.sending = false;
            }
        },

        async submitVerificationCode() {
            if (!this.codeInput || this.verifying) {
                return;
            }
            this.verifying = true;

            try {
                const response = await fetch(this.verifyCodeUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken(), 'Accept': 'application/json' },
                    body: JSON.stringify({ phone: this.fullPhone, code: this.codeInput }),
                });
                const data = await response.json();

                this.feedback = data.message;
                this.feedbackIsError = !response.ok;
                this.verified = response.ok;
            } catch (e) {
                this.feedback = 'Something went wrong verifying the code. Please try again.';
                this.feedbackIsError = true;
            } finally {
                this.verifying = false;
            }
        },

        startResendCooldown() {
            this.resendIn = 60;
            const interval = setInterval(() => {
                this.resendIn -= 1;
                if (this.resendIn <= 0) {
                    clearInterval(interval);
                }
            }, 1000);
        },
    };
}

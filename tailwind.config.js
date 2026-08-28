import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Zwenko brand purple — strategic accent, not a wash-everything
                // colour. Named `brand` (not `primary`) so it never collides
                // with a component's own `primary`-prop conventions.
                brand: {
                    50: '#f5f3ff',
                    100: '#ede9fe',
                    200: '#ddd6fe',
                    300: '#c4b5fd',
                    400: '#a78bfa',
                    500: '#8b5cf6',
                    600: '#7c3aed',
                    700: '#6d28d9', // primary brand tone from the approved mockup
                    800: '#5b21b6',
                    900: '#4c1d95',
                    950: '#2e1065',
                },
                // WhatsApp accent — used only for WhatsApp-specific status/
                // actions, never as the app's primary brand colour.
                whatsapp: {
                    DEFAULT: '#25D366',
                    dark: '#128C7E',
                },
                ink: '#111827',
                muted: '#6B7280',
                surface: '#F8FAFC',
                border: '#E5E7EB',
                // `strong` is a darker shade of the same hue for use as small
                // TEXT on a light/tinted surface — the spec's DEFAULT tones
                // read at roughly WhatsApp-green-on-white contrast when used
                // as body text (e.g. warning #F59E0B on white is ~2.1:1,
                // success #16A34A on its own bg-success-bg tint is ~3.2:1),
                // both below the 4.5:1 WCAG AA text minimum. DEFAULT stays
                // exactly as specified for icons, dots, and solid buttons
                // where only the 3:1 non-text threshold applies.
                success: { DEFAULT: '#16A34A', bg: '#F0FDF4', strong: '#15803D' },
                warning: { DEFAULT: '#F59E0B', bg: '#FFFBEB', strong: '#B45309' },
                danger: { DEFAULT: '#DC2626', bg: '#FEF2F2', strong: '#B91C1C' },
                info: { DEFAULT: '#2563EB', bg: '#EFF6FF' },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(0 0 0 / 0.04), 0 1px 3px 0 rgb(0 0 0 / 0.06)',
            },
        },
    },

    plugins: [forms],
};

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy: {
                    DEFAULT: '#172B4D',
                    50: '#F4F6F9',
                    100: '#E6EAF0',
                    200: '#C5CEDD',
                    300: '#94A6C1',
                    400: '#5F79A2',
                    500: '#3D5C87',
                    600: '#2E476C',
                    700: '#243B5A', // Secondary Navy
                    800: '#172B4D', // Primary Navy
                    900: '#0E1D35',
                    950: '#081223',
                },
                corp: {
                    primary: '#172B4D', // Corporate Navy
                    secondary: '#243B5A', // Secondary Navy
                    accent: '#DC2626', // Kencana Corporate Red
                    'accent-hover': '#B91C1C',
                    'accent-soft': '#FEF2F2',
                    red: '#DC2626',
                    'red-hover': '#B91C1C',
                    'red-soft': '#FEF2F2',
                    bg: '#F5F6F8', // Corporate Gray Background
                    surface: '#FFFFFF',
                    border: '#E5E7EB', // Neutral Gray Border
                    'border-light': '#F0F2F5',
                    text: '#1F2937',
                    muted: '#6B7280',
                    subtle: '#9CA3AF',
                },
                // Enterprise Soft Status Colors
                status: {
                    'success-bg': '#ECFDF5',
                    'success-text': '#065F46',
                    'success-border': '#A7F3D0',
                    'warning-bg': '#FFFBEB',
                    'warning-text': '#92400E',
                    'warning-border': '#FDE68A',
                    'error-bg': '#FEF2F2',
                    'error-text': '#991B1B',
                    'error-border': '#FECACA',
                    'neutral-bg': '#F3F4F6',
                    'neutral-text': '#374151',
                    'neutral-border': '#E5E7EB',
                    'info-bg': '#EFF6FF',
                    'info-text': '#1E40AF',
                    'info-border': '#BFDBFE',
                },
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03)',
                'panel': '0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03)',
                'subtle': '0 1px 2px 0 rgba(0, 0, 0, 0.03)',
            },
            borderRadius: {
                'sm': '4px',
                DEFAULT: '6px',
                'md': '8px',
                'lg': '10px',
                'xl': '12px',
            },
        },
    },

    plugins: [forms],
};

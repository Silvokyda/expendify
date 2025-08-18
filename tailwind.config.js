import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    bg: '#f2ece3',        // background (paper)
                    primary: '#0f5334',   // deep green (logo)
                    primaryDark: '#094c2c',
                    accent: '#eca425',    // coin gold
                    text: '#212529',
                    gray: '#6C757D',
                },
            },
            boxShadow: {
                card: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
            },
            borderColor: {
                DEFAULT: 'rgb(0 0 0 / 0.05)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [forms],
}
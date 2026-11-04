import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/*
 * Identitas visual "toko material": oranye safety (helm proyek / bata) + abu arang.
 * Palet indigo & violet dipetakan ulang ke oranye, sehingga semua halaman lama ikut
 * berganti warna tanpa perlu diubah satu per satu.
 */
const brand = {
    50:  '#fff7ef',
    100: '#ffecd9',
    200: '#fdd5b0',
    300: '#fbb57c',
    400: '#f78d45',
    500: '#ef6c1e',
    600: '#d65a10',
    700: '#b1470e',
    800: '#8d3912',
    900: '#733113',
    950: '#3e1607',
};

const charcoal = {
    50:  '#f6f7f9',
    100: '#eceef2',
    200: '#d5dae1',
    300: '#b0b9c6',
    400: '#8592a5',
    500: '#66748a',
    600: '#515c71',
    700: '#424b5c',
    800: '#2b323e',
    900: '#1f252f',
    950: '#151a21',
};

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
                brand,
                charcoal,
                indigo: brand,
                violet: brand,
            },
        },
    },

    plugins: [forms],
};

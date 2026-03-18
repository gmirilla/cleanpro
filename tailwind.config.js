import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
        safelist: [
        { pattern: /^bg-(yellow|blue|indigo|purple|green|red|gray|orange)-(50|100|200|700)$/ },
        { pattern: /^text-(yellow|blue|indigo|purple|green|red|gray|orange)-(600|700|800)$/ },
        { pattern: /^border-(yellow|blue|indigo|purple|green|red|gray|orange)-(200|400|500)$/ },
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};

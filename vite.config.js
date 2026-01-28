import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/booking/staycation.js'],
            refresh: true,
        }),
        tailwindcss({
            content: [
                "./resources/**/*.blade.php",
                "./resources/**/*.js",
                "./resources/**/*.vue",
            ],
            theme: {
                extend: {
                    colors: {
                        'brand': '#a75e3e',
                        'brand-dark': '#8a4825',
                        'brand-light': '#593e2d',
                    },
                },
            },
        }),
    ],
});

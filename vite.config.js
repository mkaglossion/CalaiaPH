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
    build: {
        modulePreload: {
            polyfill: false,
        },
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
            onwarn(warning, warn) {
                if (warning.code === 'UNRESOLVED_ENTRY' || 
                    (warning.message && warning.message.includes('didn\'t resolve at build time'))) {
                    return;
                }
                warn(warning);
            },
        },
    },
    customLogger: {
        hasWarned: false,
        info(msg) {
            if (!msg.includes('didn\'t resolve at build time')) {
                console.log(msg);
            }
        },
        warn(msg) {
            if (!msg.includes('didn\'t resolve at build time')) {
                console.warn(msg);
            }
        },
        warnOnce(msg) {
            if (!msg.includes('didn\'t resolve at build time')) {
                console.warn(msg);
            }
        },
        error(msg) {
            console.error(msg);
        },
    },
});

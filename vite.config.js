import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: [
                'resources/views/**',
                'routes/**',
            ],
        }),
    ],
    server: {
        watch: {
            // Jangan watch folder-folder ini — berubah terus saat runtime
            ignored: [
                '**/storage/**',
                '**/vendor/**',
                '**/node_modules/**',
                '**/.git/**',
            ],
        },
    },
});

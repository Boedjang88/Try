import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/modules/cbt-engine.js',
                'resources/js/modules/krs-war-cart.js',
                'resources/js/modules/qr-attendance.js',
                'resources/js/modules/grade-spreadsheet.js',
                'resources/js/modules/timetable-builder.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        host: '127.0.0.1',
        port: 5173,
        cors: true,
    },
    build: {
        outDir: 'public/build',
        manifest: true,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/alpinejs')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});

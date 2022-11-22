import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import path from 'path';
import { address } from 'ip';

export default defineConfig({
    server: {
        https: process.env.APP_ENV == 'production' ? true : false,
        host: address()
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            ssr: 'resources/js/ssr.jsx',
            refresh: true,
        }),
        react(),
    ],
    ssr: {
        noExternal: ['@inertiajs/server'],
    },
    resolve: {
        alias: {
            "@": path.join(__dirname, "/resources/js/"),
        }
    }
});

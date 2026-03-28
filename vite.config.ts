import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    envPrefix: ['VITE_', 'PUBLIC_'],
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            buildDirectory: 'build',
        }),
        tailwindcss(),
        react(),
    ],
    build: {
        assetsInlineLimit: 4096,
        sourcemap: false,
        minify: 'esbuild',
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['react', 'react-dom'],
                    inertia: ['@inertiajs/react'],
                    ui: ['@radix-ui', 'lucide-react', 'class-variance-authority'],
                },
            },
        },
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production'),
    },
    server: {
        host: '0.0.0.0',
        port: parseInt(process.env.VITE_PORT ?? '5173'),
        hmr: {
            host: 'localhost',
        },
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
});

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
    
    server: {
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
});

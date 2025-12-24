import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { laravelDataTypes } from './laravelDataTypes';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
        laravelDataTypes(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
    resolve: {
        alias: {},
    },
    server: {
        /** Uncomment the following lines if you want to allow access from network */
        // host: '0.0.0.0', // Allow access from network
        // hmr: {
        //     host: '10.54.2.249', // Your local IP
        // },
        watch: {
            ignored: ['**/.github/**', '**/.vscode/**', '**/tests/**', '**/database/**', '**/pint.json'],
        },
    },
});

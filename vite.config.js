import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // Bootstrap 5.3 todavía usa @import y funciones viejas de Sass:
                // silenciamos esos avisos para que la consola quede limpia.
                quietDeps: true,
                silenceDeprecations: ['import', 'mixed-decls', 'color-functions', 'global-builtin'],
            },
        },
    },
});

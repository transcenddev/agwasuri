import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    // Exclude vendor packages that ship their own build tools or PostCSS
    // helpers from Vite's dependency optimization / CommonJS transform.
    // This prevents warnings like: "A PostCSS plugin did not pass the `from` option to `postcss.parse`."
    optimizeDeps: {
        // Exclude vendor paths (try both forward and backslash styles)
        exclude: [
            "vendor/asantibanez/livewire-charts",
            "asantibanez/livewire-charts",
        ],
    },
    build: {
        commonjsOptions: {
            // Exclude vendor livewire-charts package directory from CommonJS transformation
            exclude: [
                /vendor[\\/]asantibanez[\\/]livewire-charts/,
                /vendor[\\/]asantibanez[\\/]livewire-charts\/.*/,
            ],
        },
    },
});

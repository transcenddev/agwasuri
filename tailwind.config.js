import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    // Use class strategy so toggling the `dark` class on <html> works at runtime
    darkMode: "class",
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        // include JS and CSS so Tailwind's JIT picks up classes used in scripts and stylesheet files
        "./resources/js/**/*.js",
        "./resources/css/**/*.css",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                cvsu: {
                    DEFAULT: "var(--cvsu-500)",
                    50: "var(--cvsu-50)",
                    100: "var(--cvsu-100)",
                    200: "var(--cvsu-200)",
                    300: "var(--cvsu-300)",
                    400: "var(--cvsu-400)",
                    500: "var(--cvsu-500)",
                    600: "var(--cvsu-600)",
                    700: "var(--cvsu-700)",
                    800: "var(--cvsu-800)",
                    900: "var(--cvsu-900)",
                },
            },
        },
    },

    plugins: [forms],
};

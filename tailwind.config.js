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
                // Surface and semantic tokens for consistent containers/cards
                surface: {
                    DEFAULT: "var(--surface, #ffffff)",
                    muted: "#F9FAFB",
                },
                brand: {
                    50: '#F3F8FF',
                    500: '#2563EB',
                },
            },
            borderRadius: {
                'lg-2': '0.75rem',
            },
            boxShadow: {
                card: '0 6px 18px rgba(15, 23, 42, 0.06)',
            },
            maxWidth: {
                'content-xl': '1100px',
            },
        },
    },

    plugins: [forms],
};

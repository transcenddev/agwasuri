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
        "./resources/views/livewire/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                cvsu: {
                    // Use CSS variables with sensible fallbacks so utilities work
                    // even if variables are not present at runtime.
                    DEFAULT: "var(--cvsu-500, #1B651B)",
                    50: "var(--cvsu-50, #f4fbf4)",
                    100: "var(--cvsu-100, #e6f6e6)",
                    200: "var(--cvsu-200, #bfe9bf)",
                    300: "var(--cvsu-300, #72b372)",
                    400: "var(--cvsu-400, #2a7a2a)",
                    500: "var(--cvsu-500, #1B651B)",
                    600: "var(--cvsu-600, #155414)",
                    700: "var(--cvsu-700, #103f10)",
                    800: "var(--cvsu-800, #0b2b0b)",
                    900: "var(--cvsu-900, #061a06)",
                },
                // Surface and semantic tokens for consistent containers/cards
                surface: {
                    DEFAULT: "var(--surface, #ffffff)",
                    muted: "#F9FAFB",
                },
                brand: {
                    50: "var(--cvsu-50)",
                    500: "var(--cvsu-500)",
                },
            },
            borderRadius: {
                "lg-2": "0.75rem",
            },
            boxShadow: {
                card: "0 6px 18px rgba(15, 23, 42, 0.06)",
            },
            maxWidth: {
                "content-xl": "1100px",
            },
        },
    },

    plugins: [forms],
};

import "./bootstrap";
// Import theme helper and expose toggleTheme globally so inline buttons can call it
import { initTheme, toggleTheme } from "./theme";
initTheme();
// Expose toggleTheme globally so non-module inline buttons can call it
window.toggleTheme = toggleTheme;

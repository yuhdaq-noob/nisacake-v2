import "./bootstrap";

// Impor utilitas bersama (auth, formatting, validasi)
import "./utils.js";
// Impor helper API agar utilitas login/logout ikut ter-bundle.
import "./api.js";
// Skrip per-halaman dimuat via Blade @vite agar tidak double-load
import { initUiLayer } from "./ui.js";

document.addEventListener("DOMContentLoaded", () => {
    initUiLayer();
});

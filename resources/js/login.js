/**
 * Modul halaman Login
 * Menangani submit form autentikasi dan proses login.
 */

import "./bootstrap";
import "./api.js";

document.addEventListener("DOMContentLoaded", () => {
    const passEl = document.getElementById("passwordInput");
    if (passEl) passEl.focus();

    // Toggle visibilitas password
    const toggleBtn = document.getElementById("togglePassword");
    const eyeIcon = document.getElementById("eyeIcon");
    if (toggleBtn && passEl && eyeIcon) {
        toggleBtn.addEventListener("click", function (e) {
            e.preventDefault();
            const isHidden = passEl.type === "password";
            passEl.type = isHidden ? "text" : "password";
            // Ganti ikon
            eyeIcon.innerHTML = isHidden
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.292m3.087-2.7A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.965 9.965 0 01-4.043 5.032M15 12a3 3 0 01-3 3m0 0a3 3 0 01-3-3m3 3l6 6m-6-6l-6 6"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            toggleBtn.setAttribute(
                "aria-label",
                isHidden ? "Sembunyikan PIN" : "Tampilkan PIN",
            );
        });
    }

    // Transisi halus untuk overlay loading
    const overlay = document.getElementById("loadingOverlay");
    if (overlay) {
        overlay.style.transition = "opacity 0.5s ease, visibility 0.5s";
    }
});

const loginForm = document.getElementById("loginForm");
if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        const btn = document.getElementById("btnLogin");
        const passInput = document.getElementById("passwordInput");
        const errorMsg = document.getElementById("errorMsg");
        const overlay = document.getElementById("loadingOverlay");

        passInput.classList.remove("error");
        if (errorMsg) errorMsg.style.opacity = "0";
        if (btn) {
            btn.innerText = "Verifying...";
            btn.disabled = true;
        }

        try {
            const formData = new FormData(this);
            const postUrl =
                document.body?.dataset?.loginPost ||
                window.loginPostRoute ||
                "/login";
            const response = await fetch(postUrl, {
                method: "POST",
                body: formData,
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const result = await response.json();

            if (response.ok) {
                const username = formData.get("username");
                const password = formData.get("password");
                if (window.apiLogin && username && password) {
                    try {
                        await window.apiLogin(
                            String(username),
                            String(password),
                        );
                    } catch (apiError) {
                        console.warn("API token fetch failed:", apiError);
                    }
                }
                if (overlay) {
                    overlay.style.opacity = "1";
                    overlay.style.pointerEvents = "all";
                }
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 2000); // Increased delay for smoother transition
            } else {
                throw new Error(result.message || "Login failed");
            }
        } catch (error) {
            if (btn) {
                btn.innerText = "Login";
                btn.disabled = false;
            }
            if (passInput) {
                passInput.classList.add("error");
                passInput.value = "";
                passInput.focus();
            }
            if (errorMsg) {
                errorMsg.innerText = error.message;
                errorMsg.style.opacity = "1";
            }
        }
    });
}

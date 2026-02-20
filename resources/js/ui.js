/**
 * Layer UI — mengelola drawer, modal, dan notifikasi.
 * Konfigurasi menggunakan atribut data-* pada elemen HTML.
 */

/**
 * Toggle drawer: buka/tutup
 * @param {boolean|undefined} open - paksa buka (true) atau tutup (false), toggle jika undefined
 */
function toggleDrawer(open) {
    const drawer = document.querySelector("[data-drawer]");
    const backdrop = document.querySelector("[data-drawer-backdrop]");
    if (!drawer || !backdrop) return;

    const isOpen = open ?? drawer.classList.contains("translate-x-0");
    const nextState = open !== undefined ? open : !isOpen;

    // Perbarui posisi drawer
    drawer.classList.toggle("-translate-x-full", !nextState);
    drawer.classList.toggle("translate-x-0", nextState);

    // Perbarui visibilitas backdrop
    backdrop.classList.toggle("hidden", !nextState);

    // Perbarui atribut ARIA untuk aksesibilitas
    drawer.setAttribute("aria-hidden", !nextState);
    backdrop.setAttribute("aria-hidden", !nextState);

    // Cegah body menggulir saat drawer terbuka
    if (nextState) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "";
    }
}

/**
 * Pasang event handler untuk drawer
 */
function bindDrawer() {
    const toggleButtons = document.querySelectorAll("[data-drawer-toggle]");
    const closeButtons = document.querySelectorAll("[data-drawer-close]");
    const backdrop = document.querySelector("[data-drawer-backdrop]");

    // Pasang event pada tombol toggle
    toggleButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleDrawer();
        });
    });

    // Pasang event pada tombol tutup
    closeButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleDrawer(false);
        });
    });

    // Tutup drawer saat backdrop diklik
    backdrop?.addEventListener("click", () => toggleDrawer(false));

    // Tutup drawer saat tombol Escape ditekan
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            const drawer = document.querySelector("[data-drawer]");
            if (drawer && drawer.classList.contains("translate-x-0")) {
                toggleDrawer(false);
            }
        }
    });
}

/**
 * Buka modal dengan animasi
 * @param {string} id - ID elemen modal
 */
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const backdrop = modal.querySelector(".modal-backdrop");
    const panel = modal.querySelector(".modal-panel");

    // Tampilkan modal untuk animasi
    modal.classList.remove("hidden");

    requestAnimationFrame(() => {
        modal.classList.remove("opacity-0", "pointer-events-none");
        modal.classList.add("pointer-events-auto");

        if (backdrop) {
            backdrop.classList.remove("opacity-0");
        }

        if (panel) {
            panel.classList.remove("opacity-0", "translate-y-4", "scale-95");
            panel.classList.add("translate-y-0", "scale-100");
        }
    });

    // Cegah body menggulir
    document.body.style.overflow = "hidden";
}

/**
 * Tutup modal dengan animasi
 * @param {string} id - ID elemen modal
 */
function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const backdrop = modal.querySelector(".modal-backdrop");
    const panel = modal.querySelector(".modal-panel");

    modal.classList.add("opacity-0", "pointer-events-none");
    modal.classList.remove("pointer-events-auto");

    if (backdrop) {
        backdrop.classList.add("opacity-0");
    }

    if (panel) {
        panel.classList.add("opacity-0", "translate-y-4", "scale-95");
        panel.classList.remove("translate-y-0", "scale-100");
    }

    // Tunggu animasi selesai sebelum menyembunyikan
    setTimeout(() => {
        modal.classList.add("hidden");
        document.body.style.overflow = "";
    }, 200);
}

/**
 * Pasang event untuk membuka dan menutup modal
 */
function bindModals() {
    // Pemicu buka modal
    document.querySelectorAll("[data-modal-open]").forEach((trigger) => {
        trigger.addEventListener("click", (e) => {
            e.stopPropagation();
            const target = trigger.getAttribute("data-modal-open");
            if (target) openModal(target);
        });
    });

    // Tombol tutup modal
    document.querySelectorAll("[data-modal-close]").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            const target = btn.getAttribute("data-modal-close");
            if (target) closeModal(target);
        });
    });

    // Tutup modal saat backdrop diklik
    document.querySelectorAll(".modal-backdrop").forEach((backdrop) => {
        backdrop.addEventListener("click", (e) => {
            if (e.target === backdrop) {
                const modal = backdrop.closest(".modal");
                if (modal && modal.id) {
                    closeModal(modal.id);
                }
            }
        });
    });

    // Tutup modal saat tombol Escape ditekan
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            const modal = document.querySelector(".modal:not(.hidden)");
            if (modal && modal.id) {
                closeModal(modal.id);
            }
        }
    });

    // Tutup modal saat form disubmit berhasil
    // CATATAN: jangan auto-close untuk form yang ditangani AJAX (data-ajax="true") atau jika preventDefault() dipanggil
    document.querySelectorAll(".modal form").forEach((form) => {
        form.addEventListener("submit", function (e) {
            // Jika handler mencegah default atau form bertanda AJAX, lewati auto-close.
            if (e.defaultPrevented || form.dataset.ajax === "true") {
                return;
            }

            const modal = this.closest(".modal");
            if (modal && modal.id) {
                setTimeout(() => {
                    closeModal(modal.id);
                }, 300);
            }
        });
    });
}

/**
 * Inisialisasi semua fungsi UI
 */
export function initUiLayer() {
    bindDrawer();
    bindModals();
}

export { openModal, closeModal, toggleDrawer };

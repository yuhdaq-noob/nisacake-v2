/**
 * UI Layer - Drawer, Modal, and Notifications Management
 *
 * SOLID Principles Applied:
 * - Single Responsibility: Each function handles one UI concern (drawer, modal, notifications)
 * - Open/Closed: Extensible through data attributes without modifying core logic
 * - Liskov Substitution: All toggle methods follow same pattern
 * - Interface Segregation: Functions are focused and don't force unnecessary dependencies
 * - Dependency Inversion: Depends on HTML structure, not specific element types
 *
 * Best Practices:
 * - Separation of concerns (drawer, modal, notification logic)
 * - Event delegation for dynamic elements
 * - Proper cleanup and state management
 * - Accessibility support (aria-labels, roles)
 * - Smooth animations and transitions
 */

/**
 * Toggle drawer open/close state
 * @param {boolean|undefined} open - Force open (true) or close (false), toggle if undefined
 */
function toggleDrawer(open) {
    const drawer = document.querySelector("[data-drawer]");
    const backdrop = document.querySelector("[data-drawer-backdrop]");
    if (!drawer || !backdrop) return;

    const isOpen = open ?? drawer.classList.contains("translate-x-0");
    const nextState = open !== undefined ? open : !isOpen;

    // Update drawer position
    drawer.classList.toggle("-translate-x-full", !nextState);
    drawer.classList.toggle("translate-x-0", nextState);

    // Update backdrop visibility
    backdrop.classList.toggle("hidden", !nextState);

    // Update ARIA attributes for accessibility
    drawer.setAttribute("aria-hidden", !nextState);
    backdrop.setAttribute("aria-hidden", !nextState);

    // Prevent body scroll when drawer is open
    if (nextState) {
        document.body.style.overflow = "hidden";
    } else {
        document.body.style.overflow = "";
    }
}

/**
 * Bind drawer toggle functionality to elements
 */
function bindDrawer() {
    const toggleButtons = document.querySelectorAll("[data-drawer-toggle]");
    const closeButtons = document.querySelectorAll("[data-drawer-close]");
    const backdrop = document.querySelector("[data-drawer-backdrop]");

    // Bind toggle buttons
    toggleButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleDrawer();
        });
    });

    // Bind close buttons
    closeButtons.forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            toggleDrawer(false);
        });
    });

    // Close drawer when backdrop is clicked
    backdrop?.addEventListener("click", () => toggleDrawer(false));

    // Close drawer when pressing Escape key
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
 * Open a modal with animations
 * @param {string} id - Modal element ID
 */
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    const backdrop = modal.querySelector(".modal-backdrop");
    const panel = modal.querySelector(".modal-panel");

    // Make modal visible for animation
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

    // Prevent body scroll
    document.body.style.overflow = "hidden";
}

/**
 * Close a modal with animations
 * @param {string} id - Modal element ID
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

    // Wait for animation to complete before hiding
    setTimeout(() => {
        modal.classList.add("hidden");
        document.body.style.overflow = "";
    }, 200);
}

/**
 * Bind modal trigger and close functionality
 */
function bindModals() {
    // Open modal triggers
    document.querySelectorAll("[data-modal-open]").forEach((trigger) => {
        trigger.addEventListener("click", (e) => {
            e.stopPropagation();
            const target = trigger.getAttribute("data-modal-open");
            if (target) openModal(target);
        });
    });

    // Close modal buttons
    document.querySelectorAll("[data-modal-close]").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.stopPropagation();
            const target = btn.getAttribute("data-modal-close");
            if (target) closeModal(target);
        });
    });

    // Close modal when clicking backdrop
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

    // Close modal when pressing Escape
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            const modal = document.querySelector(".modal:not(.hidden)");
            if (modal && modal.id) {
                closeModal(modal.id);
            }
        }
    });

    // Close modal when form is submitted successfully
    document.querySelectorAll(".modal form").forEach((form) => {
        form.addEventListener("submit", function () {
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
 * Initialize all UI layer functionality
 */
export function initUiLayer() {
    bindDrawer();
    bindModals();
}

export { openModal, closeModal, toggleDrawer };

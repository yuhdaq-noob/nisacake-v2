const DEFAULT_DURATION = 5000;

/**
 * Backwards-compatible helpers (delegate to `showNotification` below).
 */
export function showSuccess(message, options = {}) {
    return showNotification("success", "", message, {
        duration: options.duration ?? 4500,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

export function showErrorToast(message, options = {}) {
    return showNotification("error", "Kesalahan", message, {
        duration: options.duration ?? 8000,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

export function showWarning(message, options = {}) {
    return showNotification("warning", "Peringatan", message, {
        duration: options.duration ?? 6000,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

export function showInfo(message, options = {}) {
    return showNotification("info", "", message, {
        duration: options.duration ?? 4500,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

export function handleSessionExpired() {
    showNotification(
        "warning",
        "Sesi berakhir",
        "Sesi login telah berakhir. Silakan login kembali.",
        {
            duration: 1800,
        },
    );
    setTimeout(() => {
        window.location.href = "/login";
    }, 1200);
}

/**
 * Enhanced Notification/Toast System - Professional & Modern
 */

const notificationConfig = {
    success: {
        icon: "bi-check-circle-fill",
    },
    error: {
        icon: "bi-exclamation-circle-fill",
    },
    warning: {
        icon: "bi-exclamation-triangle-fill",
    },
    info: {
        icon: "bi-info-circle-fill",
    },
};

/**
 * Create or get notification container
 * @returns {HTMLElement}
 */
function getNotificationContainer() {
    let container = document.getElementById("notification-container");

    if (!container) {
        container = document.createElement("div");
        container.id = "notification-container";
        container.className =
            "fixed top-6 right-6 z-[999] max-w-2xl pointer-events-none space-y-3";
        document.body.appendChild(container);
    }

    return container;
}

/**
 * Show notification/toast with enhanced styling
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} title - Notification title
 * @param {string} message - Notification message
 * @param {object} options - { duration: 5000, dismissible: true }
 */
export function showNotification(
    type = "info",
    title = "",
    message = "",
    options = {},
) {
    const { duration = 5000, dismissible = true } = options;

    const config = notificationConfig[type] || notificationConfig["info"];
    const container = getNotificationContainer();
    const notificationId = `notif-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

    // Create notification element
    const notification = document.createElement("div");
    notification.id = notificationId;
    notification.setAttribute("data-type", type);
    notification.className =
        "alert-toast animate-slideInRight pointer-events-auto mb-3";
    notification.role = "alert";

    // Build HTML content
    const titleHTML = title
        ? `<p class="alert-title">${escapeHtml(title)}</p>`
        : "";
    const messageHTML = `<p class="alert-message">${escapeHtml(message)}</p>`;
    const closeButtonHTML = dismissible
        ? `
        <button
            type="button"
            class="alert-toast-close"
            aria-label="Tutup notifikasi"
            onclick="window.closeNotification('${notificationId}')"
        >
            <i class="bi bi-x-lg"></i>
        </button>
    `
        : "";

    notification.innerHTML = `
        <div class="flex-shrink-0">
            <i class="bi ${config.icon}"></i>
        </div>
        <div class="flex-1 min-w-0">
            ${titleHTML}
            ${messageHTML}
        </div>
        ${closeButtonHTML}
    `;

    container.appendChild(notification);

    // Accessibility
    notification.setAttribute(
        "aria-live",
        type === "error" ? "assertive" : "polite",
    );
    notification.tabIndex = 0;
    notification.style.setProperty("--notif-duration", `${duration}ms`);

    // Progress bar (visual timer)
    let closeTimeout;
    let progressEl;

    if (duration > 0) {
        progressEl = document.createElement("div");
        progressEl.className = "alert-toast__progress";
        notification.appendChild(progressEl);

        // Start auto-close timer
        closeTimeout = setTimeout(
            () => closeNotification(notificationId),
            duration,
        );
    } else {
        closeTimeout = null;
    }

    // Pause/resume on hover
    if (dismissible && duration > 0) {
        notification.addEventListener("mouseenter", () => {
            if (closeTimeout) clearTimeout(closeTimeout);
            if (progressEl) progressEl.style.animationPlayState = "paused";
        });

        notification.addEventListener("mouseleave", () => {
            closeTimeout = setTimeout(
                () => closeNotification(notificationId),
                duration,
            );
            if (progressEl) progressEl.style.animationPlayState = "running";
        });
    }

    return notificationId;
}

/**
 * Close a notification
 * @param {string} notificationId - ID of notification to close
 */
export function closeNotification(notificationId) {
    const notification = document.getElementById(notificationId);
    if (!notification) return;

    notification.classList.remove("animate-slideInRight");
    notification.classList.add("animate-slideOutRight");

    setTimeout(() => {
        notification.remove();
        const container = document.getElementById("notification-container");
        if (container && container.children.length === 0) {
            container.remove();
        }
    }, 320);
}

/**
 * Escape HTML special characters
 * @param {string} text
 * @returns {string}
 */
function escapeHtml(text) {
    const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, (m) => map[m]);
}

// Expose globally for accessibility
window.closeNotification = closeNotification;
window.showNotification = showNotification;

export default {
    show: showNotification,
    close: closeNotification,
};

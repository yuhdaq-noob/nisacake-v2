/**
 * Notification/Toast System - Professional & Modern
 *
 * SOLID Principles:
 * - Single Responsibility: Each function handles one notification type
 * - Open/Closed: Extensible through configuration objects
 * - Liskov Substitution: All notification methods follow same pattern
 * - Interface Segregation: Minimal, focused API
 * - Dependency Inversion: Uses HTML structure, not tied to specific DOM
 *
 * Usage:
 *   showNotification('success', 'Sukses!', 'Data telah tersimpan')
 *   showNotification('error', 'Error!', 'Terjadi kesalahan', { duration: 10000 })
 */

const notificationConfig = {
    success: {
        icon: "bi-check-circle-fill",
        bgColor: "bg-emerald-50",
        borderColor: "border-emerald-300",
        textColor: "text-emerald-800",
        titleColor: "text-emerald-900",
        iconColor: "text-emerald-600",
    },
    error: {
        icon: "bi-exclamation-circle-fill",
        bgColor: "bg-red-50",
        borderColor: "border-red-300",
        textColor: "text-red-700",
        titleColor: "text-red-900",
        iconColor: "text-red-600",
    },
    warning: {
        icon: "bi-exclamation-triangle-fill",
        bgColor: "bg-amber-50",
        borderColor: "border-amber-300",
        textColor: "text-amber-700",
        titleColor: "text-amber-900",
        iconColor: "text-amber-600",
    },
    info: {
        icon: "bi-info-circle-fill",
        bgColor: "bg-blue-50",
        borderColor: "border-blue-300",
        textColor: "text-blue-700",
        titleColor: "text-blue-900",
        iconColor: "text-blue-600",
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
            "fixed top-4 right-4 z-[999] max-w-md pointer-events-none";
        document.body.appendChild(container);
    }

    return container;
}

/**
 * Show notification/toast
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
    notification.className = `
        alert-toast
        ${config.bgColor}
        ${config.borderColor}
        border rounded-xl
        px-4 py-3.5 sm:px-5 sm:py-4
        flex items-start gap-3.5
        transition-all duration-300 ease-out
        animate-slideInRight
        pointer-events-auto
        shadow-lg
        mb-3
    `;
    notification.role = "alert";
    notification.setAttribute("data-type", type);

    // Build HTML
    notification.innerHTML = `
        <div class="flex-shrink-0 mt-0.5">
            <i class="bi ${config.icon} ${config.iconColor} text-lg"></i>
        </div>
        <div class="flex-1 min-w-0">
            ${title ? `<p class="font-semibold ${config.titleColor} text-sm sm:text-base mb-1">${escapeHtml(title)}</p>` : ""}
            <p class="text-sm ${config.textColor} opacity-95 leading-relaxed">${escapeHtml(message)}</p>
        </div>
        ${
            dismissible
                ? `
            <button
                type="button"
                class="flex-shrink-0 p-2 text-slate-400 hover:bg-slate-200/50 rounded-lg transition-colors duration-200"
                aria-label="Tutup notifikasi"
                onclick="window.closeNotification('${notificationId}')"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        `
                : ""
        }
    `;

    container.appendChild(notification);

    // Auto-close timer
    let closeTimeout;
    if (duration > 0) {
        closeTimeout = setTimeout(() => {
            closeNotification(notificationId);
        }, duration);
    }

    // Cancel timeout on hover
    if (dismissible && duration > 0) {
        notification.addEventListener("mouseenter", () =>
            clearTimeout(closeTimeout),
        );
        notification.addEventListener("mouseleave", () => {
            closeTimeout = setTimeout(() => {
                closeNotification(notificationId);
            }, duration);
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
        // Clean up container if empty
        const container = document.getElementById("notification-container");
        if (container && container.children.length === 0) {
            container.remove();
        }
    }, 300);
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

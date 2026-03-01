const DEFAULT_DURATION = 5000;

/** Show success notification */
export function showSuccess(message, options = {}) {
    return showNotification("success", "", message, {
        duration: options.duration ?? 4500,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

/** Show error notification */
export function showErrorToast(message, options = {}) {
    return showNotification("error", "Kesalahan", message, {
        duration: options.duration ?? 8000,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

/** Show warning notification */
export function showWarning(message, options = {}) {
    return showNotification("warning", "Peringatan", message, {
        duration: options.duration ?? 6000,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

/** Show info notification */
export function showInfo(message, options = {}) {
    return showNotification("info", "", message, {
        duration: options.duration ?? 4500,
        dismissible: options.dismissible ?? true,
        ...options,
    });
}

/** Handle session expired */
export function handleSessionExpired() {
    showNotification(
        "warning",
        "Sesi berakhir",
        "Sesi login telah berakhir. Silakan login kembali.",
        { duration: 1800 },
    );
    setTimeout(() => {
        window.location.href = "/login";
    }, 1200);
}

/** Notification config */
const notificationConfig = {
    success: { icon: "bi-check-circle-fill" },
    error: { icon: "bi-exclamation-circle-fill" },
    warning: { icon: "bi-exclamation-triangle-fill" },
    info: { icon: "bi-info-circle-fill" },
};

/** Get notification container */
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

/** Show notification */
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

    const notification = document.createElement("div");
    notification.id = notificationId;
    notification.setAttribute("data-type", type);
    notification.className = `toast-card toast-card--${type}`;
    notification.role = "alert";

    const titleHTML = title
        ? `<span class="toast-card__title">${escapeHtml(title)}</span>`
        : "";
    const messageHTML = `<span class="toast-card__message">${escapeHtml(message)}</span>`;
    const closeButtonHTML = dismissible
        ? `<button type="button" class="toast-card__close" aria-label="Tutup notifikasi" onclick="window.closeNotification('${notificationId}')"><i class="bi bi-x-lg"></i></button>`
        : "";

    notification.innerHTML = `
        <div class="toast-card__icon toast-card__icon--${type}"><i class="bi ${config.icon}"></i></div>
        <div class="toast-card__content">${titleHTML}${messageHTML}</div>
        ${closeButtonHTML}
    `;

    container.appendChild(notification);
    notification.setAttribute(
        "aria-live",
        type === "error" ? "assertive" : "polite",
    );
    notification.tabIndex = 0;
    notification.style.setProperty("--notif-duration", `${duration}ms`);

    let closeTimeout;
    let progressEl;

    if (duration > 0) {
        progressEl = document.createElement("div");
        progressEl.className = "toast-card__progress";
        progressEl.style.animationDuration = `${duration}ms`;
        notification.appendChild(progressEl);
        closeTimeout = setTimeout(
            () => closeNotification(notificationId),
            duration,
        );
    } else {
        closeTimeout = null;
    }

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

/** Close notification */
export function closeNotification(notificationId) {
    const notification = document.getElementById(notificationId);
    if (!notification) return;
    notification.style.animation =
        "toastSlideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards";
    setTimeout(() => {
        notification.remove();
        const container = document.getElementById("notification-container");
        if (container && container.children.length === 0) container.remove();
    }, 300);
}

/** Escape HTML */
function escapeHtml(text) {
    if (typeof text !== "string") return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

/** Confirm dialog */
export function confirmDialog(options = {}) {
    const {
        title = "Konfirmasi",
        message = "Apakah Anda yakin?",
        type = "warning",
        confirmText = "Ya",
        cancelText = "Batal",
        onConfirm = null,
        onCancel = null,
    } = options;

    const existingModal = document.getElementById("custom-confirm-modal");
    if (existingModal) existingModal.remove();

    const modalId = `confirm-modal-${Date.now()}`;
    const iconMap = {
        warning: {
            icon: "bi-exclamation-triangle-fill",
            class: "modal-icon--warning",
        },
        danger: {
            icon: "bi-exclamation-circle-fill",
            class: "modal-icon--danger",
        },
        info: { icon: "bi-info-circle-fill", class: "modal-icon--info" },
    };
    const iconConfig = iconMap[type] || iconMap.warning;

    const modal = document.createElement("div");
    modal.id = "custom-confirm-modal";
    modal.className = "modal-overlay";

    modal.innerHTML = `
        <div class="modal-container" id="${modalId}">
            <div class="modal-header">
                <div class="modal-icon ${iconConfig.class}"><i class="bi ${iconConfig.icon}"></i></div>
                <h3 class="modal-title"></h3>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn--secondary" id="${modalId}-cancel">${cancelText}</button>
                <button type="button" class="modal-btn ${type === "danger" ? "modal-btn--danger" : "modal-btn--primary"}" id="${modalId}-confirm"><span class="btn-text">${confirmText}</span></button>
            </div>
        </div>
    `;

    modal.querySelector(".modal-title").textContent = title;
    modal.querySelector(".modal-body").textContent = message;
    document.body.appendChild(modal);

    return new Promise((resolve) => {
        const confirmBtn = document.getElementById(`${modalId}-confirm`);
        const cancelBtn = document.getElementById(`${modalId}-cancel`);
        const container = document.getElementById(modalId);
        let isProcessing = false;

        const closeModal = (result) => {
            modal.classList.remove("active");
            setTimeout(() => modal.remove(), 300);
            resolve(result);
        };

        requestAnimationFrame(() => modal.classList.add("active"));

        confirmBtn.addEventListener("click", async () => {
            if (isProcessing) return;
            isProcessing = true;
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            const btnText = confirmBtn.querySelector(".btn-text");
            const originalText = btnText.textContent;
            btnText.innerHTML =
                '<span class="modal-btn__spinner"></span> Memproses...';
            try {
                if (onConfirm) await onConfirm();
            } catch (error) {
                console.error("Error:", error);
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                btnText.textContent = originalText;
                isProcessing = false;
                return;
            }
            closeModal(true);
        });

        cancelBtn.addEventListener("click", () => {
            if (isProcessing) return;
            if (onCancel) onCancel();
            closeModal(false);
        });

        modal.addEventListener("click", (e) => {
            if (e.target === modal && !isProcessing) {
                if (onCancel) onCancel();
                closeModal(false);
            }
        });

        const handleEscape = (e) => {
            if (e.key === "Escape" && !isProcessing) {
                if (onCancel) onCancel();
                closeModal(false);
                document.removeEventListener("keydown", handleEscape);
            }
        };
        document.addEventListener("keydown", handleEscape);

        const focusableElements = modal.querySelectorAll("button");
        const firstFocusable = focusableElements[0];
        const lastFocusable = focusableElements[focusableElements.length - 1];
        container.addEventListener("keydown", (e) => {
            if (e.key === "Tab") {
                if (e.shiftKey && document.activeElement === firstFocusable) {
                    e.preventDefault();
                    lastFocusable.focus();
                } else if (
                    !e.shiftKey &&
                    document.activeElement === lastFocusable
                ) {
                    e.preventDefault();
                    firstFocusable.focus();
                }
            }
        });

        confirmBtn.focus();
    });
}

window.closeNotification = closeNotification;
window.showNotification = showNotification;
window.confirmDialog = confirmDialog;

export default {
    show: showNotification,
    close: closeNotification,
    confirm: confirmDialog,
};

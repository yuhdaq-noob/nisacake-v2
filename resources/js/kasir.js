/**
 * Modul Kasir (POS)
 * Menangani pemilihan produk, keranjang, dan proses pesanan.
 */

import "./bootstrap";
import "./api.js";
import { getAuthHeaders, formatRupiah, showError, hideError } from "./utils.js";
import {
    handleSessionExpired,
    showSuccess,
    showErrorToast,
    confirmDialog,
} from "./notifications.js";

const apiProducts = "/api/products";
const apiOrder = "/api/buat-pesanan";
const apiPreOrder = "/api/jadwal-pesanan";
const apiCompleteOrder = "/api/orders";

/**
 * Pastikan token autentikasi tersedia
 * Cek localStorage dan arahkan ke login bila tidak ada
 */
function ensureAuthToken() {
    const token = localStorage.getItem("auth_token");
    if (!token) {
        console.warn("No auth token found. Redirecting to login...");
        window.location.href = "/login";
    }
    return token;
}

let cart = [];
let productsDB = [];
let lastOrderId = null;
let isLoadingScheduledOrders = false;

/**
 * Sync cart card height with order input card (desktop only).
 * - sets inline height on `#cartCard` to match `#orderInputCard`
 * - removes inline height on small screens
 */
function syncCartHeight() {
    const inputCard = document.getElementById("orderInputCard");
    const cartCard = document.getElementById("cartCard");
    if (!inputCard || !cartCard) return;

    // Hanya terapkan tinggi yang sama pada layar besar (breakpoint lg)
    if (window.innerWidth < 1024) {
        cartCard.style.height = "";
        return;
    }

    cartCard.style.height = `${inputCard.offsetHeight}px`;
}

/** Helper debounce kecil untuk event resize */
function debounce(fn, wait = 100) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
    };
}

/**
 * Definisi tipe JSDoc
 * @typedef {{product_id:number, name:string, price:number, quantity:number}} CartItem
 * @typedef {{customer_name:string, order_date?:string, items:CartItem[]}} OrderPayload
 */

/**
 * Helper tanggal/waktu
 */
function pad(n) {
    return String(n).padStart(2, "0");
}

function toLocalInput(date) {
    // date: Date -> "YYYY-MM-DDTHH:MM" (format untuk <input type=datetime-local>)
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function roundUpToSlot(date = new Date(), minutes = 15) {
    const ms = 1000 * 60 * minutes;
    return new Date(Math.ceil(date.getTime() / ms) * ms);
}

function nowLocalInputRounded(slotMinutes = 15) {
    return toLocalInput(roundUpToSlot(new Date(), slotMinutes));
}

/**
 * Helper validasi (masing-masing fungsi memvalidasi satu kepentingan)
 */
const FormValidator = {
    validateCustomerName: (name) => {
        if (!name) {
            return { valid: false, error: "Nama pelanggan harus diisi." };
        }
        if (name.length > 255) {
            return {
                valid: false,
                error: "Nama pelanggan maksimal 255 karakter.",
            };
        }
        return { valid: true };
    },

    validateOrderDate: (dateTimeStr, futureOnly = false) => {
        if (!dateTimeStr) {
            return {
                valid: false,
                error: "Tanggal/waktu pesanan harus diisi.",
            };
        }

        const d = new Date(dateTimeStr);
        if (Number.isNaN(d.getTime())) {
            return { valid: false, error: "Format tanggal/waktu tidak valid." };
        }

        if (futureOnly) {
            const now = new Date();
            // backend mengharuskan scheduled_at berada di masa depan (StoreOrderRequest::after:now)
            if (d <= now) {
                return {
                    valid: false,
                    error: "Waktu jadwal harus di masa depan.",
                };
            }
        }

        return { valid: true };
    },

    validateCart: (cart) => {
        if (cart.length === 0) {
            return {
                valid: false,
                error: "Keranjang kosong. Tambahkan produk terlebih dahulu.",
            };
        }
        return { valid: true };
    },

    validateProduct: (inputVal, qty) => {
        if (!inputVal || isNaN(qty) || qty < 1) {
            return {
                valid: false,
                error: "Pilih produk dan masukkan jumlah yang valid.",
            };
        }
        return { valid: true };
    },
};

document.addEventListener("DOMContentLoaded", async () => {
    if (!document.getElementById("product_list")) return;

    // Set default order_date ke waktu sekarang (dibulatkan ke slot berikutnya) dan set min agar tidak bisa pilih waktu lampau
    const orderDateInput = document.getElementById("order_date");
    if (orderDateInput) {
        orderDateInput.value = nowLocalInputRounded(15);
        orderDateInput.min = toLocalInput(new Date());
    }

    try {
        const response = await fetch(apiProducts, {
            headers: {
                ...getAuthHeaders(),
            },
        });

        if (response.status === 401) {
            handleSessionExpired();
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const rawData = await response.json();
        if (Array.isArray(rawData)) {
            productsDB = rawData;
        } else if (rawData.data && Array.isArray(rawData.data)) {
            productsDB = rawData.data;
        } else {
            productsDB = [];
        }

        const datalist = document.getElementById("product_list");
        datalist.innerHTML = "";

        if (productsDB.length === 0) {
            showError("error_product_input", "No products available.");
        }

        productsDB.forEach((p) => {
            const option = document.createElement("option");
            option.value = `${p.name} - ${formatRupiah(p.selling_price)}`;
            datalist.appendChild(option);
        });

        // Muat pesanan terjadwal setelah produk selesai dimuat
        await loadScheduledOrders();
    } catch (error) {
        console.error("Error:", error);
        showError(
            "error_product_input",
            "Failed to load products. Check your connection.",
        );
    }

    // Bersihkan pesan error saat pengguna memasukkan input
    document
        .getElementById("customer_name")
        ?.addEventListener("input", () => hideError("error_customer_name"));
    document
        .getElementById("product_input")
        ?.addEventListener("input", () => hideError("error_product_input"));
    document
        .getElementById("quantity")
        ?.addEventListener("input", () => hideError("error_product_input"));
    document
        .getElementById("order_date")
        ?.addEventListener("input", () => hideError("error_order_date"));

    // Pasang tombol Tes Telegram di Kasir (konfirmasi + umpan balik)
    const btnTestTelegramKasir = document.getElementById(
        "btnTestTelegramKasir",
    );
    if (btnTestTelegramKasir) {
        btnTestTelegramKasir.addEventListener("click", async () => {
            const confirmSend = await confirmDialog({
                title: "Kirim Tes Telegram",
                message: "Kirim pesan tes ke Telegram sekarang?",
                type: "info",
                confirmText: "Kirim",
                cancelText: "Batal",
            });
            if (!confirmSend) return;

            btnTestTelegramKasir.disabled = true;
            try {
                const res = await fetch("/admin/telegram/test", {
                    method: "GET",
                    headers: { ...getAuthHeaders() },
                });
                const body = await res.json().catch(() => ({}));
                if (res.ok) {
                    showSuccess("Cek Telegram Anda.");
                } else {
                    showErrorToast(
                        "Gagal: " +
                            (body.message || body.error || res.statusText),
                    );
                }
            } catch (err) {
                console.error(err);
                showErrorToast("Terjadi kesalahan saat mengirim test.");
            } finally {
                btnTestTelegramKasir.disabled = false;
            }
        });
    }

    // Sync cart height with input card and attach resize handler
    syncCartHeight();
    window.addEventListener("resize", debounce(syncCartHeight, 120));
});

function tambahKeKeranjang() {
    hideError("error_product_input");

    const inputVal = document.getElementById("product_input").value;
    const qty = parseInt(document.getElementById("quantity").value);

    // Validate product and quantity
    const productValidation = FormValidator.validateProduct(inputVal, qty);
    if (!productValidation.valid) {
        showError("error_product_input", productValidation.error);
        return;
    }

    // Find product by input string format
    const product = productsDB.find(
        (p) => `${p.name} - ${formatRupiah(p.selling_price)}` === inputVal,
    );

    if (!product) {
        showError(
            "error_product_input",
            "Produk tidak ditemukan. Pilih dari daftar yang tersedia.",
        );
        return;
    }

    // Add product to cart
    const productId = product.id;
    const existingItem = cart.find((item) => item.product_id == productId);

    if (existingItem) {
        existingItem.quantity += qty;
    } else {
        cart.push({
            product_id: productId,
            name: product.name,
            price: product.selling_price,
            quantity: qty,
        });
    }
    renderCart();

    // Reset input for next product
    document.getElementById("product_input").value = "";
    document.getElementById("quantity").value = "1";
}

function renderCart() {
    const tbody = document.getElementById("tabelKeranjang");
    const totalDisplay = document.getElementById("totalDisplay");

    if (cart.length === 0) {
        tbody.innerHTML = `<tr>
                <td colspan="5" class="table-empty-state">
                    <p>Keranjang kosong</p>
                    <small class="text-slate-400">Pilih produk untuk memulai.</small>
                </td>
            </tr>`;
        totalDisplay.innerText = "Rp 0";
        // sync heights so cart card matches input card even when empty
        syncCartHeight();
        return;
    }

    let html = "";
    let grandTotal = 0;

    cart.forEach((item, index) => {
        const priceNum = Number(item.price) || 0;
        const qtyNum = Number(item.quantity) || 0;
        const subtotal = priceNum * qtyNum;
        grandTotal += subtotal;

        html += `
                <tr class="hover:bg-slate-700/50 transition-colors">
                    <td class="font-semibold text-slate-200 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">${item.name}</td>
                    <td class="text-right text-slate-400 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">${formatRupiah(priceNum)}</td>
                    <td class="text-center font-semibold text-cyan-400 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">${item.quantity}</td>
                    <td class="text-right font-semibold text-slate-200 px-2 sm:px-4 py-2 sm:py-3 text-xs sm:text-sm">${formatRupiah(subtotal)}</td>
                    <td class="text-center px-2 sm:px-4 py-2 sm:py-3">
                        <button class="table-action-btn table-action-btn--delete" onclick="hapusItem(${index})" type="button" aria-label="Hapus item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
        `;
    });

    tbody.innerHTML = html;
    totalDisplay.innerText = formatRupiah(grandTotal);
}

function hapusItem(index) {
    cart.splice(index, 1);
    renderCart();
}

/**
 * Process order submission (common logic for immediate order and pre-order)
 * Mengikuti Single Responsibility & DRY: Logika bersama pengiriman order
 *
 * @param {string} endpoint - API endpoint to call
 * @param {Object} payload - Order data to submit
 * @param {HTMLElement} submitButton - Button element
 * @param {string} successMessage - Message to show on success
 * @param {Function} onSuccess - Callback on successful submission
 */
async function submitOrderRequest(
    endpoint,
    payload,
    submitButton,
    successMessage,
    onSuccess,
) {
    const submitLabel = submitButton?.querySelector("span");
    const originalLabel = submitLabel?.innerText || "";

    try {
        submitButton.disabled = true;
        if (submitLabel) submitLabel.innerText = "Processing...";

        const response = await fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                ...getAuthHeaders(),
            },
            body: JSON.stringify(payload),
        });

        // Handle authentication errors
        if (response.status === 401) {
            handleSessionExpired();
            return;
        }

        // Check if response is valid JSON before parsing
        if (!response.ok) {
            const contentType = response.headers.get("content-type");
            let errorMessage = "An error occurred.";

            if (contentType?.includes("application/json")) {
                const result = await response.json();

                // Handle Laravel validation errors (422 status)
                if (response.status === 422 && result.errors) {
                    const errors = result.errors;
                    const errorMessages = [];
                    for (const [field, messages] of Object.entries(errors)) {
                        if (Array.isArray(messages)) {
                            errorMessages.push(...messages);
                        } else {
                            errorMessages.push(messages);
                        }
                    }
                    errorMessage =
                        errorMessages.join("; ") ||
                        result.message ||
                        errorMessage;
                } else {
                    errorMessage = result.message || errorMessage;
                }
            }

            showError("error_checkout", "Failed: " + errorMessage);
            return;
        }

        const result = await response.json();
        showSuccess(successMessage);
        if (onSuccess) onSuccess(result);
    } catch (error) {
        console.error("Error submitting order:", error);
        showError(
            "error_checkout",
            "System error occurred. Please check your connection.",
        );
    } finally {
        if (submitButton) submitButton.disabled = false;
        if (submitLabel) submitLabel.innerText = originalLabel;
    }
}

/**
 * Process immediate order with stock deduction
 * Mengikuti Single Responsibility: Hanya menangani validasi dan pengiriman order langsung
 */
async function prosesTransaksi() {
    hideError("error_customer_name");
    hideError("error_checkout");
    hideError("error_order_date");

    const customerName = document.getElementById("customer_name").value;
    const orderDate = document.getElementById("order_date").value;

    // Validate all inputs
    const nameValidation = FormValidator.validateCustomerName(customerName);
    if (!nameValidation.valid) {
        showError("error_customer_name", nameValidation.error);
        return;
    }

    const dateValidation = FormValidator.validateOrderDate(orderDate);
    if (!dateValidation.valid) {
        showError("error_order_date", dateValidation.error);
        return;
    }

    const cartValidation = FormValidator.validateCart(cart);
    if (!cartValidation.valid) {
        showError("error_checkout", cartValidation.error);
        return;
    }

    const payload = {
        customer_name: customerName,
        order_date: orderDate,
        items: cart,
    };
    const submitButton = document.getElementById("btnBayarSekarang");

    await submitOrderRequest(
        apiOrder,
        payload,
        submitButton,
        "Pesanan berhasil diproses.",
        (result) => {
            const createdOrderId = result?.data?.id ?? result?.id ?? null;
            if (createdOrderId) {
                lastOrderId = createdOrderId;
                showCompleteBox(createdOrderId);
            }

            // Reset UI / kosongkan keranjang setelah pesanan berhasil diproses
            cart = [];
            renderCart();
            document.getElementById("customer_name").value = "";
            document.getElementById("product_input").value = "";
            document.getElementById("quantity").value = "1";
        },
    );
}

/**
 * Process pre-order without stock deduction
 * Mengikuti Single Responsibility: Hanya menangani validasi dan pengiriman pre-order
 */
/**
 * Format date string to match Laravel validation: Y-m-d\TH:i
 * Input: "2026-02-20" (from input type="date")
 * Output: "2026-02-20T00:00"
 * Note: Uses UTC to avoid timezone issues, then converts to local time string
 */
function formatDateTimeForBackend(dateString) {
    if (!dateString) return null;

    // If already contains time (datetime-local format), try to normalize and return
    const datetimeRegex = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/;
    if (datetimeRegex.test(dateString)) {
        return dateString;
    }

    // If date-only (YYYY-MM-DD), append midnight
    const dateOnlyRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (dateOnlyRegex.test(dateString)) {
        return `${dateString}T00:00`;
    }

    // Fallback: try parsing and formatting (local time)
    const parsed = new Date(dateString);
    if (Number.isNaN(parsed.getTime())) return null;
    return toLocalInput(parsed);
}

/**
 * Process pre-order without stock deduction
 * Mengikuti Single Responsibility: Hanya menangani validasi dan pengiriman pre-order
 */
async function jadwalkanPesanan() {
    hideError("error_customer_name");
    hideError("error_checkout");
    hideError("error_order_date");

    const customerName = document.getElementById("customer_name").value;
    const orderDate = document.getElementById("order_date").value;

    // Validate all inputs
    const nameValidation = FormValidator.validateCustomerName(customerName);
    if (!nameValidation.valid) {
        showError("error_customer_name", nameValidation.error);
        return;
    }

    const dateValidation = FormValidator.validateOrderDate(orderDate, true);
    if (!dateValidation.valid) {
        showError("error_order_date", dateValidation.error);
        return;
    }

    const cartValidation = FormValidator.validateCart(cart);
    if (!cartValidation.valid) {
        showError("error_checkout", cartValidation.error);
        return;
    }

    const formattedDateTime = formatDateTimeForBackend(orderDate);

    const payload = {
        customer_name: customerName,
        scheduled_at: formattedDateTime,
        items: cart,
    };

    const submitButton = document.getElementById("btnJadwalkanPesanan");

    await submitOrderRequest(
        apiPreOrder,
        payload,
        submitButton,
        "Pre-order berhasil dijadwalkan.",
        () => {
            // Reset form after success
            document.getElementById("customer_name").value = "";
            document.getElementById("product_input").value = "";
            document.getElementById("quantity").value = "1";
            cart = [];
            renderCart();
            // Refresh scheduled orders after pre-order is created
            loadScheduledOrders();
        },
    );
}

function showCompleteBox(orderId) {
    const box = document.getElementById("orderCompleteBox");
    const label = document.getElementById("lastOrderId");
    if (label) label.innerText = `#${orderId}`;
    if (box) box.classList.remove("hidden");
}

async function completeLastOrder() {
    if (!lastOrderId) return;

    try {
        const response = await fetch(
            `${apiCompleteOrder}/${lastOrderId}/complete`,
            {
                method: "PATCH",
                headers: {
                    ...getAuthHeaders(),
                },
            },
        );
        if (response.status === 401) {
            handleSessionExpired();
            return;
        }
        if (response.ok) {
            showSuccess(`Pesanan #${lastOrderId} berhasil ditandai selesai.`);
            lastOrderId = null;
            const box = document.getElementById("orderCompleteBox");
            if (box) box.classList.add("hidden");
        } else {
            const result = await response.json().catch(() => ({}));
            showErrorToast(result.message || "Gagal menandai pesanan selesai.");
        }
    } catch (error) {
        console.error(error);
        showErrorToast("Terjadi kesalahan saat memperbarui status pesanan.");
    }
}

/**
 * Load and render scheduled pre-orders
 * Mengikuti Single Responsibility: Mengambil data dan mendelegasikan ke fungsi render
 * UX: show loading spinner on refresh button, disable to prevent double-clicks,
 * and surface error toast on failure.
 */
async function loadScheduledOrders() {
    const container = document.getElementById("scheduledOrdersContainer");
    const refreshBtn = document.getElementById("btnRefreshScheduledOrders");
    if (!container) return;

    // prevent duplicate concurrent requests
    if (isLoadingScheduledOrders) return;
    isLoadingScheduledOrders = true;

    // set loading UI on button
    if (refreshBtn) {
        refreshBtn.dataset.loading = "true";
        refreshBtn.setAttribute("aria-busy", "true");
        refreshBtn.classList.add("btn-loading");
        refreshBtn.querySelector(".refresh-icon")?.classList.add("hidden");
        refreshBtn
            .querySelector(".refresh-spinner")
            ?.classList.remove("hidden");
    }

    try {
        const response = await fetch(apiPreOrder, {
            headers: {
                ...getAuthHeaders(),
            },
        });

        // Handle authentication errors
        if (response.status === 401) {
            handleSessionExpired();
            return;
        }

        // Handle non-OK responses (including 500 errors)
        if (!response.ok) {
            const errText = `Server error: ${response.status}`;
            throw new Error(errText);
        }

        const rawData = await response.json();
        let scheduledOrders = [];

        if (Array.isArray(rawData)) {
            scheduledOrders = rawData;
        } else if (rawData?.data && Array.isArray(rawData.data)) {
            scheduledOrders = rawData.data;
        }

        renderScheduledOrders(container, scheduledOrders);
        // micro-animation on refresh button (less noisy)
        showRefreshSuccessAnimation();
    } catch (error) {
        console.error("Error loading scheduled orders:", error);
        showErrorToast("Gagal memuat pesanan terjadwal. Periksa koneksi.");
        // preserve previous behavior: render empty state if no data
        renderScheduledOrders(container, []);
    } finally {
        // restore button UI
        isLoadingScheduledOrders = false;
        if (refreshBtn) {
            refreshBtn.dataset.loading = "false";
            refreshBtn.setAttribute("aria-busy", "false");
            refreshBtn.classList.remove("btn-loading");
            refreshBtn
                .querySelector(".refresh-icon")
                ?.classList.remove("hidden");
            refreshBtn
                .querySelector(".refresh-spinner")
                ?.classList.add("hidden");
        }
    }
}

/**
 * Render scheduled pre-orders in the UI
 * Mengikuti Single Responsibility: Hanya menangani logika rendering
 */
function renderScheduledOrders(container, orders) {
    if (orders.length === 0) {
        container.innerHTML = `
            <div class="text-center py-6 sm:py-8 text-slate-500">
                <p class="font-semibold text-sm sm:text-base">Belum ada pesanan terjadwal</p>
                <small class="text-[0.7rem] sm:text-xs text-slate-400">Gunakan tombol "Jadwalkan" untuk membuat pre-order</small>
            </div>
        `;
        return;
    }

    let html = `<div class="space-y-2 sm:space-y-3">`;

    orders.forEach((order) => {
        const scheduledAt = new Date(order.scheduled_at || Date.now());
        const datePart = scheduledAt.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
        const timePart = scheduledAt.toLocaleTimeString("id-ID", {
            hour: "2-digit",
            minute: "2-digit",
        });

        const itemsList =
            order.items
                ?.map(
                    (item) =>
                        `${item.product?.name ?? "Unknown"} (${item.quantity}x)`,
                )
                .join(", ") || "-";

        html += `
            <div class="bg-slate-800 rounded-lg shadow-sm hover:shadow-md transform hover:-translate-y-0.5 transition duration-200 border border-slate-700 p-3">
                <div class="flex justify-between items-start gap-3 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[0.7rem] font-semibold text-slate-400">ID #${order.id}</p>
                        <p class="text-lg font-semibold text-white mt-1 truncate flex items-center">
                            <i class="bi bi-person-circle text-slate-400 mr-2"></i>
                            ${order.customer_name || "-"}
                        </p>
                    </div>

                    <div class="text-right flex-shrink-0 flex flex-col items-end gap-1">
                        <span class="text-xs font-medium px-2 py-1 rounded-full bg-blue-500/20 text-blue-400 uppercase mr-2">TERJADWAL</span>
                        <div class="mt-1 text-right">
                            <div class="text-sm font-medium text-slate-200">${datePart}</div>
                            <div class="text-xs text-slate-400 mt-0.5">${timePart}</div>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-2 line-clamp-2">Items: ${itemsList}</p>

                <div class="border-t border-slate-700 mt-3 pt-3 flex items-center justify-between gap-3 flex-wrap">
                    <p class="text-xl font-bold text-white">${formatRupiah(order.total_price || 0)}</p>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            onclick="cancelScheduledOrder(${order.id})"
                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-red-400 hover:text-white bg-red-500/10 hover:bg-red-600 border border-red-500/30 hover:border-red-600 rounded-lg transition-all duration-200"
                            aria-label="Batalkan pesanan"
                        >
                            <i class="bi bi-x-lg"></i>
                            <span class="hidden sm:inline">Batal</span>
                        </button>
                        <button
                            type="button"
                            onclick="completeScheduledOrder(${order.id})"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold shadow-sm hover:shadow-md transition bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="w-3 h-3">
                                <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                            </svg>
                            <span class="hidden sm:inline">Bayar</span>
                            <span class="sm:hidden">Bayar</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    html += `</div>`;
    container.innerHTML = html;
}

/**
 * Process scheduled pre-order to complete order
 * Converts pre-order status from PRE_ORDER to COMPLETED with stock deduction
 */
async function completeScheduledOrder(orderId) {
    hideError("error_checkout");

    try {
        // Call executePreOrder endpoint which handles entire conversion logic
        const submitResponse = await fetch(
            `/api/orders/${orderId}/execute-preorder`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    ...getAuthHeaders(),
                },
            },
        );

        if (submitResponse.status === 401) {
            handleSessionExpired();
            return;
        }

        if (!submitResponse.ok) {
            const contentType = submitResponse.headers.get("content-type");
            let errorMessage = "An error occurred.";

            if (contentType?.includes("application/json")) {
                const result = await submitResponse.json();
                errorMessage = result.message || errorMessage;
            }

            showError("error_checkout", "Failed: " + errorMessage);
            return;
        }

        showSuccess(`Pesanan dari jadwal #${orderId} berhasil diproses.`);
        // Refresh jadwal pesanan list agar pesanan yang sudah dieksekusi otomatis hilang
        await loadScheduledOrders();
    } catch (error) {
        console.error("Error executing pre-order:", error);
        showError(
            "error_checkout",
            error.message || "System error occurred. Please try again.",
        );
    }
}

const completeButton = document.getElementById("btnCompleteOrder");
if (completeButton) {
    completeButton.addEventListener("click", completeLastOrder);
}

/**
 * Show a micro animation (check) on the Refresh button to confirm success (less noisy)
 */
function showRefreshSuccessAnimation() {
    const btn = document.getElementById("btnRefreshScheduledOrders");
    if (!btn) return;
    if (btn.dataset.loading === "true") return; // don't animate while loading

    const icon = btn.querySelector(".refresh-icon");
    const confirm = btn.querySelector(".refresh-confirm");

    btn.classList.add("btn-success-flash");
    icon?.classList.add("hidden");
    confirm?.classList.remove("hidden");
    confirm?.classList.add("show");

    setTimeout(() => {
        confirm?.classList.remove("show");
        confirm?.classList.add("hidden");
        icon?.classList.remove("hidden");
        btn.classList.remove("btn-success-flash");
    }, 900);
}

/**
 * Cancel scheduled pre-order
 * Changes order status from PRE_ORDER to CANCELLED
 */
async function cancelScheduledOrder(orderId) {
    // Konfirmasi sebelum membatalkan
    const confirmCancel = await confirmDialog({
        title: "Batalkan Pesanan",
        message: `Yakin ingin membatalkan pesanan #${orderId}?\n\nPesanan yang dibatalkan tidak dapat dikembalikan.`,
        type: "danger",
        confirmText: "Ya, Batalkan",
        cancelText: "Tidak",
    });
    if (!confirmCancel) return;

    hideError("error_checkout");

    try {
        const response = await fetch(`/api/orders/${orderId}/cancel`, {
            method: "PATCH",
            headers: {
                ...getAuthHeaders(),
            },
        });

        if (response.status === 401) {
            handleSessionExpired();
            return;
        }

        if (!response.ok) {
            const contentType = response.headers.get("content-type");
            let errorMessage = "An error occurred.";

            if (contentType?.includes("application/json")) {
                const result = await response.json();
                errorMessage = result.message || errorMessage;
            }

            showError("error_checkout", "Failed: " + errorMessage);
            return;
        }

        showSuccess(`Pesanan #${orderId} berhasil dibatalkan.`);
        // Refresh jadwal pesanan list agar pesanan yang dibatalkan otomatis hilang
        await loadScheduledOrders();
    } catch (error) {
        console.error("Error cancelling order:", error);
        showError(
            "error_checkout",
            error.message || "System error occurred. Please try again.",
        );
    }
}

// Expose functions to global scope so inline onclick in Blade works when bundled by Vite
window.tambahKeKeranjang = tambahKeKeranjang;
window.hapusItem = hapusItem;
window.prosesTransaksi = prosesTransaksi;
window.jadwalkanPesanan = jadwalkanPesanan;
window.loadScheduledOrders = loadScheduledOrders;
window.completeScheduledOrder = completeScheduledOrder;
window.cancelScheduledOrder = cancelScheduledOrder;

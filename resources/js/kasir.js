/**
 * Point of Sale (POS) module.
 * Handles product selection, shopping cart, and order processing.
 */

import "./bootstrap";
import "./api.js";
import { getAuthHeaders, formatRupiah, showError, hideError } from "./utils.js";

const apiProducts = "/api/products";
const apiOrder = "/api/buat-pesanan";
const apiPreOrder = "/api/jadwal-pesanan";
const apiCompleteOrder = "/api/orders";

/**
 * Ensure authentication token is available
 * Checks localStorage for token, refreshes if needed
 * Follows Single Responsibility: Only handles token setup
 */
function ensureAuthToken() {
    const token = localStorage.getItem('auth_token');
    if (!token) {
        console.warn('No auth token found. Redirecting to login...');
        window.location.href = '/login';
    }
    return token;
}

let cart = [];
let productsDB = [];
let lastOrderId = null;

/**
 * Validation helper functions
 * Follows Single Responsibility: Each function validates a specific concern
 */
const FormValidator = {
    validateCustomerName: (name) => {
        if (!name) {
            return { valid: false, error: "Nama pelanggan harus diisi." };
        }
        if (name.length > 255) {
            return { valid: false, error: "Nama pelanggan maksimal 255 karakter." };
        }
        return { valid: true };
    },

    validateOrderDate: (date, futureOnly = false) => {
        if (!date) {
            return { valid: false, error: "Tanggal pesanan harus diisi." };
        }
        if (futureOnly) {
            const selectedDate = new Date(date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                return { valid: false, error: "Tanggal jadwal harus hari ini atau di masa depan." };
            }
        }
        return { valid: true };
    },

    validateCart: (cart) => {
        if (cart.length === 0) {
            return { valid: false, error: "Keranjang kosong. Tambahkan produk terlebih dahulu." };
        }
        return { valid: true };
    },

    validateProduct: (inputVal, qty) => {
        if (!inputVal || isNaN(qty) || qty < 1) {
            return { valid: false, error: "Pilih produk dan masukkan jumlah yang valid." };
        }
        return { valid: true };
    }
};

document.addEventListener("DOMContentLoaded", async () => {
    if (!document.getElementById("product_list")) return;

    // Set default date untuk order_date ke hari ini
    const orderDateInput = document.getElementById("order_date");
    if (orderDateInput) {
        const today = new Date().toISOString().split('T')[0];
        orderDateInput.value = today;
    }

    try {
        const response = await fetch(apiProducts, {
            headers: {
                ...getAuthHeaders(),
            },
        });

        if (response.status === 401) {
            alert("Sesi login telah berakhir. Silakan login kembali.");
            window.location.href = "/login";
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

        // Load scheduled orders after products are loaded
        await loadScheduledOrders();

    } catch (error) {
        console.error("Error:", error);
        showError(
            "error_product_input",
            "Failed to load products. Check your connection.",
        );
    }

    // Clear error messages on user input
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
                <td colspan="5" class="text-center py-4 sm:py-5 text-slate-500">
                    <span class="block mb-1 font-semibold text-xs sm:text-sm">Keranjang kosong</span>
                    <small class="text-[0.65rem] sm:text-xs text-slate-400">Pilih produk untuk memulai.</small>
                </td>
            </tr>`;
        totalDisplay.innerText = "Rp 0";
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
                <tr class="hover:bg-slate-50 border-b border-slate-100">
                    <td class="font-semibold text-slate-900 px-1 sm:px-2 py-2 sm:py-2.5 text-[0.7rem] sm:text-xs">${item.name}</td>
                    <td class="text-right text-slate-500 px-1 sm:px-2 py-2 sm:py-2.5 text-[0.7rem] sm:text-xs">${formatRupiah(priceNum)}</td>
                    <td class="text-center font-semibold text-slate-900 px-1 sm:px-2 py-2 sm:py-2.5 text-[0.7rem] sm:text-xs">${item.quantity}</td>
                    <td class="text-right font-semibold text-slate-900 px-1 sm:px-2 py-2 sm:py-2.5 text-[0.7rem] sm:text-xs">${formatRupiah(subtotal)}</td>
                    <td class="text-center px-1 sm:px-2 py-2 sm:py-2.5"><button class="inline-flex items-center justify-center w-6 h-6 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-all duration-200" onclick="hapusItem(${index})" type="button"><i class="bi bi-trash text-xs sm:text-sm"></i></button></td>
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
 * Follows Single Responsibility & DRY: Shared order submission logic
 *
 * @param {string} endpoint - API endpoint to call
 * @param {Object} payload - Order data to submit
 * @param {HTMLElement} submitButton - Button element
 * @param {string} successMessage - Message to show on success
 * @param {Function} onSuccess - Callback on successful submission
 */
async function submitOrderRequest(endpoint, payload, submitButton, successMessage, onSuccess) {
    const submitLabel = submitButton?.querySelector('span');
    const originalLabel = submitLabel?.innerText || '';

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
            showError(
                "error_checkout",
                "Sesi login telah berakhir. Silakan login kembali.",
            );
            setTimeout(() => window.location.href = "/login", 1000);
            return;
        }

        // Check if response is valid JSON before parsing
        if (!response.ok) {
            const contentType = response.headers.get('content-type');
            let errorMessage = "An error occurred.";

            if (contentType?.includes('application/json')) {
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
                    errorMessage = errorMessages.join('; ') || result.message || errorMessage;
                } else {
                    errorMessage = result.message || errorMessage;
                }
            }

            showError("error_checkout", "Failed: " + errorMessage);
            return;
        }

        const result = await response.json();
        alert(successMessage);
        if (onSuccess) onSuccess(result);

    } catch (error) {
        console.error("Error submitting order:", error);
        showError(
            "error_checkout",
            "System error occurred. Please check your connection."
        );
    } finally {
        if (submitButton) submitButton.disabled = false;
        if (submitLabel) submitLabel.innerText = originalLabel;
    }
}

/**
 * Process immediate order with stock deduction
 * Follows Single Responsibility: Only handles immediate order validation and submission
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

    const payload = { customer_name: customerName, order_date: orderDate, items: cart };
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
        }
    );
}

/**
 * Process pre-order without stock deduction
 * Follows Single Responsibility: Only handles pre-order validation and submission
 */
/**
 * Format date string to match Laravel validation: Y-m-d\TH:i
 * Input: "2026-02-20" (from input type="date")
 * Output: "2026-02-20T00:00"
 * Note: Uses UTC to avoid timezone issues, then converts to local time string
 */
function formatDateTimeForBackend(dateString) {
    if (!dateString) return null;

    // Parse date without timezone conversion (treat as local date)
    const parts = dateString.split('-');
    const year = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10);
    const day = parseInt(parts[2], 10);

    // Format: YYYY-MM-DDTHH:MM
    const monthStr = String(month).padStart(2, '0');
    const dayStr = String(day).padStart(2, '0');

    return `${year}-${monthStr}-${dayStr}T00:00`;
}

/**
 * Process pre-order without stock deduction
 * Follows Single Responsibility: Only handles pre-order validation and submission
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
        items: cart
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
        }
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
            alert("Sesi login telah berakhir. Silakan login kembali.");
            window.location.href = "/login";
            return;
        }
        if (response.ok) {
            alert(`Pesanan #${lastOrderId} berhasil ditandai selesai.`);
            lastOrderId = null;
            const box = document.getElementById("orderCompleteBox");
            if (box) box.classList.add("hidden");
        } else {
            const result = await response.json().catch(() => ({}));
            alert(result.message || "Gagal menandai pesanan selesai.");
        }
    } catch (error) {
        console.error(error);
        alert("Terjadi kesalahan saat memperbarui status pesanan.");
    }
}

/**
 * Load and render scheduled pre-orders
 * Follows Single Responsibility: Fetches and delegates to render
 */
async function loadScheduledOrders() {
    const container = document.getElementById("scheduledOrdersContainer");
    if (!container) return;

    try {
        const response = await fetch(apiPreOrder, {
            headers: {
                ...getAuthHeaders(),
            },
        });

        // Handle authentication errors
        if (response.status === 401) {
            window.location.href = "/login";
            return;
        }

        // Handle non-OK responses (including 500 errors)
        if (!response.ok) {
            throw new Error(`Server error: ${response.status}`);
        }

        const rawData = await response.json();
        let scheduledOrders = [];

        if (Array.isArray(rawData)) {
            scheduledOrders = rawData;
        } else if (rawData?.data && Array.isArray(rawData.data)) {
            scheduledOrders = rawData.data;
        }

        renderScheduledOrders(container, scheduledOrders);

    } catch (error) {
        console.error("Error loading scheduled orders:", error);
        // Show empty state instead of error - this is OK if there's no pre-orders yet
        renderScheduledOrders(container, []);
    }
}

/**
 * Render scheduled pre-orders in the UI
 * Follows Single Responsibility: Only handles rendering logic
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
        const scheduledDate = new Date(order.scheduled_at).toLocaleDateString('id-ID', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const itemsList = order.items?.map(item =>
            `${item.product?.name ?? 'Unknown'} (${item.quantity}x)`
        ).join(', ') || '-';

        html += `
            <div class="border border-slate-200 rounded-lg p-2.5 sm:p-3 bg-slate-50 hover:bg-slate-100 transition-colors">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[0.7rem] font-semibold text-slate-600">ID #${order.id}</p>
                        <p class="text-xs sm:text-sm font-semibold text-slate-900 mt-0.5 sm:mt-1 truncate">${order.customer_name}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-[0.7rem] text-slate-600">Jadwal</p>
                        <p class="text-xs sm:text-sm font-semibold text-slate-900 mt-0.5">${scheduledDate}</p>
                    </div>
                </div>
                <p class="text-[0.65rem] sm:text-xs text-slate-600 mb-2 line-clamp-2">Items: ${itemsList}</p>
                <div class="flex gap-2 justify-between sm:justify-end items-center flex-wrap">
                    <p class="text-xs sm:text-sm font-bold text-cyan-700">${formatRupiah(order.total_price || 0)}</p>
                    <button
                        type="button"
                        onclick="completeScheduledOrder(${order.id})"
                        class="btn-prim"
                        style="padding: 0.5rem 0.75rem; font-size: 0.75rem; white-space: nowrap;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16" class="w-3 h-3">
                            <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                        </svg>
                        <span class="hidden sm:inline">Bayar</span>
                        <span class="sm:hidden" style="font-size: 0.65rem;">Bayar</span>
                    </button>
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
        const submitResponse = await fetch(`/api/orders/${orderId}/execute-preorder`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                ...getAuthHeaders(),
            },
        });

        if (submitResponse.status === 401) {
            window.location.href = "/login";
            return;
        }

        if (!submitResponse.ok) {
            const contentType = submitResponse.headers.get('content-type');
            let errorMessage = "An error occurred.";

            if (contentType?.includes('application/json')) {
                const result = await submitResponse.json();
                errorMessage = result.message || errorMessage;
            }

            showError("error_checkout", "Failed: " + errorMessage);
            return;
        }

        alert(`Pesanan dari jadwal #${orderId} berhasil diproses.`);
        // Refresh jadwal pesanan list agar pesanan yang sudah dieksekusi otomatis hilang
        await loadScheduledOrders();

    } catch (error) {
        console.error("Error executing pre-order:", error);
        showError(
            "error_checkout",
            error.message || "System error occurred. Please try again."
        );
    }
}

const completeButton = document.getElementById("btnCompleteOrder");
if (completeButton) {
    completeButton.addEventListener("click", completeLastOrder);
}

// Expose functions to global scope so inline onclick in Blade works when bundled by Vite
window.tambahKeKeranjang = tambahKeKeranjang;
window.hapusItem = hapusItem;
window.prosesTransaksi = prosesTransaksi;
window.jadwalkanPesanan = jadwalkanPesanan;
window.loadScheduledOrders = loadScheduledOrders;
window.completeScheduledOrder = completeScheduledOrder;

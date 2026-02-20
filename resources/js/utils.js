/**
 * Fungsi utilitas bersama yang dipakai di beberapa modul.
 */

/**
 * Ambil header otentikasi dari localStorage bila tersedia.
 * Digunakan di gudang.js, kasir.js, dan laporan.js
 */
export const getAuthHeaders = () => {
    if (typeof window !== "undefined" && window.getAuthHeaders) {
        return window.getAuthHeaders();
    }
    // Fallback: ambil token langsung dari localStorage
    const token =
        typeof window !== "undefined" && window.localStorage
            ? window.localStorage.getItem("auth_token")
            : null;
    return token ? { Authorization: `Bearer ${token}` } : {};
};

/**
 * Format number as Indonesian Rupiah currency.
 * Used in kasir.js and laporan.js
 */
export const formatRupiah = (angka) =>
    new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    }).format(angka);

/**
 * Format number with Indonesian locale (no currency symbol).
 * Used in gudang.js for price display
 */
export const formatNumber = (angka) =>
    new Intl.NumberFormat("id-ID").format(angka);

/**
 * Periksa apakah tanggal adalah hari ini
 */
export const isToday = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    );
};

/**
 * Periksa apakah tanggal berada dalam 7 hari terakhir
 */
export const isLast7Days = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    date.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);

    const diffTime = Math.abs(today - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays <= 7 && date <= today;
};

/**
 * Periksa apakah tanggal berada pada bulan ini
 */
export const isThisMonth = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    return (
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    );
};

/**
 * Periksa apakah tanggal berada pada bulan lalu
 */
export const isLastMonth = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    let targetMonth = today.getMonth() - 1;
    let targetYear = today.getFullYear();
    if (targetMonth < 0) {
        targetMonth = 11;
        targetYear = targetYear - 1;
    }
    return date.getMonth() === targetMonth && date.getFullYear() === targetYear;
};

/**
 * Periksa apakah tanggal berada pada tahun ini
 */
export const isThisYear = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    return date.getFullYear() === today.getFullYear();
};

/**
 * Validasi apakah string tanggal valid (YYYY-MM-DD).
 */
export const isValidDate = (dateString) => {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
};

/**
 * Format tanggal ke format Indonesia (DD/MM/YYYY).
 */
export const formatDateIndo = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    });
};

/**
 * Helper untuk menampilkan/menyembunyikan pesan error
 */
export const showError = (elementId, message) => {
    const el = document.getElementById(elementId);
    if (el) {
        el.innerText = message;
        el.classList.remove("hidden");
    }
};

export const hideError = (elementId) => {
    const el = document.getElementById(elementId);
    if (el) el.classList.add("hidden");
};

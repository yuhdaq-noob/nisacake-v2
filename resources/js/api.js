import axios from "axios";

// Utilitas API — autentikasi dan helper login/logout

/**
 * Pasang token auth global: simpan ke localStorage dan terapkan ke header axios.
 */
export function setAuthToken(token) {
    if (token) {
        localStorage.setItem("auth_token", token);
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    } else {
        localStorage.removeItem("auth_token");
        delete axios.defaults.headers.common["Authorization"];
    }
}

export function getAuthHeaders() {
    const token = localStorage.getItem("auth_token");
    return token ? { Authorization: `Bearer ${token}` } : {};
}

/**
 * Login melalui API dan simpan token untuk request berikutnya.
 */
export async function apiLogin(username, password) {
    const response = await axios.post("/api/login", { username, password });
    const token = response?.data?.token;
    if (!token) throw new Error("Token tidak diterima dari server");
    setAuthToken(token);
    return response.data;
}

/**
 * Logout melalui API dan hapus token lokal.
 */
export async function apiLogout() {
    try {
        await axios.post("/api/logout");
    } finally {
        setAuthToken(null);
    }
}

// Ekspor helper ke global untuk penggunaan di skrip/templat lama
if (typeof window !== "undefined") {
    window.apiLogin = apiLogin;
    window.apiLogout = apiLogout;
    window.setAuthToken = setAuthToken;
    window.getAuthHeaders = getAuthHeaders;
}

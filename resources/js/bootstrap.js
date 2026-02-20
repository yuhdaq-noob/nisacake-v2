import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Pasang header Bearer dari localStorage jika tersedia
const token = localStorage.getItem("auth_token");
if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}

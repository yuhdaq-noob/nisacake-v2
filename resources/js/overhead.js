import { getAuthHeaders } from "./utils.js";

const overheadApiUrl = "/api/overhead-settings";

document.addEventListener("DOMContentLoaded", async () => {
    if (!document.getElementById("tabelOverhead")) return;
    await loadOverheadSettings();
});

async function loadOverheadSettings() {
    const tbody = document.getElementById("tabelOverhead");
    if (!tbody) return;
    tbody.innerHTML =
        '<tr><td colspan="3" class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-cyan-400"></div></td></tr>';
    try {
        const response = await fetch(overheadApiUrl, {
            headers: getAuthHeaders(),
        });
        if (response.status === 401) {
            alert("Sesi login telah berakhir. Silakan login kembali.");
            window.location.href = "/login";
            return;
        }
        let data = await response.json();
        let items;
        if (Array.isArray(data)) {
            items = data;
        } else if (data.data && Array.isArray(data.data)) {
            items = data.data;
        } else {
            items = [];
        }
        renderOverheadTable(items);
    } catch (error) {
        console.error("Error loading overhead settings:", error);
        tbody.innerHTML =
            '<tr><td colspan="3" class="text-center py-4 text-rose-400"><i class="bi bi-exclamation-triangle-fill"></i><p class="text-xs mt-1">Gagal memuat data overhead.</p></td></tr>';
    }
}

function renderOverheadTable(items) {
    const tbody = document.getElementById("tabelOverhead");
    if (!tbody) return;
    if (!items || items.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="3" class="table-empty-state"><p>Belum ada konfigurasi overhead.</p></td></tr>';
        return;
    }
    let html = "";
    items.forEach((item) => {
        if (!item) return;
        const rawValue = Number(item.value);
        const formattedValue = Number.isFinite(rawValue)
            ? new Intl.NumberFormat("id-ID", {
                  maximumFractionDigits: 2,
              }).format(rawValue)
            : item.value;
        html += `
            <tr class="hover:bg-slate-700/50 transition-colors">
                <td class="text-slate-200 font-medium">${item.label ?? item.key}</td>
                <td class="text-right font-semibold text-white">${formattedValue}</td>
                <td class="text-center text-slate-500 text-xs font-medium uppercase hidden sm:table-cell">${item.unit || "-"}</td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

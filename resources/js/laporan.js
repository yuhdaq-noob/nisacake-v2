/**
 * Modul Laporan dan Dashboard Keuangan
 * Mengelola pemuatan data, filter, chart, dan ekspor laporan.
 */

import "./bootstrap";
import "./api.js";
import {
    formatRupiah,
    isToday,
    isLast7Days,
    isThisMonth,
    isLastMonth,
    isThisYear,
    getAuthHeaders,
} from "./utils.js";
import { handleSessionExpired, showErrorToast } from "./notifications.js";

const apiUrl = "/api/reports";
const overheadApiUrl = "/api/overhead-settings";
let allData = [];
let myChart = null;

// Muat dan inisialisasi data laporan saat halaman dimuat
document.addEventListener("DOMContentLoaded", async () => {
    if (!document.getElementById("myChart")) return;

    // Tampilkan (loading)
    const tbody = document.getElementById("tabelLaporan");
    if (tbody) {
        tbody.innerHTML =
            '<tr><td colspan="7" class="text-center py-6"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-700"></div><p class="mt-2 text-slate-500 text-sm">Memuat data transaksi...</p></td></tr>';
    }

    bindExportDropdown();
    try {
        const response = await fetch(apiUrl, {
            headers: getAuthHeaders(),
        });
        if (response.status === 401) {
            handleSessionExpired();
            return;
        }
        let data = await response.json();

        // Tangani format array atau objek dengan properti .data (format Resource)
        if (!Array.isArray(data)) {
            if (data.data && Array.isArray(data.data)) {
                allData = data.data;
            } else {
                allData = [];
            }
        } else {
            allData = data;
        }

        hitungKartuRekap(allData);
        renderChart(allData);
        renderTable(allData);

        await loadOverheadSettings();

        // Pasang event listener untuk aksi ekspor
        document
            .getElementById("btnExportExcel")
            ?.addEventListener("click", (e) => {
                e.preventDefault();
                exportLaporan("excel");
            });
        document
            .getElementById("btnExportPdf")
            ?.addEventListener("click", (e) => {
                e.preventDefault();
                exportLaporan("pdf");
            });
    } catch (error) {
        console.error("Error loading report data:", error);
        showErrorToast(
            "Gagal memuat data laporan. Silakan refresh untuk mencoba lagi.",
        );
        const tbody = document.getElementById("tabelLaporan");
        if (tbody) {
            tbody.innerHTML =
                '<tr><td colspan="7" class="text-center py-6 text-rose-600"><i class="bi bi-exclamation-triangle-fill text-2xl mb-2"></i><p class="text-sm font-medium">Gagal memuat data laporan. Silakan refresh halaman.</p></td></tr>';
        }
    }
});

async function loadOverheadSettings() {
    const tbody = document.getElementById("tabelOverhead");
    if (!tbody) return;

    // Tampilkan status memuat (loading)
    tbody.innerHTML =
        '<tr><td colspan="3" class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-cyan-700"></div></td></tr>';

    try {
        const response = await fetch(overheadApiUrl, {
            headers: getAuthHeaders(),
        });

        if (response.status === 401) {
            handleSessionExpired();
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
        showErrorToast("Gagal memuat data overhead.");
        tbody.innerHTML =
            '<tr><td colspan="3" class="text-center py-4 text-rose-600"><i class="bi bi-exclamation-triangle-fill"></i><p class="text-xs mt-1">Gagal memuat data overhead.</p></td></tr>';
    }
}

// Filter and search event listeners
document
    .getElementById("filterWaktu")
    ?.addEventListener("change", applyFilters);
document.getElementById("searchInput")?.addEventListener("input", applyFilters);

function applyFilters() {
    const timeFilter = document.getElementById("filterWaktu").value;
    const searchText = document
        .getElementById("searchInput")
        .value.toLowerCase();

    const filteredData = allData.filter((item) => {
        // Check date filter
        let dateMatch = true;
        const itemDate = item.date;
        if (timeFilter === "today") dateMatch = isToday(itemDate);
        else if (timeFilter === "last7") dateMatch = isLast7Days(itemDate);
        else if (timeFilter === "month") dateMatch = isThisMonth(itemDate);
        else if (timeFilter === "last_month") dateMatch = isLastMonth(itemDate);
        else if (timeFilter === "year") dateMatch = isThisYear(itemDate);

        // Check text search filter (customer name or product)
        const textMatch =
            (item.customer || "").toLowerCase().includes(searchText) ||
            (item.products || "").toLowerCase().includes(searchText);

        return dateMatch && textMatch;
    });

    renderTable(filteredData);
    renderChart(filteredData);
}

function hitungKartuRekap(data) {
    let omzetToday = 0;
    let profitToday = 0;
    let profitMonth = 0;

    data.forEach((item) => {
        if (isToday(item.date)) {
            omzetToday += Number(item.total_omzet) || 0;
            profitToday += Number(item.profit) || 0;
        }
        if (isThisMonth(item.date)) {
            profitMonth += Number(item.profit) || 0;
        }
    });

    document.getElementById("cardOmzetToday").innerText =
        formatRupiah(omzetToday);
    document.getElementById("cardProfitToday").innerText =
        formatRupiah(profitToday);
    document.getElementById("cardProfitMonth").innerText =
        formatRupiah(profitMonth);
}

function renderTable(data) {
    const tbody = document.getElementById("tabelLaporan");
    let html = "";
    let tOmzet = 0,
        tHPP = 0,
        tProfit = 0;

    if (!data || data.length === 0) {
        tbody.innerHTML =
            '<tr><td colspan="7" class="table-empty-state"><p>Tidak ada data transaksi pada periode ini.</p></td></tr>';
        document.getElementById("tableTotalOmzet").innerText = "Rp 0";
        document.getElementById("tableTotalHPP").innerText = "Rp 0";
        document.getElementById("tableTotalProfit").innerText = "Rp 0";
        return;
    }

    data.forEach((order, index) => {
        const omzetVal = Number(order.total_omzet) || 0;
        const hppVal = Number(order.total_hpp) || 0;
        const profitVal = Number(order.profit) || 0;

        tOmzet += omzetVal;
        tHPP += hppVal;
        tProfit += profitVal;

        // Determine profit styling
        const profitClass =
            profitVal >= 0 ? "text-emerald-700" : "text-rose-700";

        html += `
            <tr class="hover:bg-slate-50 transition-colors" data-order-id="${order.id}">
                <td class="font-semibold text-slate-900">#${order.id}</td>
                <td class="text-slate-700 text-xs sm:text-sm">${order.date}</td>
                <td class="text-slate-800 hidden sm:table-cell">${order.customer || "-"}</td>
                <td class="text-slate-600 text-xs max-w-[150px] truncate" title="${order.products}">${order.products}</td>
                <td class="text-right font-medium text-slate-900">${formatRupiah(omzetVal)}</td>
                <td class="text-right font-medium text-slate-700 hidden sm:table-cell">${formatRupiah(hppVal)}</td>
                <td class="text-right font-bold ${profitClass}">${formatRupiah(profitVal)}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    document.getElementById("tableTotalOmzet").innerText = formatRupiah(tOmzet);
    document.getElementById("tableTotalHPP").innerText = formatRupiah(tHPP);
    document.getElementById("tableTotalProfit").innerText =
        formatRupiah(tProfit);
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
    items.forEach((item, index) => {
        if (!item) return;

        const rawValue = Number(item.value);
        const formattedValue = Number.isFinite(rawValue)
            ? new Intl.NumberFormat("id-ID", {
                  maximumFractionDigits: 2,
              }).format(rawValue)
            : item.value;

        html += `
            <tr class="hover:bg-slate-50 transition-colors">
                <td class="text-slate-800 font-medium">${item.label ?? item.key}</td>
                <td class="text-right font-semibold text-slate-900">${formattedValue}</td>
                <td class="text-center text-slate-500 text-xs font-medium uppercase hidden sm:table-cell">${item.unit || "-"}</td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
}

function bindExportDropdown() {
    const trigger = document.querySelector("[data-dropdown-trigger]");
    const menu = document.querySelector("[data-dropdown-menu]");
    if (!trigger || !menu) return;

    const hide = () => menu.classList.add("hidden");

    trigger.addEventListener("click", (e) => {
        e.stopPropagation();
        menu.classList.toggle("hidden");
    });

    document.addEventListener("click", (e) => {
        if (!menu.contains(e.target) && !trigger.contains(e.target)) hide();
    });
}

function renderChart(data) {
    const canvas = document.getElementById("myChart");
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    if (!ctx) return;

    // Detect screen size for responsive configuration
    const isMobile = window.innerWidth < 768;
    const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;

    let dailyGroups = {};
    data.forEach((item) => {
        if (!item || !item.date) return;
        const d = new Date(item.date);
        if (isNaN(d)) return;

        const dateKey = d.toISOString().slice(0, 10); // YYYY-MM-DD
        const label = d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: isMobile ? "short" : "short",
            year: isMobile ? undefined : "numeric",
        });

        if (!dailyGroups[dateKey]) {
            dailyGroups[dateKey] = {
                omzet: 0,
                profit: 0,
                rawDate: d,
                label: label,
            };
        }

        dailyGroups[dateKey].omzet += Number(item.total_omzet) || 0;
        dailyGroups[dateKey].profit += Number(item.profit) || 0;
    });

    let sortedKeys = Object.keys(dailyGroups).sort(
        (a, b) => dailyGroups[a].rawDate - dailyGroups[b].rawDate,
    );

    const labels = sortedKeys.map((k) => dailyGroups[k].label);
    const dataOmzet = sortedKeys.map((key) => dailyGroups[key].omzet);
    const dataProfit = sortedKeys.map((key) => dailyGroups[key].profit);

    if (myChart) {
        try {
            myChart.destroy();
        } catch (e) {
            // ignore
        }
        myChart = null;
    }

    // Responsive configuration based on device
    const chartConfig = {
        type: "line",
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Omzet",
                    data: dataOmzet,
                    borderColor: "#0891b2",
                    backgroundColor: isMobile
                        ? "rgba(8, 145, 178, 0.05)"
                        : "rgba(8, 145, 178, 0.1)",
                    borderWidth: isMobile ? 2 : 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: isMobile ? 0 : 0,
                    pointHoverRadius: isMobile ? 5 : 6,
                    pointHitRadius: isMobile ? 20 : 10,
                },
                {
                    label: "Profit",
                    data: dataProfit,
                    borderColor: "#0e7490",
                    backgroundColor: isMobile
                        ? "rgba(14, 116, 144, 0.03)"
                        : "rgba(14, 116, 144, 0.08)",
                    borderWidth: isMobile ? 2 : 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: isMobile ? 0 : 3,
                    pointBackgroundColor: "#fff",
                    pointBorderColor: "#0e7490",
                    pointBorderWidth: 2,
                    pointHoverRadius: isMobile ? 5 : 6,
                    pointHitRadius: isMobile ? 20 : 10,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            devicePixelRatio: window.devicePixelRatio || 1,
            plugins: {
                legend: {
                    display: true,
                    position: isMobile ? "bottom" : "top",
                    align: isMobile ? "center" : "end",
                    labels: {
                        boxWidth: isMobile ? 12 : 10,
                        padding: isMobile ? 12 : 10,
                        usePointStyle: true,
                        font: {
                            size: isMobile ? 11 : 12,
                            family: "'Inter', 'system-ui', sans-serif",
                            weight: "500",
                        },
                        color: "#334155",
                    },
                },
                tooltip: {
                    enabled: true,
                    mode: "index",
                    intersect: false,
                    backgroundColor: "rgba(255, 255, 255, 0.97)",
                    titleColor: "#1e293b",
                    bodyColor: "#475569",
                    borderColor: "#e2e8f0",
                    borderWidth: 1,
                    padding: isMobile ? 10 : 12,
                    boxPadding: 6,
                    titleFont: {
                        size: isMobile ? 12 : 13,
                        weight: "600",
                    },
                    bodyFont: {
                        size: isMobile ? 11 : 12,
                    },
                    displayColors: true,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || "";
                            if (label) {
                                label += ": ";
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat("id-ID", {
                                    style: "currency",
                                    currency: "IDR",
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0,
                                }).format(context.parsed.y);
                            }
                            return label;
                        },
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "#f1f5f9",
                        drawBorder: false,
                        lineWidth: 1,
                    },
                    border: {
                        display: false,
                    },
                    ticks: {
                        font: {
                            size: isMobile ? 9 : 10,
                            family: "'Inter', 'system-ui', sans-serif",
                        },
                        color: "#64748b",
                        padding: isMobile ? 4 : 8,
                        maxTicksLimit: isMobile ? 5 : 8,
                        callback: function (value) {
                            if (value >= 1000000) {
                                return isMobile
                                    ? (value / 1000000).toFixed(1) + "jt"
                                    : "Rp " +
                                          (value / 1000000).toFixed(1) +
                                          "jt";
                            }
                            if (value >= 1000) {
                                return isMobile
                                    ? (value / 1000).toFixed(0) + "rb"
                                    : "Rp " + (value / 1000).toFixed(0) + "rb";
                            }
                            return isMobile ? value : "Rp " + value;
                        },
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                    border: {
                        display: false,
                    },
                    ticks: {
                        font: {
                            size: isMobile ? 9 : 10,
                            family: "'Inter', 'system-ui', sans-serif",
                        },
                        color: "#64748b",
                        maxRotation: isMobile ? 45 : 0,
                        minRotation: isMobile ? 45 : 0,
                        padding: isMobile ? 4 : 8,
                        autoSkip: true,
                        autoSkipPadding: isMobile ? 20 : 10,
                        maxTicksLimit: isMobile ? 6 : 12,
                    },
                },
            },
            interaction: {
                mode: isMobile ? "nearest" : "index",
                axis: "x",
                intersect: false,
            },
            hover: {
                mode: isMobile ? "nearest" : "index",
                intersect: false,
            },
            animation: {
                duration: isMobile ? 400 : 750,
                easing: "easeInOutQuart",
            },
        },
    };

    myChart = new Chart(ctx, chartConfig);

    // Tangani perubahan ukuran jendela untuk pembaruan responsif
    let resizeTimer;
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (myChart) {
                const wasMobile =
                    myChart.options.plugins.legend.position === "bottom";
                const nowMobile = window.innerWidth < 768;

                // Hanya lakukan re-render jika status mobile berubah
                if (wasMobile !== nowMobile) {
                    renderChart(data);
                }
            }
        }, 250);
    });
}

function exportLaporan(format) {
    const timeFilter = document.getElementById("filterWaktu").value;
    const searchText = document.getElementById("searchInput").value;

    // Bangun parameter query dengan pengaturan filter saat ini
    const params = new URLSearchParams({
        format: format,
        period: timeFilter,
        search: searchText,
    });

    window.location.href = `/laporan/export?${params.toString()}`;
}

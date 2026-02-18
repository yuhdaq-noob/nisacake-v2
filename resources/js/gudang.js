/**
 * Inventory Management Module
 *
 * SOLID Principles Applied:
 * 1. Single Responsibility Principle (SRP)
 *    - Each function has one clear purpose (loadMaterials, loadHistory, etc.)
 *    - Separate utility functions for formatting and calculations
 *
 * 2. Open/Closed Principle (OCP)
 *    - Status determination is extensible without modifying core logic
 *    - Price resolution uses configuration objects
 *
 * 3. Liskov Substitution Principle (LSP)
 *    - API response handling works with both array and Resource format
 *
 * 4. Interface Segregation Principle (ISP)
 *    - Functions accept only the parameters they need
 *    - Utility functions are small and focused
 *
 * 5. Dependency Inversion Principle (DIP)
 *    - Depends on abstractions (API endpoints) not concrete implementations
 *    - Uses utility functions from utils.js and ui.js
 *
 * Best Practices:
 * - Async/await for better readability
 * - Error handling with try/catch
 * - Consistent naming conventions (camelCase)
 * - Documentation with JSDoc comments
 * - Separation of concerns (data fetching, rendering, event handling)
 * - DRY principle (Don't Repeat Yourself)
 */

import "./bootstrap";
import "./api.js";
import { getAuthHeaders, formatNumber } from "./utils.js";
import { closeModal } from "./ui.js";

const apiMaterials = "/api/materials";
const apiHistory = "/api/stocks/history";
const apiAddStock = "/api/stocks/add";
const apiMaterialPrice = "/api/materials";
const apiPriceHistory = "/api/materials/price-history";

const materialMap = new Map();

const BASE_UNIT_CONFIG = {
    gram: { unit: "kg", factor: 1000 },
    g: { unit: "kg", factor: 1000 },
    ml: { unit: "liter", factor: 1000 },
};

function getBaseUnitConfig(unitValue) {
    const key = (unitValue || "").trim().toLowerCase();
    return BASE_UNIT_CONFIG[key] || null;
}

function parseRupiahInput(value) {
    if (!value) return NaN;

    let cleaned = value.toString().trim();

    cleaned = cleaned.replace(/\s+/g, "");

    if (cleaned.includes(",") && cleaned.includes(".")) {
        cleaned = cleaned.replace(/\./g, "").replace(",", ".");
    } else {
        if (cleaned.includes(".") && !cleaned.includes(",")) {
            cleaned = cleaned.replace(/\./g, "");
        }
        if (cleaned.includes(",") && !cleaned.includes(".")) {
            cleaned = cleaned.replace(",", ".");
        }
    }

    return Number(cleaned);
}

function resolveDisplayPrice(mat) {
    const unitValue = (mat.unit || "").trim();
    const baseUnitValue = (mat.base_unit || "").trim();
    const hasBasePrice =
        mat.price_per_base_unit !== null &&
        mat.price_per_base_unit !== undefined;
    const baseUnitConfig = getBaseUnitConfig(unitValue);
    const shouldDeriveBase =
        !hasBasePrice ||
        baseUnitValue === "" ||
        baseUnitValue.toLowerCase() === unitValue.toLowerCase();

    let displayUnit = baseUnitValue || unitValue;
    let displayPrice = hasBasePrice
        ? mat.price_per_base_unit
        : mat.price_per_unit;

    if (shouldDeriveBase && baseUnitConfig) {
        displayUnit = baseUnitConfig.unit;
        displayPrice = (mat.price_per_unit || 0) * baseUnitConfig.factor;
    }

    return { displayUnit, displayPrice };
}

function getEditConfig(mat) {
    const unitValue = (mat.unit || "").trim();
    if (!unitValue) {
        return { isEditable: false };
    }
    const { displayUnit, displayPrice } = resolveDisplayPrice(mat);

    return {
        isEditable: true,
        basePrice: Number(displayPrice ?? 0),
        editUnit: displayUnit || unitValue,
    };
}

function buildPriceCellHtml(mat) {
    const { displayUnit, displayPrice } = resolveDisplayPrice(mat);
    const editConfig = getEditConfig(mat);
    const editButton = editConfig.isEditable
        ? `<button type="button" class="btn-edit-price inline-flex items-center gap-1 text-xs font-semibold text-amber-800 hover:text-amber-900" data-material-id="${mat.id}" title="Ubah harga"><i class="bi bi-pencil-square"></i><span>Ubah</span></button>`
        : "";

    return `
        <span class="price-display-group">
            <span class="price-display">Rp ${formatNumber(displayPrice)}/${displayUnit}</span>
            ${editButton}
        </span>
    `;
}

// Load inventory data on page initialization
document.addEventListener("DOMContentLoaded", () => {
    if (!document.getElementById("tabelStok")) return;
    loadMaterials();
    loadHistory();
    loadPriceHistory();
    attachPriceEditHandler();
});

/**
 * Load material inventory table and restock modal dropdown.
 * Implements SRP by focusing solely on data loading and rendering.
 */
async function loadMaterials() {
    const tbody = document.getElementById("tabelStok");
    const selectBahan = document.getElementById("selectBahan");

    // Show loading state
    if (tbody) {
        tbody.innerHTML =
            '<tr><td colspan="5" class="text-center py-6"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-cyan-700"></div><p class="mt-2 text-slate-500 text-sm">Memuat data...</p></td></tr>';
    }

    try {
        const response = await fetch(apiMaterials, {
            headers: {
                ...getAuthHeaders(),
            },
        });
        if (response.status === 401) {
            alert("Sesi login telah berakhir. Silakan login kembali.");
            window.location.href = "/login";
            return;
        }
        let materials = await response.json();

        // Handle both array and object with .data property (Resource format)
        if (!Array.isArray(materials)) {
            if (materials.data && Array.isArray(materials.data)) {
                materials = materials.data;
            } else {
                materials = [];
            }
        }

        let htmlTabel = "";
        let htmlOption =
            '<option value="" disabled selected>-- Select Material --</option>';

        materialMap.clear();

        materials.forEach((mat) => {
            materialMap.set(String(mat.id), mat);

            // Determine status indicator using utility classes (SOLID - Open/Closed Principle)
            let statusClass = "status-badge status-badge--success";
            let statusText = "Optimal";
            let statusIcon = '<i class="bi bi-check-circle-fill"></i>';

            if (mat.current_stock < mat.min_stock_level) {
                statusClass = "status-badge status-badge--danger";
                statusText = "Critical";
                statusIcon = '<i class="bi bi-x-circle-fill"></i>';
            } else if (mat.current_stock < mat.min_stock_level * 2) {
                statusClass = "status-badge status-badge--warning";
                statusText = "Low";
                statusIcon = '<i class="bi bi-exclamation-circle-fill"></i>';
            }

            htmlTabel += `
                <tr data-material-id="${mat.id}" class="hover:bg-slate-50 transition-colors">
                    <td class="font-semibold text-slate-900">${mat.name}</td>
                    <td class="col-price hidden sm:table-cell" data-material-id="${mat.id}">${buildPriceCellHtml(mat)}</td>
                    <td class="text-right font-medium text-slate-700">${formatNumber(mat.current_stock)}</td>
                    <td class="uppercase text-slate-500 font-medium hidden md:table-cell">${mat.unit}</td>
                    <td class="text-center">
                        <span class="${statusClass}">${statusIcon} ${statusText}</span>
                    </td>
                </tr>
            `;

            htmlOption += `<option value="${mat.id}">${mat.name} (${mat.unit})</option>`;
        });

        // Render data (check if elements exist before setting innerHTML)
        if (tbody) {
            tbody.innerHTML =
                htmlTabel ||
                '<tr><td colspan="5" class="table-empty-state"><p>Tidak ada data bahan.</p></td></tr>';
        }
        if (selectBahan) {
            selectBahan.innerHTML = htmlOption;
        }
    } catch (error) {
        console.error("Error loading materials:", error);
        // Show user-friendly error message
        if (tbody) {
            tbody.innerHTML =
                '<tr><td colspan="5" class="text-center py-6 text-rose-600"><i class="bi bi-exclamation-triangle-fill text-2xl mb-2"></i><p class="text-sm font-medium">Gagal memuat data. Silakan refresh halaman.</p></td></tr>';
        }
    }
}

/**
 * Load stock transaction history log.
 */
async function loadHistory() {
    try {
        const response = await fetch(apiHistory, {
            headers: {
                ...getAuthHeaders(),
            },
        });
        if (response.status === 401) {
            alert("Sesi login telah berakhir. Silakan login kembali.");
            window.location.href = "/login";
            return;
        }
        let logs = await response.json();

        // Handle both array and object with .data property (Resource format)
        if (!Array.isArray(logs)) {
            if (logs.data && Array.isArray(logs.data)) {
                logs = logs.data;
            } else {
                logs = [];
            }
        }

        const list = document.getElementById("listLog");

        if (logs.length === 0) {
            list.innerHTML =
                '<li class="px-5 py-4 text-center text-slate-500 text-sm">Tidak ada riwayat transaksi.</li>';
            return;
        }

        let html = "";
        logs.forEach((log) => {
            const isIn = log.type === "in";
            const textClass = isIn ? "text-emerald-600" : "text-rose-600";
            const sign = isIn ? "+" : "-";
            const badgeText = isIn ? "Masuk" : "Keluar";
            const badgeColor = isIn
                ? "bg-emerald-50 text-emerald-700 border-emerald-100"
                : "bg-rose-50 text-rose-700 border-rose-100";

            const date = new Date(log.created_at).toLocaleString("id-ID", {
                day: "numeric",
                month: "short",
                hour: "2-digit",
                minute: "2-digit",
            });

            const materialName =
                (log.material && (log.material.name || log.material)) || "-";
            const description = log.description || "-";

            html += `
                <li class="group px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                    <div class="flex-1 min-w-0 flex items-start gap-3">
                        <span class="mt-1 inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 group-hover:bg-amber-50 group-hover:text-amber-700">
                            ${isIn ? '<i class="bi bi-arrow-down-circle-fill"></i>' : '<i class="bi bi-arrow-up-circle-fill"></i>'}
                        </span>
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900 truncate">${materialName}</div>
                            <p class="mt-0.5 text-xs text-slate-500 truncate">${description}</p>
                        </div>
                    </div>
                    <div class="shrink-0 text-right text-xs">
                        <span class="inline-flex items-center justify-center rounded-full border px-2 py-0.5 text-[0.68rem] font-semibold uppercase tracking-wide ${badgeColor}">${badgeText}</span>
                        <div class="mt-1 text-sm font-bold ${textClass}">${sign}${log.amount}</div>
                        <div class="mt-0.5 text-slate-400">${date}</div>
                    </div>
                </li>
            `;
        });
        list.innerHTML = html;
    } catch (error) {
        console.error(error);
    }
}

/**
 * Handle restock form submission.
 */
const formRestock = document.getElementById("formRestock");
if (formRestock) {
    formRestock.addEventListener("submit", async (e) => {
        e.preventDefault();

        const data = {
            material_id: document.getElementById("selectBahan").value,
            amount: document.getElementById("inputJumlah").value,
            description:
                document.getElementById("inputKet").value || "Manual Restock",
        };

        try {
            const response = await fetch(apiAddStock, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    ...getAuthHeaders(),
                },
                body: JSON.stringify(data),
            });
            if (response.status === 401) {
                alert("Sesi login telah berakhir. Silakan login kembali.");
                window.location.href = "/login";
                return;
            }

            const result = await response.json();

            if (response.ok) {
                alert("Stock added successfully!");
                formRestock.reset();
                closeModal("modalRestock");
                loadMaterials();
                loadHistory();
            } else {
                alert(
                    "Failed to add stock: " +
                        (result.message || JSON.stringify(result)),
                );
            }
        } catch (error) {
            console.error(error);
            alert(
                "System error occurred. Please check the console for details.",
            );
        }
    });
}

function attachPriceEditHandler() {
    const tableBody = document.getElementById("tabelStok");
    if (!tableBody || window.priceEditHandlerAttached) {
        return;
    }

    window.priceEditHandlerAttached = true;

    tableBody.addEventListener("click", async (event) => {
        const editButton = event.target.closest(".btn-edit-price");
        const saveButton = event.target.closest(".btn-save-price");
        const cancelButton = event.target.closest(".btn-cancel-price");

        if (editButton) {
            const materialId = editButton.getAttribute("data-material-id");
            const cell = editButton.closest(".col-price");
            const material = materialMap.get(String(materialId));
            const editConfig = material ? getEditConfig(material) : null;

            if (!cell || !material || !editConfig || !editConfig.isEditable) {
                return;
            }

            cell.innerHTML = `
                <div class="flex items-center gap-2 flex-wrap">
                    <div class="flex items-center rounded-lg border border-slate-200 overflow-hidden">
                        <span class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100">Rp</span>
                        <input type="number" class="price-input w-24 px-3 py-2 text-sm focus:outline-none" min="0" step="0.01" value="${editConfig.basePrice}">
                        <span class="px-3 py-2 text-xs font-semibold text-slate-600 bg-slate-100">/${editConfig.editUnit}</span>
                    </div>
                    <button type="button" class="btn-save-price px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-semibold" data-material-id="${materialId}" title="Simpan">
                        Simpan
                    </button>
                    <button type="button" class="btn-cancel-price px-3 py-2 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700" data-material-id="${materialId}" title="Batal">
                        Batal
                    </button>
                    <p class="w-full text-[11px] text-slate-400 mt-1">Contoh input: 35000 atau 35.000 (keduanya berarti Rp 35.000/${editConfig.editUnit}).</p>
                </div>
            `;

            return;
        }

        if (cancelButton) {
            const materialId = cancelButton.getAttribute("data-material-id");
            const cell = cancelButton.closest(".col-price");
            const material = materialMap.get(String(materialId));

            if (cell && material) {
                cell.innerHTML = buildPriceCellHtml(material);
            }

            return;
        }

        if (saveButton) {
            const materialId = saveButton.getAttribute("data-material-id");
            const cell = saveButton.closest(".col-price");
            const input = cell ? cell.querySelector(".price-input") : null;

            if (!cell || !input) {
                return;
            }

            const rawValue = input.value.trim();
            const pricePerBaseUnit = parseRupiahInput(rawValue);

            if (
                !rawValue ||
                Number.isNaN(pricePerBaseUnit) ||
                pricePerBaseUnit <= 0
            ) {
                alert("Harga harus berupa angka dan lebih dari 0.");
                return;
            }

            input.disabled = true;
            saveButton.disabled = true;

            try {
                const response = await fetch(
                    `${apiMaterialPrice}/${materialId}/price`,
                    {
                        method: "PATCH",
                        headers: {
                            "Content-Type": "application/json",
                            ...getAuthHeaders(),
                        },
                        body: JSON.stringify({
                            price_per_base_unit: pricePerBaseUnit,
                        }),
                    },
                );
                if (response.status === 401) {
                    alert("Sesi login telah berakhir. Silakan login kembali.");
                    window.location.href = "/login";
                    return;
                }

                const result = await response.json();

                if (!response.ok) {
                    const message =
                        result.message || "Gagal memperbarui harga.";
                    alert(message);
                    const material = materialMap.get(String(materialId));
                    if (material) {
                        cell.innerHTML = buildPriceCellHtml(material);
                    }
                    return;
                }

                const updatedMaterial = result.data || result;
                materialMap.set(String(materialId), updatedMaterial);
                cell.innerHTML = buildPriceCellHtml(updatedMaterial);
                loadPriceHistory();
            } catch (error) {
                console.error(error);
                alert("Terjadi kesalahan saat memperbarui harga.");
                const material = materialMap.get(String(materialId));
                if (material) {
                    cell.innerHTML = buildPriceCellHtml(material);
                }
            }
        }
    });
}

async function loadPriceHistory() {
    const list = document.getElementById("listPriceLog");
    if (!list) {
        return;
    }

    try {
        const response = await fetch(apiPriceHistory, {
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
            list.innerHTML =
                '<li class="px-5 py-4 text-center text-slate-500 text-sm">Riwayat harga belum tersedia.</li>';
            return;
        }

        let logs = await response.json();

        if (!Array.isArray(logs)) {
            if (logs.data && Array.isArray(logs.data)) {
                logs = logs.data;
            } else {
                logs = [];
            }
        }

        if (logs.length === 0) {
            list.innerHTML =
                '<li class="px-5 py-4 text-center text-slate-500 text-sm">Belum ada perubahan harga.</li>';
            return;
        }

        let html = "";
        logs.forEach((log) => {
            const baseUnit = log.base_unit || "";
            const oldPrice = log.old_price_per_base_unit ?? 0;
            const newPrice = log.new_price_per_base_unit ?? 0;
            const date = new Date(log.created_at).toLocaleString("id-ID", {
                day: "numeric",
                month: "short",
                hour: "2-digit",
                minute: "2-digit",
            });

            const name = log.material?.name || "-";

            html += `
                <li class="group px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-50/80 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 truncate">${name}</div>
                        <p class="mt-0.5 text-xs text-slate-500">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[0.68rem] font-semibold text-slate-700 mr-1">Sebelum</span>
                            Rp ${formatNumber(oldPrice)}/${baseUnit}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[0.68rem] font-semibold text-emerald-700 mr-1">Sesudah</span>
                            Rp ${formatNumber(newPrice)}/${baseUnit}
                        </p>
                    </div>
                    <div class="shrink-0 text-right text-xs text-slate-400">${date}</div>
                </li>
            `;
        });

        list.innerHTML = html;
    } catch (error) {
        console.error(error);
        list.innerHTML =
            '<li class="list-group-item text-center text-danger">Gagal memuat riwayat harga.</li>';
    }
}

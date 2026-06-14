@extends('layouts.app')
@php($title = 'Laporan Keuangan')
@php($active = 'laporan')

@section('content')
<br>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Laporan</p>
            <h2 class="text-2xl font-bold text-white">Dashboard Keuangan</h2>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-3 lg:gap-6 mb-6">
        <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-300 flex items-center gap-2"><i class="bi bi-bar-chart-line-fill text-cyan-400"></i> Omzet Hari Ini</p>
            </div>
            <div class="p-4">
                <h3 class="text-3xl font-bold text-cyan-400" id="cardOmzetToday">Rp 0</h3>
            </div>
        </div>
        <div class="bg-gradient-to-br from-cyan-700 to-blue-800 rounded-2xl shadow-lg border border-cyan-600/50 overflow-hidden">
            <div class="px-5 py-4 border-b border-cyan-500/30">
                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-cyan-100 flex items-center gap-2"><i class="bi bi-currency-dollar"></i> Profit Hari Ini</p>
            </div>
            <div class="p-4">
                <h3 class="text-3xl font-bold text-white" id="cardProfitToday">Rp 0</h3>
            </div>
        </div>
        <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-700">
                <p class="text-xs font-semibold uppercase tracking-[0.1em] text-slate-300 flex items-center gap-2"><i class="bi bi-graph-up-arrow text-green-400"></i> Profit Bulan Ini</p>
            </div>
            <div class="p-4">
                <h3 class="text-3xl font-bold text-green-400" id="cardProfitMonth">Rp 0</h3>
            </div>
        </div>
    </div>

    <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 mb-6 overflow-hidden flex flex-col">
        <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between sticky top-0 bg-slate-800 z-10">
            <p class="text-sm font-semibold text-white flex items-center gap-2"><i class="bi bi-bar-chart-line-fill text-cyan-400"></i> Grafik Transaksi</p>
        </div>
        <div class="p-4 sm:p-5">
            <div class="relative h-[280px] sm:h-[320px] lg:h-[360px] w-full">
                <canvas id="myChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>

    <!-- Main Table - Rincian Transaksi -->
    <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 overflow-hidden flex flex-col mb-6">
        <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700 sticky top-0 bg-slate-800 z-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <h5 class="text-sm sm:text-base font-semibold text-white flex items-center gap-2"><i class="bi bi-credit-card-2-front-fill text-cyan-400"></i> Rincian Transaksi</h5>
                <div class="flex flex-wrap gap-2 items-center">
                    <div class="relative">
                        <button type="button" class="dropdown-trigger" data-dropdown-trigger aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-download"></i>
                            <span>Export</span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right hidden" data-dropdown-menu role="menu">
                            <a class="dropdown-item" href="#" id="btnExportExcel" role="menuitem">
                                <i class="bi bi-file-earmark-excel text-emerald-400"></i>
                                <span>Excel (.xlsx)</span>
                            </a>
                            <a class="dropdown-item" href="#" id="btnExportPdf" role="menuitem">
                                <i class="bi bi-file-earmark-pdf text-red-400"></i>
                                <span>PDF (.pdf)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2 items-stretch">
                <input
                type="text"
                id="searchInput"
                class="w-full rounded-lg border
                border-slate-600
                bg-slate-700/50 px-3 py-2 text-xs sm:text-sm font-medium
                text-slate-100
                focus:border-cyan-500 focus:ring-2
                focus:ring-cyan-500/20 transition-colors
                placeholder-slate-500"
                placeholder="Cari pelanggan / produk...">
                <div class="relative">
                    <select id="filterWaktu" class="filter-select w-full" aria-label="Filter waktu">
                        <option value="all">Semua Waktu</option>
                        <option value="today">Hari Ini</option>
                        <option value="last7">7 Hari Terakhir</option>
                        <option value="month">Bulan Ini</option>
                        <option value="last_month">Bulan Lalu</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="flex-1 overflow-x-auto">
            <div class="table-scroll-container max-h-[500px] overflow-y-auto">
                <table class="w-full table-basic text-xs sm:text-sm">
                    <thead class="sticky top-0">
                        <tr>
                            <th class="text-left" style="min-width: 50px;">ID</th>
                            <th class="text-left" style="min-width: 70px;">Tanggal</th>
                            <th class="text-left hidden sm:table-cell" style="min-width: 90px;">Pelanggan</th>
                            <th class="text-left" style="min-width: 80px;">Produk</th>
                            <th class="text-right" style="min-width: 70px;">Omzet</th>
                            <th class="text-right hidden sm:table-cell" style="min-width: 70px;">HPP</th>
                            <th class="text-right" style="min-width: 70px;">Profit</th>
                        </tr>
                    </thead>
                    <tbody id="tabelLaporan">
                        <tr><td colspan="7" class="text-center py-6 text-slate-400">Memuat data...</td></tr>
                    </tbody>
                    <tfoot class="sticky bottom-0 z-10">
                        <tr class="font-bold border-t border-slate-700 bg-slate-800/95 backdrop-blur-sm">
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-slate-100" style="min-width: 50px;"></td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-slate-100" style="min-width: 70px;"></td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-slate-100 hidden sm:table-cell" style="min-width: 90px;"></td>
                            <td class="px-3 sm:px-4 py-2 sm:py-3 text-left text-slate-100" style="min-width: 80px;">TOTAL:</td>
                            <td id="tableTotalOmzet" class="px-3 sm:px-4 py-2 sm:py-3 text-right text-cyan-400 font-bold" style="min-width: 70px;">Rp 0</td>
                            <td id="tableTotalHPP" class="px-3 sm:px-4 py-2 sm:py-3 text-right text-cyan-400 font-bold hidden sm:table-cell" style="min-width: 70px;">Rp 0</td>
                            <td id="tableTotalProfit" class="px-3 sm:px-4 py-2 sm:py-3 text-right text-green-400 font-bold" style="min-width: 70px;">Rp 0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/js/laporan.js'])
@endsection

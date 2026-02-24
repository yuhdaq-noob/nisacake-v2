@extends('layouts.app')
@php($title = 'Kasir')
@php($active = 'kasir')

@section('content')
    <div class="grid gap-4 lg:gap-6 grid-cols-1 lg:grid-cols-3">
        <!-- Input Section -->
        <div class="lg:col-span-1">
            <div id="orderInputCard" class="bg-slate-800 shadow-lg rounded-xl border border-slate-700 overflow-hidden sticky top-4">
                <div class="px-3 sm:px-6 pt-3 sm:pt-5 pb-3 border-b border-slate-700 bg-gradient-to-r from-slate-800 to-slate-700/50">
                    <p class="text-[0.65rem] sm:text-xs uppercase tracking-widest font-bold text-cyan-400">Pesanan Baru</p>
                    <h3 class="text-base sm:text-xl font-bold text-white mt-1 sm:mt-2">Input Produk</h3>
                </div>
                <div class="px-3 sm:px-6 py-3 sm:py-4 space-y-3 sm:space-y-4">
                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-xs sm:text-sm font-semibold text-slate-300">Nama Pelanggan</label>
                        <input type="text" id="customer_name" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 sm:px-4 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all placeholder-slate-500 text-sm font-medium min-h-[44px]" placeholder="Ketik nama pelanggan..." required>
                        <div id="error_customer_name" class="hidden text-xs text-red-400 font-medium"></div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-xs sm:text-sm font-semibold text-slate-300">Pilih Produk</label>
                        <input class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 sm:px-4 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all placeholder-slate-500 text-sm font-medium min-h-[44px]" list="product_list" id="product_input" placeholder="Cari produk..." autocomplete="off">
                        <datalist id="product_list"></datalist>
                        <div id="error_product_input" class="hidden text-xs text-red-400 font-medium"></div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-2 pt-0.5 sm:pt-1">
                        <label class="text-xs sm:text-sm font-semibold text-slate-300">Jumlah</label>
                        <div class="flex gap-2">
                            <input type="number" id="quantity" class="flex-1 rounded-lg border border-slate-600 bg-slate-700/50 px-3 sm:px-4 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all text-center font-semibold text-slate-100 min-h-[44px]" value="1" min="1">
                            <button class="btn-prim" type="button" onclick="tambahKeKeranjang()" style="padding: 0.75rem; min-width: 44px; height: 44px;">
                                <i class="bi bi-plus-lg text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1.5 sm:space-y-2">
                        <label class="text-xs sm:text-sm font-semibold text-slate-300">Tanggal Pesanan</label>
                        <input type="datetime-local" id="order_date" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 px-3 sm:px-4 py-2.5 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-all text-sm font-medium text-slate-100 min-h-[44px]" required value="" step="900">
                        <div id="error_order_date" class="hidden text-xs text-red-400 font-medium"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="lg:col-span-2">
            <div id="cartCard" class="bg-slate-800 shadow-lg rounded-xl border border-slate-700 overflow-hidden flex flex-col h-fit">
                <div class="px-3 sm:px-6 pt-3 sm:pt-5 pb-3 border-b border-slate-700 bg-gradient-to-r from-slate-800 to-slate-700/50">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-[0.65rem] sm:text-xs uppercase tracking-widest font-bold text-cyan-400">Keranjang</p>
                            <h3 class="text-base sm:text-xl font-bold text-white mt-1 sm:mt-2">Ringkasan belanja</h3>
                        </div>
                        <div class="sm:text-right">
                            <p class="text-xs text-slate-400 font-medium">Total</p>
                            <p id="totalDisplay" class="text-xl sm:text-2xl font-bold text-cyan-400 mt-0.5">Rp 0</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto flex-1">
                    <div class="table-scroll-container min-h-0 overflow-y-auto max-h-[400px]">
                        <table class="w-full table-basic text-xs sm:text-sm">
                            <thead class="sticky top-0">
                                <tr>
                                    <th class="text-left" style="min-width: 120px;">Produk</th>
                                    <th class="text-right" style="min-width: 80px;">Harga</th>
                                    <th class="text-center" style="min-width: 50px;">Qty</th>
                                    <th class="text-right" style="min-width: 80px;">Subtotal</th>
                                    <th class="text-center" style="min-width: 60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKeranjang">
                                <tr><td colspan="5" class="table-empty-state"><p>Keranjang kosong. Tambahkan produk untuk memulai.</p></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-3 sm:px-6 py-3 sm:py-4 space-y-3 border-t border-slate-700 bg-slate-800/50">
                    <div id="error_checkout" class="hidden text-center text-sm font-semibold text-red-400"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            id="btnJadwalkanPesanan"
                            type="button"
                            onclick="jadwalkanPesanan()"
                            class="btn-secondary"
                        >
                            <i class="bi bi-calendar-event text-lg"></i>
                            <span>Jadwalkan</span>
                        </button>
                        <button
                            id="btnBayarSekarang"
                            type="button"
                            onclick="prosesTransaksi()"
                            class="btn-prim"
                        >
                            <i class="bi bi-credit-card text-lg"></i>
                            <span>Bayar Sekarang</span>
                        </button>
                    </div>
                    <div id="orderCompleteBox" class="hidden p-3 border border-green-500/30 rounded-lg bg-green-500/10">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Pesanan Section -->
    <div class="mt-4 sm:mt-6">
        <div class="bg-slate-800 shadow-lg rounded-xl border border-slate-700 overflow-hidden">
            <div class="px-3 sm:px-6 pt-3 sm:pt-5 pb-3 border-b border-slate-700 bg-gradient-to-r from-slate-800 to-slate-700/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <p class="text-[0.65rem] sm:text-xs uppercase tracking-widest font-bold text-cyan-400">Jadwal</p>
                    <h3 class="text-base sm:text-xl font-bold text-white mt-1 sm:mt-2">Pesanan Terjadwal</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        id="btnRefreshScheduledOrders"
                        type="button"
                        onclick="loadScheduledOrders()"
                        class="inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-2 text-xs sm:text-sm font-semibold text-slate-300 hover:text-white bg-slate-700 hover:bg-slate-600 rounded-lg hover:shadow-md border border-slate-600 transition-all duration-200 cursor-pointer min-h-[40px]"
                        aria-live="polite"
                        aria-busy="false"
                        data-loading="false"
                    >
                        <span class="refresh-icon inline-flex items-center" aria-hidden="true">
                            <i class="bi bi-arrow-clockwise"></i>
                        </span>
                        <span class="refresh-spinner inline-flex items-center" aria-hidden="true">
                            <i class="bi bi-arrow-repeat icon-spin"></i>
                        </span>
                        <span class="refresh-confirm inline-flex items-center" aria-hidden="true">
                            <i class="bi bi-check-lg text-emerald-400"></i>
                        </span>
                        <span class="sm:inline refresh-text">Refresh</span>
                    </button>

                    <button id="btnTestTelegramKasir" type="button" class="ml-0.5 sm:ml-1 inline-flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-2 text-xs sm:text-sm font-semibold text-slate-300 bg-slate-700 hover:bg-slate-600 rounded-lg border border-slate-600 transition-all min-h-[40px]" title="Test Telegram">
                        <i class="bi bi-bell-fill text-amber-500"></i>
                        <span class="hidden sm:inline">Test Telegram</span>
                    </button>
                </div>
            </div>
            <div id="scheduledOrdersContainer" class="px-3 sm:px-6 py-3 sm:py-4 min-h-[200px]">
                <!-- Scheduled orders rendered here -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['resources/js/kasir.js'])
@endsection

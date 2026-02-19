@extends('layouts.app')
@php($title = 'Kasir')
@php($active = 'kasir')

@section('content')
    <div class="grid gap-4 lg:gap-6 grid-cols-1 lg:grid-cols-3">
        <!-- Input Section -->
        <div class="lg:col-span-1">
            <div id="orderInputCard" class="bg-white shadow-md rounded-xl border border-slate-200 overflow-hidden sticky top-4">
                <div class="px-4 sm:px-6 pt-4 sm:pt-5 pb-3 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <p class="text-xs uppercase tracking-widest font-bold text-slate-400">Pesanan Baru</p>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 mt-2">Input Produk</h3>
                </div>
                <div class="px-4 sm:px-6 py-4 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Nama Pelanggan</label>
                        <input type="text" id="customer_name" class="w-full rounded-lg border border-slate-300 bg-white text-slate-900 px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all placeholder-slate-400 text-sm font-medium" placeholder="Ketik nama pelanggan..." required>
                        <div id="error_customer_name" class="hidden text-xs text-red-600 font-medium"></div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Pilih Produk</label>
                        <input class="w-full rounded-lg border border-slate-300 bg-white text-slate-900 px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all placeholder-slate-400 text-sm font-medium" list="product_list" id="product_input" placeholder="Cari produk..." autocomplete="off">
                        <datalist id="product_list"></datalist>
                        <div id="error_product_input" class="hidden text-xs text-red-600 font-medium"></div>
                    </div>

                    <div class="space-y-2 pt-1">
                        <label class="text-sm font-semibold text-slate-700">Jumlah</label>
                        <div class="flex gap-2">
                            <input type="number" id="quantity" class="flex-1 rounded-lg border border-slate-300 bg-white px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-center font-semibold text-slate-900" value="1" min="1">
                            <button class="btn-prim" type="button" onclick="tambahKeKeranjang()" style="padding: 0.75rem 0.75rem; min-width: auto; width: auto;">
                                <i class="bi bi-plus-lg text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Tanggal Pesanan</label>
                        <input type="datetime-local" id="order_date" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all text-sm font-medium text-slate-900" required value="" step="900">
                        <div id="error_order_date" class="hidden text-xs text-red-600 font-medium"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="lg:col-span-2">
            <div id="cartCard" class="bg-white shadow-md rounded-xl border border-slate-200 overflow-hidden flex flex-col h-fit">
                <div class="px-4 sm:px-6 pt-4 sm:pt-5 pb-3 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-xs uppercase tracking-widest font-bold text-slate-400">Keranjang</p>
                            <h3 class="text-lg sm:text-xl font-bold text-slate-900 mt-2">Ringkasan belanja</h3>
                        </div>
                        <div class="sm:text-right">
                            <p class="text-xs text-slate-600 font-medium">Total</p>
                            <p id="totalDisplay" class="text-2xl font-bold text-blue-600 mt-0.5">Rp 0</p>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto flex-1">
                    <div class="min-h-0 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-slate-100 border-b border-slate-200">
                                <tr>
                                    <th class="text-left px-4 sm:px-6 py-3 font-semibold text-slate-700">Produk</th>
                                    <th class="text-right px-4 sm:px-6 py-3 font-semibold text-slate-700">Harga</th>
                                    <th class="text-center px-4 sm:px-6 py-3 font-semibold text-slate-700">Qty</th>
                                    <th class="text-right px-4 sm:px-6 py-3 font-semibold text-slate-700">Subtotal</th>
                                    <th class="text-center px-4 sm:px-6 py-3 font-semibold text-slate-700">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tabelKeranjang" class="divide-y divide-slate-200">
                                <tr><td colspan="5" class="text-center py-8 text-slate-500 font-medium">Keranjang kosong. Tambahkan produk untuk memulai.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="px-4 sm:px-6 py-4 space-y-3 border-t border-slate-200 bg-slate-50">
                    <div id="error_checkout" class="hidden text-center text-sm font-semibold text-red-600"></div>
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
                    <div id="orderCompleteBox" class="hidden p-3 border border-green-300 rounded-lg bg-green-50">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jadwal Pesanan Section -->
    <div class="mt-6">
        <div class="bg-white shadow-md rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-4 sm:px-6 pt-4 sm:pt-5 pb-3 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-widest font-bold text-slate-400">Jadwal</p>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 mt-2">Pesanan Terjadwal</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        id="btnRefreshScheduledOrders"
                        type="button"
                        onclick="loadScheduledOrders()"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 bg-white hover:bg-slate-100 rounded-lg hover:shadow-sm border border-slate-200 transition-all duration-200 cursor-pointer"
                        aria-live="polite"
                        aria-busy="false"
                        data-loading="false"
                    >
                        <span class="refresh-icon inline-flex items-center" aria-hidden="true">
                            <i class="bi bi-arrow-clockwise"></i>
                        </span>
                        <span class="refresh-spinner hidden inline-flex items-center" aria-hidden="true">
                            <i class="bi bi-arrow-repeat icon-spin"></i>
                        </span>
                        <span class="refresh-confirm hidden inline-flex items-center" aria-hidden="true">
                            <i class="bi bi-check-lg text-emerald-600"></i>
                        </span>
                        <span class="hidden sm:inline refresh-text">Refresh</span>
                    </button>

                    <button id="btnTestTelegramKasir" type="button" class="ml-1 inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 transition-all" title="Test Telegram">
                        <i class="bi bi-bell-fill text-amber-600"></i>
                        <span>Test Telegram</span>
                    </button>
                </div>
            </div>
            <div id="scheduledOrdersContainer" class="px-4 sm:px-6 py-4 min-h-[200px]">
                <!-- Scheduled orders rendered here -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
    @vite(['resources/js/kasir.js'])
@endsection

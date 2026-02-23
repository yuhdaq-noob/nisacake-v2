@extends('layouts.app')
@php($title = 'Gudang & Inventaris')
@php($active = 'gudang')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-400">Inventaris</p>
            <h2 class="text-xl sm:text-2xl font-bold text-white mt-1">Manajemen Stok</h2>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <button class="btn-prim inline-flex items-center justify-center gap-2 flex-1 sm:flex-none" data-modal-open="modalRestock">
                <i class="bi bi-cart-plus-fill"></i>
                <span>Belanja Bahan</span>
            </button>
            <button type="button" class="btn-danger flex items-center justify-center gap-2 flex-1 sm:flex-none" data-modal-open="modalKurangStok">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Catat Kerusakan</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <x-alert type="success" title="Berhasil" message="{{ session('success') }}" dismissible id="session-alert-success" />
    @endif

    @if(session('error'))
        <x-alert type="error" title="Terjadi Kesalahan" message="{{ session('error') }}" dismissible id="session-alert-error" />
    @endif

    @if(session('warning'))
        <x-alert type="warning" title="Perhatian" message="{{ session('warning') }}" dismissible id="session-alert-warning" />
    @endif

    <div class="grid gap-4 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-slate-800 shadow-lg rounded-2xl border border-slate-700 overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700 sticky top-0 bg-slate-800 z-10">
                    <div class="flex items-center justify-between gap-3">
                        <h5 class="text-sm sm:text-base font-semibold text-white flex items-center gap-2"><i class="bi bi-box-seam-fill text-cyan-400"></i> Stok Fisik Saat Ini</h5>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-400">Live</span>
                    </div>
                </div>
                <div class="flex-1 overflow-x-auto">
                    <div class="table-scroll-container max-h-[500px] overflow-y-auto">
                        <table class="w-full table-basic text-xs sm:text-sm">
                            <thead class="sticky top-0">
                                <tr>
                                    <th class="text-left" style="min-width: 100px;">Nama Bahan</th>
                                    <th class="text-right hidden sm:table-cell" style="min-width: 85px;">Harga/Unit</th>
                                    <th class="text-right" style="min-width: 60px;">Stok</th>
                                    <th class="text-left hidden md:table-cell" style="min-width: 60px;">Satuan</th>
                                    <th class="text-center" style="min-width: 70px;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="tabelStok">
                                <tr><td colspan="5" class="text-center py-6 text-slate-400 font-medium">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="bg-slate-800 rounded-2xl shadow-lg border border-slate-700 overflow-hidden flex flex-col mt-4">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700 sticky top-0 bg-slate-800 z-10">
                    <h5 class="text-sm sm:text-base font-semibold text-white mb-1 flex items-center gap-2"><i class="bi bi-gear-fill text-slate-400"></i> Overhead & Asumsi</h5>
                    <p class="text-xs text-slate-400">Parameter perhitungan HPP</p>
                </div>
                <div class="px-4 sm:px-5 py-3 sm:py-4 overflow-y-auto lg:max-h-[400px]">
                    <table class="w-full table-basic text-xs sm:text-sm">
                        <thead class="sticky top-0">
                            <tr>
                                <th class="text-left" style="min-width: 130px;">Komponen</th>
                                <th class="text-right" style="min-width: 90px;">Nilai</th>
                                <th class="text-center hidden sm:table-cell" style="min-width: 50px;">Unit</th>
                            </tr>
                        </thead>
                        <tbody id="tabelOverhead">
                            <tr><td colspan="3" class="text-center py-6 text-slate-400">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-slate-800 shadow-lg rounded-2xl border border-slate-700 overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700">
                    <p class="text-sm sm:text-base font-semibold text-white">Riwayat Keluar/Masuk</p>
                </div>
                <div class="flex-1 overflow-y-auto lg:max-h-[500px] max-h-[400px]">
                    <ul class="divide-y divide-slate-700" id="listLog">
                        <li class="px-4 sm:px-5 py-3 sm:py-4 text-center text-sm text-slate-400 font-medium">Memuat riwayat...</li>
                    </ul>
                </div>
            </div>

            <div class="bg-slate-800 shadow-lg rounded-2xl border border-slate-700 overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700">
                    <p class="text-sm sm:text-base font-semibold text-white">Riwayat Perubahan Harga</p>
                </div>
                <div class="flex-1 overflow-y-auto lg:max-h-[400px] max-h-[400px]">
                    <ul class="divide-y divide-slate-700" id="listPriceLog">
                        <li class="px-4 sm:px-5 py-3 sm:py-4 text-center text-sm text-slate-400 font-medium">Memuat riwayat harga...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Restock -->
    <div id="modalRestock" class="modal fixed inset-0 z-50 hidden opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
        <div class="modal-backdrop absolute inset-0 bg-slate-900/70 opacity-0 transition-opacity duration-200 ease-out" data-modal-close="modalRestock"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="modal-panel w-full max-w-lg bg-slate-800 rounded-2xl shadow-xl border border-slate-700 transform transition-all duration-200 ease-out translate-y-4 scale-95 opacity-0">
                <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700">
                    <div>
                        <p class="text-sm font-semibold text-white flex items-center gap-2"><i class="bi bi-cart-plus-fill text-cyan-400"></i> Input Belanja Bahan</p>
                        <p class="text-xs text-slate-400 mt-1">Catat restock bahan baku</p>
                    </div>
                    <button class="p-2 text-slate-400 hover:bg-slate-700 rounded-lg transition-colors" data-modal-close="modalRestock" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="formRestock" data-ajax="true" class="px-4 sm:px-5 py-4 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300">Pilih Bahan Baku</label>
                        <select id="selectBahan" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-colors" required>
                            <option value="" disabled selected>Pilih Bahan</option>
                        </select>
                        <div id="error_restock_material" class="hidden text-xs text-red-400 mt-1"></div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300">Jumlah Masuk (Restock)</label>
                        <input type="number" id="inputJumlah" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-colors" min="1" placeholder="Contoh: 5000" required>
                        <p class="text-xs text-slate-500">Masukkan angka sesuai satuan bahan.</p>
                        <div id="error_restock_amount" class="hidden text-xs text-red-400 mt-1"></div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300">Keterangan (Opsional)</label>
                        <input type="text" id="inputKet" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-colors" placeholder="Contoh: Belanja di Pasar Besar">
                        <div id="error_restock_description" class="hidden text-xs text-red-400 mt-1"></div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" data-modal-close="modalRestock">Batal</button>
                        <button type="submit" class="btn-prim">Simpan Stok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Catat Kerusakan -->
    <div id="modalKurangStok" class="modal fixed inset-0 z-50 hidden opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
        <div class="modal-backdrop absolute inset-0 bg-slate-900/70 opacity-0 transition-opacity duration-200 ease-out" data-modal-close="modalKurangStok"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="modal-panel w-full max-w-lg bg-slate-800 rounded-2xl shadow-xl border border-slate-700 transform transition-all duration-200 ease-out translate-y-4 scale-95 opacity-0">
                <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-700">
                    <div>
                        <p class="text-sm font-semibold text-red-400 flex items-center gap-2"><i class="bi bi-exclamation-triangle-fill text-red-500"></i> Catat Pengurangan Stok</p>
                        <p class="text-xs text-slate-400 mt-1">Kerusakan atau susut stok</p>
                    </div>
                    <button class="p-2 text-slate-400 hover:bg-slate-700 rounded-lg transition-colors" data-modal-close="modalKurangStok" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="formKurangStok" data-ajax="true" class="px-4 sm:px-5 py-4 space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300">Pilih Bahan Baku</label>
                        <select id="selectKurang" name="material_id" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-colors" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} (Sisa: {{ $m->current_stock }} {{ $m->unit }})</option>
                            @endforeach
                        </select>
                        <div id="kurangStockInfo" class="text-xs text-slate-400 mt-1">Sisa: —</div>
                        <div id="error_kurang_material" class="hidden text-xs text-red-400 mt-1"></div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300">Jumlah Berkurang</label>
                        <input id="kurangAmount" type="number" name="amount" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-colors" min="1" placeholder="Contoh: 5" required>
                        <p class="text-xs text-slate-500 mt-1">Masukkan angka sesuai satuan bahan.</p>
                        <div id="error_kurang_amount" class="hidden text-xs text-red-400 mt-1"></div>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-300">Keterangan / Alasan</label>
                        <textarea id="kurangDescription" name="description" class="w-full rounded-lg border border-slate-600 bg-slate-700/50 text-slate-100 px-3 py-2 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 transition-colors" rows="3" placeholder="Wajib diisi! Contoh: Telur pecah atau tepung basah." required></textarea>
                        <p class="text-xs text-slate-500 mt-1">Jelaskan alasan secara singkat (maks 255 karakter).</p>
                        <div id="error_kurang_description" class="hidden text-xs text-red-400 mt-1"></div>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" data-modal-close="modalKurangStok">Batal</button>
                        <button type="submit" id="btnSimpanKurang" class="btn-danger">Simpan Catatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/gudang.js', 'resources/js/overhead.js'])
@endsection

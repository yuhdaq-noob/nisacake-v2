@extends('layouts.app')
@php($title = 'Gudang & Inventaris')
@php($active = 'gudang')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] font-semibold text-slate-500">Inventaris</p>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1">Manajemen Stok</h2>
        </div>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <button class="btn-prim inline-flex items-center justify-center gap-2 flex-1 sm:flex-none" data-modal-open="modalRestock">
                <i class="bi bi-cart-plus-fill"></i>
                <span>Belanja Bahan</span>
            </button>
            <button type="button" class="btn-secondary flex items-center justify-center gap-2 flex-1 sm:flex-none" data-modal-open="modalKurangStok">
                <i class="bi bi-exclamation-triangle-fill text-orange-500"></i>
                <span>Catat Kerusakan</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <x-alert
            type="success"
            title="Berhasil"
            message="{{ session('success') }}"
            dismissible
            id="session-alert-success"
        />
    @endif

    @if(session('error'))
        <x-alert
            type="error"
            title="Terjadi Kesalahan"
            message="{{ session('error') }}"
            dismissible
            id="session-alert-error"
        />
    @endif

    @if(session('warning'))
        <x-alert
            type="warning"
            title="Perhatian"
            message="{{ session('warning') }}"
            dismissible
            id="session-alert-warning"
        />
    @endif

    <div class="grid gap-4 lg:grid-cols-3 items-start">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white shadow-card rounded-2xl border border-slate-100 overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                    <div class="flex items-center justify-between gap-3">
                        <h5 class="text-sm sm:text-base font-semibold text-slate-900 flex items-center gap-2"><i class="bi bi-box-seam-fill text-cyan-600"></i> Stok Fisik Saat Ini</h5>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-cyan-50 text-cyan-700">Live</span>
                    </div>
                </div>
                <div class="flex-1 overflow-x-auto">
                    <div class="table-scroll-container max-h-[500px] overflow-y-auto">
                        <table class="w-full table-basic table-dark-header text-xs sm:text-sm">
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
                                <tr><td colspan="5" class="text-center py-6 text-slate-500 font-medium">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Overhead & Asumsi Table -->
            <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden flex flex-col mt-4">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-100 sticky top-0 bg-white z-10">
                    <h5 class="text-sm sm:text-base font-semibold text-slate-900 mb-1 flex items-center gap-2"><i class="bi bi-gear-fill text-slate-600"></i> Overhead & Asumsi</h5>
                    <p class="text-xs text-slate-500">Parameter perhitungan HPP</p>
                </div>
                <div class="px-4 sm:px-5 py-3 sm:py-4">
                    <table class="w-full table-basic text-xs sm:text-sm">
                        <thead class="sticky top-0">
                            <tr>
                                <th class="text-left" style="min-width: 130px;">Komponen</th>
                                <th class="text-right" style="min-width: 90px;">Nilai</th>
                                <th class="text-center hidden sm:table-cell" style="min-width: 50px;">Unit</th>
                            </tr>
                        </thead>
                        <tbody id="tabelOverhead">
                            <tr><td colspan="3" class="text-center py-6 text-slate-500">Memuat...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white shadow-card rounded-2xl border border-slate-100 overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-100">
                    <p class="text-sm sm:text-base font-semibold text-slate-900">Riwayat Keluar/Masuk</p>
                </div>
                <div class="flex-1 overflow-y-auto" style="max-height: 400px;">
                    <ul class="divide-y divide-slate-100" id="listLog">
                        <li class="px-4 sm:px-5 py-3 sm:py-4 text-center text-sm text-slate-500 font-medium">Memuat riwayat...</li>
                    </ul>
                </div>
            </div>

            <div class="bg-white shadow-card rounded-2xl border border-slate-100 overflow-hidden flex flex-col">
                <div class="px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-100">
                    <p class="text-sm sm:text-base font-semibold text-slate-900">Riwayat Perubahan Harga</p>
                </div>
                <div class="flex-1 overflow-y-auto" style="max-height: 400px;">
                    <ul class="divide-y divide-slate-100" id="listPriceLog">
                        <li class="px-4 sm:px-5 py-3 sm:py-4 text-center text-sm text-slate-500 font-medium">Memuat riwayat harga...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Restock -->
    <div id="modalRestock" class="modal fixed inset-0 z-50 hidden opacity-0 pointer-events-none transition-opacity duration-200 ease-out">
        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200 ease-out" data-modal-close="modalRestock"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="modal-panel w-full max-w-lg bg-white rounded-2xl shadow-lg border border-slate-100 transform transition-all duration-200 ease-out translate-y-4 scale-95 opacity-0">
                <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-4 border-b border-slate-100">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Input Belanja Bahan</p>
                        <p class="text-xs text-slate-500 mt-1">Catat restock bahan baku</p>
                    </div>
                    <button class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors" data-modal-close="modalRestock" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form id="formRestock" class="px-4 sm:px-5 py-4 space-y-4">
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-900">Pilih Bahan Baku</label>
                        <select id="selectBahan" class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-900 px-3 py-2 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200/50 transition-colors" required>
                            <option value="" disabled selected>-- Pilih Bahan --</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-900">Jumlah Masuk (Restock)</label>
                        <input type="number" id="inputJumlah" class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-900 px-3 py-2 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200/50 transition-colors" min="1" placeholder="Contoh: 5000" required>
                        <p class="text-xs text-slate-500">Masukkan angka sesuai satuan bahan.</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-900">Keterangan (Opsional)</label>
                        <input type="text" id="inputKet" class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-900 px-3 py-2 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200/50 transition-colors" placeholder="Contoh: Belanja di Pasar Besar">
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
        <div class="modal-backdrop absolute inset-0 bg-slate-900/50 opacity-0 transition-opacity duration-200 ease-out" data-modal-close="modalKurangStok"></div>
        <div class="relative z-10 flex items-center justify-center min-h-screen p-4">
            <div class="modal-panel w-full max-w-lg bg-white rounded-2xl shadow-lg border border-red-200 transform transition-all duration-200 ease-out translate-y-4 scale-95 opacity-0">
                <div class="flex items-center justify-between px-4 sm:px-5 py-3 sm:py-4 border-b border-red-200">
                    <div>
                        <p class="text-sm font-semibold text-red-700">Catat Pengurangan Stok</p>
                        <p class="text-xs text-slate-500 mt-1">Kerusakan atau susut stok</p>
                    </div>
                    <button class="p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors" data-modal-close="modalKurangStok" aria-label="Tutup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form action="{{ route('materials.reduce') }}" method="POST" class="px-4 sm:px-5 py-4 space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-900">Pilih Bahan Baku</label>
                        <select name="material_id" class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-900 px-3 py-2 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200/50 transition-colors" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($materials as $m)
                                <option value="{{ $m->id }}">{{ $m->name }} (Sisa: {{ $m->current_stock }} {{ $m->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-900">Jumlah Berkurang</label>
                        <input type="number" name="amount" class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-900 px-3 py-2 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200/50 transition-colors" min="1" placeholder="Contoh: 5" required>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-900">Keterangan / Alasan</label>
                        <textarea name="description" class="w-full rounded-lg border border-slate-200 bg-slate-50 text-slate-900 px-3 py-2 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200/50 transition-colors" rows="3" placeholder="Wajib diisi! Contoh: Telur pecah atau tepung basah." required></textarea>
                    </div>
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 pt-2">
                        <button type="button" class="btn-secondary" data-modal-close="modalKurangStok">Batal</button>
                        <button type="submit" class="btn-prim" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25), 0 1px 3px rgba(0, 0, 0, 0.1);">Simpan Catatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @vite(['resources/js/gudang.js', 'resources/js/overhead.js'])
@endsection

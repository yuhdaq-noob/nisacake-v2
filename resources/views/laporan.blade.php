@extends('layouts.app')
@php($title = 'Laporan Keuangan')
@php($active = 'laporan')

{{-- FIXME: PERHITUNGAN --}}
{{--
    TODO:TAMBAHKAN KE TABEL MATERIALS DATABASE
         {
            KARDUS KUE,
            MIKA KUE,
            PLASTIK "termasuk ukuran" nanti pakai id ke untuk di produk sesuai id
          }


    TODO: PISAHKAN CATEGORY PACKAGING DAN RAW MATERIAL DI MATERIALS DATABASE,

    TODO: TAMBAHKAN CARD TABLE OVERHEAD DI LAPORAN,

    TODO: LALU REFACTOR PERHITUNGAN HPP DENGAN RUMUS (BTK BUKAN BTKL)

    --}}

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Laporan</p>
            <h2 class="text-2xl font-bold text-slate-900">Dashboard Keuangan</h2>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3 mb-4">
        <div class="bg-white rounded-2xl shadow-card border border-slate-100">
            <div class="p-4">
                <p class="text-xs text-slate-500 uppercase">Omzet Hari Ini</p>
                <h3 class="text-2xl font-bold text-slate-900" id="cardOmzetToday">Rp 0</h3>
            </div>
        </div>
        <div class="bg-amber-900 rounded-2xl shadow-card text-white">
            <div class="p-4">
                <p class="text-xs text-amber-100 uppercase">Profit Hari Ini</p>
                <h3 class="text-2xl font-bold" id="cardProfitToday">Rp 0</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl shadow-card border border-slate-100">
            <div class="p-4">
                <p class="text-xs text-slate-500 uppercase">Profit Bulan Ini</p>
                <h3 class="text-2xl font-bold text-slate-900" id="cardProfitMonth">Rp 0</h3>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 mb-4">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <p class="text-sm font-semibold text-slate-900">Grafik 10 Transaksi Terakhir</p>
        </div>
        <div class="p-5">
            <div class="relative h-[300px] w-full">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100">
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <h5 class="text-lg font-semibold text-slate-900">Rincian Transaksi</h5>
            <div class="flex flex-wrap gap-2 items-center">
                <div class="relative">
                    <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" data-dropdown-trigger>
                        Export
                        <i class="bi bi-chevron-down text-xs"></i>
                    </button>
                    <div class="hidden absolute right-0 mt-2 w-40 rounded-xl border border-slate-200 bg-white shadow-card overflow-hidden" data-dropdown-menu>
                        <a class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" href="#" id="btnExportExcel">Excel (.xlsx)</a>
                        <a class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" href="#" id="btnExportPdf">PDF (.pdf)</a>
                    </div>
                </div>
                <input type="text" id="searchInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-200" placeholder="Cari pelanggan / produk..." style="min-width: 200px;">
                <select id="filterWaktu" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-200">
                    <option value="all">Semua Waktu</option>
                    <option value="today">Hari Ini</option>
                    <option value="last7">7 Hari Terakhir</option>
                    <option value="month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                    <option value="year">Tahun Ini</option>
                </select>
            </div>
        </div>

        <div class="p-2">
            <div class="overflow-x-auto">
              <table class="min-w-full table-basic table-dark-header text-sm">
                  <thead>
                      <tr>
                          <th class="text-left">ID</th>
                          <th class="text-left" style="min-width: 120px;">Tanggal</th>
                          <th class="text-left" style="min-width: 150px;">Pelanggan</th>
                          <th class="text-left">Produk</th>
                          <th class="text-right">Omzet</th>
                          <th class="text-right">HPP</th>
                          <th class="text-right">PROFIT</th>
                      </tr>
                  </thead>
                  <tbody id="tabelLaporan">
                      <tr><td colspan="7" class="text-center py-4">Memuat data...</td></tr>
                  </tbody>
                  <tfoot>
                      <tr class="font-bold bg-slate-50">
                          <td colspan="4" class="text-right">TOTAL:</td>
                          <td id="tableTotalOmzet" class="text-right">Rp 0</td>
                          <td id="tableTotalHPP" class="text-right">Rp 0</td>
                          <td id="tableTotalProfit" class="text-right text-emerald-700">Rp 0</td>
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
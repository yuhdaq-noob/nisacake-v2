{{--
    Komponen Alert — tampilan notifikasi modern dan profesional

    Props:
        - type: 'success', 'error', 'warning', 'info' (default: 'info')
        - title: Judul alert (opsional)
        - message: Isi pesan alert (wajib)
        - icon: Kelas ikon kustom (opsional)
        - dismissible: Jika true tampilkan tombol tutup
        - id: ID unik untuk alert yang dapat ditutup
--}}

@props([
    'type' => 'info',
    'title' => null,
    'message' => '',
    'icon' => null,
    'dismissible' => false,
    'id' => 'alert-' . Str::random(8),
])

@php
$typeConfig = [
    'success' => [
        'bgGradient' => 'bg-gradient-to-br from-emerald-50 to-emerald-100/50',
        'borderColor' => 'border-emerald-400',
        'textColor' => 'text-emerald-800',
        'titleColor' => 'text-emerald-900',
        'defaultIcon' => 'check-circle-fill',
    ],
    'error' => [
        'bgGradient' => 'bg-gradient-to-br from-red-50 to-red-100/50',
        'borderColor' => 'border-red-400',
        'textColor' => 'text-red-800',
        'titleColor' => 'text-red-900',
        'defaultIcon' => 'exclamation-circle-fill',
    ],
    'warning' => [
        'bgGradient' => 'bg-gradient-to-br from-amber-50 to-amber-100/50',
        'borderColor' => 'border-amber-400',
        'textColor' => 'text-amber-800',
        'titleColor' => 'text-amber-900',
        'defaultIcon' => 'exclamation-triangle-fill',
    ],
    'info' => [
        'bgGradient' => 'bg-gradient-to-br from-blue-50 to-blue-100/50',
        'borderColor' => 'border-blue-400',
        'textColor' => 'text-blue-800',
        'titleColor' => 'text-blue-900',
        'defaultIcon' => 'info-circle-fill',
    ],
];

$config = $typeConfig[$type] ?? $typeConfig['info'];
$iconClass = $icon ?? $config['defaultIcon'];
$accentColorMap = [
    'success' => 'text-emerald-600',
    'error' => 'text-red-600',
    'warning' => 'text-amber-600',
    'info' => 'text-blue-600',
];
@endphp

<div
    id="{{ $id }}"
    class="alert-container group relative rounded-lg border-1.5 {{ $config['borderColor'] }} {{ $config['bgGradient'] }} px-4 py-3.5 sm:px-5 sm:py-4 flex items-start gap-3.5 transition-all duration-300 ease-out animate-slideInDown shadow-md"
    role="alert"
    data-alert-type="{{ $type }}"
>
    {{-- Border aksen kiri --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-lg"
        @switch($type)
            @case('success')
                style="background: linear-gradient(180deg, #22c55e 0%, #16a34a 100%);"
            @break
            @case('error')
                style="background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);"
            @break
            @case('warning')
                style="background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);"
            @break
            @case('info')
                style="background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);"
            @break
        @endswitch
    ></div>

    {{-- Ikon --}}
    <div class="flex-shrink-0 pt-0.5">
        <i class="bi bi-{{ $iconClass }} {{ $accentColorMap[$type] }} text-xl"></i>
    </div>

    {{-- Konten --}}
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="font-bold {{ $config['titleColor'] }} text-sm sm:text-base mb-0.5">
                {{ $title }}
            </p>
        @endif
        <p class="text-sm {{ $config['textColor'] }} opacity-95 leading-relaxed font-medium">
            {{ $message }}
        </p>
        {{-- Slot untuk konten tambahan --}}
        @if($slot->isNotEmpty())
            <div class="mt-2.5 text-xs {{ $config['textColor'] }} opacity-85">
                {{ $slot }}
            </div>
        @endif
    </div>

    {{-- Tombol tutup --}}
    @if($dismissible)
        <button
            type="button"
            class="flex-shrink-0 p-1.5 rounded-md transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 opacity-70 hover:opacity-100"
            :class="'{{ match($type) {
                'success' => 'hover:bg-emerald-200',
                'error' => 'hover:bg-red-200',
                'warning' => 'hover:bg-amber-200',
                'info' => 'hover:bg-blue-200',
                default => 'hover:bg-slate-200',
            } }}'"
            aria-label="Tutup notifikasi"
            onclick="closeAlert('{{ $id }}')"
        >
            <i class="bi bi-x-lg text-lg"></i>
        </button>
    @endif
</div>

{{-- Skrip untuk alert yang bisa ditutup --}}
@if($dismissible)
    @once
        <script>
            window.closeAlert = function(alertId) {
                const alertEl = document.getElementById(alertId);
                if (!alertEl) return;

                alertEl.classList.remove('animate-slideInDown');
                alertEl.classList.add('animate-slideOutUp');

                setTimeout(() => {
                    alertEl.remove();
                }, 300);
            };

            // Tutup otomatis alert setelah 5 detik (kecuali ada interaksi pengguna)
            document.querySelectorAll('[data-alert-type]').forEach(alert => {
                if (!alert.querySelector('button[onclick*="closeAlert"]')) return;

                const timeout = setTimeout(() => {
                    closeAlert(alert.id);
                }, 5000);

                alert.addEventListener('mouseenter', () => clearTimeout(timeout));
            });
        </script>
    @endonce
@endif

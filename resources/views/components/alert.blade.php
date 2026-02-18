{{--
  Alert Component - Modern & Professional Notification Display

  SOLID Principles Applied:
  - Single Responsibility: Component handles only alert display
  - Open/Closed: Extensible through props (type, icon, dismissible, etc.)
  - Liskov Substitution: Interchangeable alert types (success, error, warning, info)
  - Interface Segregation: Props are focused and minimal
  - Dependency Inversion: Depends on tailwind utilities, not specific implementations

  Props:
    - type: 'success', 'error', 'warning', 'info' (default: 'info')
    - title: Alert heading (optional)
    - message: Alert message text (required)
    - icon: Custom icon class or leave empty for default
    - dismissible: Boolean to show close button (default: false)
    - id: Unique ID for dismissible alerts
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
        'bgColor' => 'bg-emerald-50',
        'borderColor' => 'border-emerald-300',
        'textColor' => 'text-emerald-800',
        'titleColor' => 'text-emerald-900',
        'iconColor' => 'text-emerald-600',
        'closeColor' => 'hover:bg-emerald-100',
        'defaultIcon' => 'bi bi-check-circle-fill',
    ],
    'error' => [
        'bgColor' => 'bg-red-50',
        'borderColor' => 'border-red-300',
        'textColor' => 'text-red-700',
        'titleColor' => 'text-red-900',
        'iconColor' => 'text-red-600',
        'closeColor' => 'hover:bg-red-100',
        'defaultIcon' => 'bi bi-exclamation-circle-fill',
    ],
    'warning' => [
        'bgColor' => 'bg-amber-50',
        'borderColor' => 'border-amber-300',
        'textColor' => 'text-amber-700',
        'titleColor' => 'text-amber-900',
        'iconColor' => 'text-amber-600',
        'closeColor' => 'hover:bg-amber-100',
        'defaultIcon' => 'bi bi-exclamation-triangle-fill',
    ],
    'info' => [
        'bgColor' => 'bg-blue-50',
        'borderColor' => 'border-blue-300',
        'textColor' => 'text-blue-700',
        'titleColor' => 'text-blue-900',
        'iconColor' => 'text-blue-600',
        'closeColor' => 'hover:bg-blue-100',
        'defaultIcon' => 'bi bi-info-circle-fill',
    ],
];

$config = $typeConfig[$type] ?? $typeConfig['info'];
$iconClass = $icon ?? $config['defaultIcon'];
@endphp

<div
    id="{{ $id }}"
    class="alert-container group rounded-xl border {{ $config['borderColor'] }} {{ $config['bgColor'] }} px-4 py-3.5 sm:px-5 sm:py-4 flex items-start gap-3.5 transition-all duration-300 ease-out animate-slideInDown"
    role="alert"
    data-alert-type="{{ $type }}"
>
    {{-- Icon --}}
    <div class="flex-shrink-0 mt-0.5">
        <i class="bi {{ $iconClass }} {{ $config['iconColor'] }} text-lg"></i>
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="font-semibold {{ $config['titleColor'] }} text-sm sm:text-base mb-1">
                {{ $title }}
            </p>
        @endif
        <p class="text-sm {{ $config['textColor'] }} opacity-95 leading-relaxed">
            {{ $message }}
        </p>
        {{-- Slot for additional content --}}
        @if($slot->isNotEmpty())
            <div class="mt-2.5 text-xs {{ $config['textColor'] }} opacity-85">
                {{ $slot }}
            </div>
        @endif
    </div>

    {{-- Close Button --}}
    @if($dismissible)
        <button
            type="button"
            class="flex-shrink-0 p-2 text-slate-400 {{ $config['closeColor'] }} rounded-lg transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1"
            aria-label="Tutup notifikasi"
            onclick="closeAlert('{{ $id }}')"
        >
            <i class="bi bi-x-lg"></i>
        </button>
    @endif
</div>

{{-- Dismissible Alert Script --}}
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

            // Auto-close alerts after 5 seconds (except on user interaction)
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
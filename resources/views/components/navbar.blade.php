@php
    $defaultLinks = [
        ['label' => 'Kasir', 'href' => url('/kasir'), 'key' => 'kasir', 'icon' => 'bi bi-cash-stack'],
        ['label' => 'Gudang', 'href' => url('/gudang'), 'key' => 'gudang', 'icon' => 'bi bi-boxes'],
        ['label' => 'Laporan', 'href' => url('/laporan'), 'key' => 'laporan', 'icon' => 'bi bi-graph-up-arrow'],
    ];

    $navLinks = $links ?? ($navbarLinks ?? $defaultLinks);
    $activeKey = $active ?? null;
@endphp

<!-- Mobile Top Bar -->
<div class="fixed top-0 inset-x-0 z-40 bg-gradient-to-r from-white via-white to-slate-50/80 backdrop-blur-sm border-b border-slate-200 lg:hidden shadow-sm">
    <div class="flex items-center justify-between h-16 px-5">
        <div class="flex items-center gap-3">
            <img
              src="/images/logo.png"
              alt="NC Logo"
              class="h-10 w-10 rounded-xl shadow-md object-cover"
            />
            <div class="leading-tight">
                <p class="text-sm font-bold text-slate-900">Nisa Cake</p>
                <p class="text-xs font-medium text-slate-500">System</p>
            </div>
        </div>
        <button class="p-2.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300 transition-all duration-200" data-drawer-toggle aria-label="Buka menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</div>

<!-- Sidebar / Drawer -->
<aside
    class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-slate-200 shadow-lg transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
    data-drawer
    data-sidebar
    data-collapsed="false"
>
    <div class="flex flex-col h-full bg-gradient-to-b from-white via-white to-slate-50">
        <!-- Header Section -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <img
                  src="/images/logo.png"
                  alt="NC Logo"
                  class="h-12 w-12 rounded-xl shadow-md object-cover"
                />
                <div class="sidebar-textual leading-tight">
                    <p class="text-base font-bold text-slate-900 tracking-tight">Nisa Cake</p>
                    <p class="text-xs font-medium text-slate-500">System</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="hidden lg:flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 hover:border-slate-300 transition-all duration-200" data-sidebar-collapse aria-expanded="false">
                    <span class="collapse-icon collapse-icon--expand">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m-6-6 6 6-6 6" />
                        </svg>
                    </span>
                    <span class="collapse-icon collapse-icon--collapse">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4m6 6-6-6 6-6" />
                        </svg>
                    </span>
                </button>
                <button class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 rounded-lg transition-colors" data-drawer-close aria-label="Tutup menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="px-5 py-6 flex-shrink-0" style="border-bottom: 1px solid #e2e8f0;">
            <div class="profile-card rounded-xl bg-gradient-to-br from-cyan-50 to-blue-50 border border-cyan-200/50 shadow-sm hover:shadow-md transition-all duration-200 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        <img src="/images/owner.jpg" alt="Foto Profil" class="h-14 w-14 rounded-xl object-cover ring-2 ring-white shadow-md">
                        <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-500 ring-2 ring-white"></span>
                    </div>
                    <div class="sidebar-textual flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900">Ibu Nisa</p>
                        <p class="text-xs text-slate-600 mt-0.5 font-medium">Owner & Admin</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-5 py-6 overflow-y-auto space-y-1.5" aria-label="Navigasi utama">
            <p class="sidebar-textual text-xs font-bold uppercase tracking-widest text-slate-400 px-2 mb-4">Menu Utama</p>
            @foreach($navLinks as $link)
                @php
                    $hrefPath = parse_url($link['href'], PHP_URL_PATH) ?: '/';
                    $isActive = false;
                    if($activeKey) {
                        $isActive = ($activeKey === ($link['key'] ?? $link['href']));
                    } else {
                        $trim = trim($hrefPath, '/');
                        $isActive = $hrefPath === '/' ? request()->is('/') : (request()->is($trim) || request()->is($trim.'/*'));
                    }
                    $baseIcon = isset($link['icon']) ? '<i class="'.$link['icon'].'"></i>' : '<i class="bi bi-circle"></i>';
                    $activeClass = $isActive
                        ? 'bg-gradient-to-r from-cyan-500/10 to-blue-500/10 text-cyan-700 border-l-2 border-l-cyan-500 shadow-sm font-semibold'
                        : 'text-slate-600 hover:bg-slate-100/60 hover:text-slate-900 hover:border-l-slate-300 border-l-2 border-l-transparent';
                @endphp
                <a
                    href="{{ $link['href'] }}"
                    title="{{ $link['label'] }}"
                    aria-current="{{ $isActive ? 'page' : 'false' }}"
                    data-tooltip="{{ $link['label'] }}"
                    class="nav-link group relative flex items-center gap-3.5 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)] {{ $activeClass }}"
                >
                    <span class="text-lg flex-shrink-0 {{ $isActive ? 'text-cyan-600' : 'text-slate-500 group-hover:text-slate-700' }}">{!! $baseIcon !!}</span>
                    <span class="nav-label flex-1">{{ $link['label'] }}</span>
                    @if($isActive)
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-600"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <!-- Logout Button -->
        @if($showLogout ?? true)
            <div class="flex-shrink-0 px-5 pb-6 pt-5 border-t border-slate-100">
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" title="Logout" class="logout-btn w-full inline-flex items-center justify-center gap-3 rounded-lg bg-red-50 text-red-700 py-2.5 px-4 font-semibold text-sm border border-red-100/80 hover:bg-red-100 hover:border-red-200 transition-all duration-200 shadow-sm hover:shadow-md">
                        <i class="bi bi-box-arrow-right text-base"></i>
                        <span class="logout-label">Logout</span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</aside>

<div class="hidden fixed inset-0 bg-slate-900/40 z-40 lg:hidden" data-drawer-backdrop></div>

@once
    <style>
        [data-sidebar] {
            font-family: "Poppins", sans-serif;
        }

        [data-sidebar][data-collapsed="true"] {
            width: 5rem;
        }

        [data-sidebar] .sidebar-textual,
        [data-sidebar] .nav-label,
        [data-sidebar] .logout-label,
        [data-sidebar] .profile-card {
            transition: opacity 200ms cubic-bezier(0.4, 0, 0.2, 1), transform 200ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        [data-sidebar][data-collapsed="true"] .sidebar-textual,
        [data-sidebar][data-collapsed="true"] .nav-label,
        [data-sidebar][data-collapsed="true"] .logout-label {
            opacity: 0;
            transform: translateX(-8px);
            pointer-events: none;
            width: 0;
        }

        [data-sidebar][data-collapsed="true"] .profile-card {
            opacity: 0;
            transform: translateY(-12px);
            pointer-events: none;
            height: 0;
            margin: 0;
            padding: 0;
            border: 0;
        }

        [data-sidebar][data-collapsed="true"] .nav-link {
            justify-content: center;
            padding-left: 0.625rem;
            padding-right: 0.625rem;
            border-left-width: 2px;
            border-left-color: transparent;
        }

        [data-sidebar][data-collapsed="true"] .nav-link span:last-child {
            display: none;
        }

        [data-sidebar][data-collapsed="true"] .logout-btn {
            justify-content: center;
            padding-left: 0.625rem;
            padding-right: 0.625rem;
        }

        [data-sidebar][data-collapsed="true"] .logout-btn span {
            display: none;
        }

        [data-sidebar][data-collapsed="true"] .nav-link::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 0.6rem);
            top: 50%;
            transform: translateY(-50%) scale(0.94);
            background: #1e293b;
            color: #f1f5f9;
            padding: 0.4rem 0.6rem;
            border-radius: 0.6rem;
            box-shadow: 0 8px 24px rgba(3, 105, 161, 0.18);
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 150ms cubic-bezier(0.4, 0, 0.2, 1), transform 150ms cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            z-index: 50;
        }

        [data-sidebar][data-collapsed="true"] .nav-link:hover::after {
            opacity: 1;
            transform: translateY(-50%) scale(1);
        }

        [data-sidebar] .collapse-icon--collapse {
            display: none;
        }

        [data-sidebar][data-collapsed="true"] .collapse-icon--expand {
            display: none;
        }

        [data-sidebar][data-collapsed="true"] .collapse-icon--collapse {
            display: inline-flex;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.querySelector("[data-sidebar]");
            const toggles = document.querySelectorAll("[data-sidebar-collapse]");

            if (!sidebar || !toggles.length) {
                return;
            }

            toggles.forEach(function (button) {
                button.addEventListener("click", function () {
                    const isCollapsed = sidebar.getAttribute("data-collapsed") === "true";
                    sidebar.setAttribute("data-collapsed", isCollapsed ? "false" : "true");
                    button.setAttribute("aria-expanded", isCollapsed ? "false" : "true");
                });
            });
        });
    </script>
@endonce
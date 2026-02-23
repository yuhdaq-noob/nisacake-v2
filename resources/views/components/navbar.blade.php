@php
    // Use default links from component, or custom links if provided
    $navLinks = $links ?? $defaultLinks;
    $activeKey = $active ?? null;

    // Dark mode - always enabled for professional dark theme
    $isDark = true;

    // Get user data from component (already authenticated)
    $userName = $user?->name ?? 'Guest';
    $userRole = $user?->username ?? 'User';

    // Image paths with fallback
    $logoPath = file_exists(public_path('images/logo.png')) ? '/images/logo.png' : '/favicon.ico';
    $profilePath = file_exists(public_path('images/owner.jpg')) ? '/images/owner.jpg' : null;

    // Professional Dark Theme Colors - Fixed
    $headerBg = 'bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900';
    $sidebarBg = 'bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900';
    $textPrimary = 'text-slate-100';
    $textSecondary = 'text-slate-300';
    $textMuted = 'text-slate-400';
    $borderColor = 'border-slate-700';
    $hoverBg = 'hover:bg-slate-700/50';

    // Accent colors for dark theme
    $accentColor = 'cyan';
    $accentGlow = 'rgba(6, 182, 212, 0.15)';
@endphp

<!-- Mobile Top Bar -->
<div class="fixed top-0 inset-x-0 z-40 {{ $headerBg }} backdrop-blur-sm border-b {{ $borderColor }} lg:hidden shadow-sm">
    <div class="flex items-center justify-between h-16 px-5">
        <div class="flex items-center gap-3">
            <img
              src="{{ $logoPath }}"
              alt="NC Logo"
              class="h-10 w-10 rounded-xl shadow-md object-cover"
            />
            <div class="leading-tight">
                <p class="text-sm font-bold {{ $textPrimary }}">Nisa Cake</p>
                <p class="text-xs font-medium {{ $textSecondary }}">System</p>
            </div>
        </div>
        <button class="p-2.5 rounded-lg border {{ $borderColor }} {{ $hoverBg }} {{ $textSecondary }} transition-all duration-200" data-drawer-toggle aria-label="Buka menu">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
</div>

<!-- Sidebar / Drawer -->
<aside
    class="fixed inset-y-0 left-0 z-50 w-72 {{ $sidebarBg }} border-r {{ $borderColor }} shadow-lg transition-all duration-300 ease-in-out -translate-x-full lg:translate-x-0"
    data-drawer
    data-sidebar
    data-collapsed="false"
>
    <div class="flex flex-col h-full">
        <!-- Header Section -->
        <div class="flex items-center justify-between px-6 py-5 border-b {{ $borderColor }}">
            <div class="flex items-center gap-4">
                <img
                  src="{{ $logoPath }}"
                  alt="NC Logo"
                  class="sidebar-logo h-12 w-12 rounded-xl shadow-md object-cover"
                />
                <div class="sidebar-textual leading-tight">
                    <p class="text-base font-bold {{ $textPrimary }} tracking-tight">Nisa Cake</p>
                    <p class="text-xs font-medium {{ $textSecondary }}">System</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="hidden lg:flex h-9 w-9 items-center justify-center rounded-lg border {{ $borderColor }} {{ $textSecondary }} {{ $hoverBg }} transition-all duration-200" data-sidebar-collapse aria-expanded="false">
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
                <button class="lg:hidden p-2 {{ $textSecondary }} {{ $hoverBg }} rounded-lg transition-colors" data-drawer-close aria-label="Tutup menu">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Profile Card - Dark Theme -->
        <div class="px-5 py-6 flex-shrink-0" style="border-bottom: 1px solid #334155;">
            <div class="profile-card rounded-xl bg-gradient-to-br from-slate-800 to-slate-700/80 border border-slate-600/50 shadow-lg hover:shadow-xl hover:shadow-cyan-500/5 transition-all duration-300 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="relative flex-shrink-0">
                        @if($profilePath)
                            <img src="{{ $profilePath }}" alt="Foto Profil" class="h-14 w-14 rounded-xl object-cover ring-2 ring-slate-600 shadow-md">
                        @else
                            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center ring-2 ring-slate-600 shadow-md">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                            </div>
                        @endif
                        <span class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full bg-green-500 ring-2 ring-slate-700"></span>
                    </div>
                    <div class="sidebar-textual flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-100">{{ $userName }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ $userRole }}</p>
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
                        ? 'bg-gradient-to-r from-slate-700/50 to-slate-600/50 text-white border-l-2 border-l-cyan-400 shadow-sm font-semibold'
                        : 'text-slate-300 hover:bg-slate-800/60 hover:text-white hover:border-l-slate-500 border-l-2 border-l-transparent';
                @endphp
                <a
                    href="{{ $link['href'] }}"
                    title="{{ $link['label'] }}"
                    aria-current="{{ $isActive ? 'page' : 'false' }}"
                    data-tooltip="{{ $link['label'] }}"
                    class="nav-link group relative flex items-center gap-3.5 rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200 ease-[cubic-bezier(0.4,0,0.2,1)] {{ $activeClass }}"
                >
                    <span class="text-lg flex-shrink-0 {{ $isActive ? 'text-cyan-400' : 'text-slate-400 group-hover:text-white' }}">{!! $baseIcon !!}</span>
                    <span class="nav-label flex-1">{{ $link['label'] }}</span>
                    @if($isActive)
                        <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <!-- Logout Button -->
        @if($showLogout ?? true)
            <div class="flex-shrink-0 px-5 pb-6 pt-5 border-t {{ $borderColor }}">
                <form action="{{ route('logout') }}" method="POST" class="w-full">
                    @csrf
                    <button type="submit" title="Logout" class="logout-btn w-full inline-flex items-center justify-center gap-3 rounded-lg bg-red-900/30 text-red-400 border-red-700/50 hover:bg-red-900/50 py-2.5 px-4 font-semibold text-sm border transition-all duration-200 shadow-sm hover:shadow-md">
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
            box-shadow: 0 8px 24px rgba(8, 145, 178, 0.18);
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

        [data-sidebar][data-collapsed="true"] .sidebar-logo {
            height: 40px;
            width: 40px;
            transition: width 200ms ease, height 200ms ease, transform 200ms ease;
        }

        [data-sidebar][data-collapsed="true"] .nav-link .text-lg {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.6rem;
            transition: background-color 160ms, color 160ms, transform 160ms, box-shadow 160ms;
        }

        [data-sidebar][data-collapsed="true"] .nav-link:hover .text-lg {
            background-color: rgba(8, 145, 178, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(8, 145, 178, 0.15);
            color: #0891b2;
        }

        [data-sidebar][data-collapsed="true"] .nav-link[aria-current="page"] .text-lg {
            background: linear-gradient(135deg, rgba(8,145,178,0.15), rgba(6,182,212,0.1));
            color: #0891b2;
            box-shadow: 0 8px 30px rgba(8,145,178,0.15);
        }

        [data-sidebar][data-collapsed="true"] .nav-link::before {
            content: '';
            position: absolute;
            left: calc(100% + 0.44rem);
            top: 50%;
            transform: translateY(-50%) rotate(45deg) scale(0.95);
            width: 8px;
            height: 8px;
            background: #0f1724;
            opacity: 0;
            pointer-events: none;
            transition: opacity 150ms, transform 150ms;
            z-index: 49;
            border-radius: 1px;
        }

        [data-sidebar][data-collapsed="true"] .nav-link:hover::before {
            opacity: 1;
            transform: translateY(-50%) rotate(45deg) scale(1);
        }

        [data-sidebar][data-collapsed="true"] .nav-link::after {
            box-shadow: 0 12px 30px rgba(2, 6, 23, 0.12);
            transform-origin: left center;
            transition: opacity 160ms cubic-bezier(0.2,0.9,0.2,1), transform 160ms cubic-bezier(0.2,0.9,0.2,1);
        }

        [data-sidebar][data-collapsed="true"] .logout-btn i {
            display: inline-flex;
            width: 2.25rem;
            height: 2.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.6rem;
            transition: background 120ms, transform 120ms, box-shadow 120ms;
        }

        [data-sidebar][data-collapsed="true"] .logout-btn:hover i {
            background-color: rgba(239, 68, 68, 0.1);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.15);
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

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Nisa Cake' }} - Nisa Cake</title>

    <!-- Bootstrap Icons (for existing icon set reuse) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Vite Assets (CSS & JS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @yield('styles')
</head>
<body class="bg-slate-50 text-slate-900">
    <div class="min-h-screen flex lg:pl-72">
        <!-- Sidebar Navigation -->
        <x-navbar active="{{ $active ?? null }}" dark="true" />

        <!-- Main Content -->
        <div class="flex-1 w-full">
            <div class="lg:hidden h-14"></div>
            <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-10 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Page-Specific Scripts -->
    @yield('scripts')
</body>
</html>

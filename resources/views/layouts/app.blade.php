<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Fahrimart</title>
    @vite('resources/js/app.js')
</head>
<body>
    <div class="app-shell">
        @include('layouts.partials.sidebar')

        <main class="app-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
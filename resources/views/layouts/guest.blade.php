<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Fahrimart</title>
    @vite('resources/js/app.js')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html> 
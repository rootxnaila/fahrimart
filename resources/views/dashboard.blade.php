<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Fahrimart</title>
</head>
<body>
    <h1>Dashboard Fahrimart</h1>
    <p>Halo, {{ auth()->user()->nama }} ({{ auth()->user()->role }})</p>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">Logout</button>
    </form>
</body>
</html>
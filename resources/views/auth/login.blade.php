<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Fahrimart</title>
</head>
<body>
    <h1>Fahrimart</h1>
    <h2>Login</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <label for="email">Email</label><br>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div>
            <label for="password">Password</label><br>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label>
                <input type="checkbox" name="remember"> Ingat saya
            </label>
        </div>

        @error('email')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <button type="submit">Masuk</button>
    </form>
</body>
</html>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Dashboard</title>
    <!-- Aqui você pode incluir seus arquivos CSS -->
</head>
<body>
    <<div class="navbar">
        <nav>
            <a href="{{ route('home') }}">Home</a>
            <a href="{{ route('login') }}">Login</a>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </nav>
    </div>

    <div class="content">
        @yield('content') <!-- Aqui o conteúdo das páginas será injetado -->
    </div>

    <!-- Aqui você pode incluir seus arquivos JS -->
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NeWave One</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen text-white">
    <!-- Container principal -->
    <div class="w-full max-w-md bg-gray-800 rounded-lg shadow-lg p-8">
        <!-- Logo -->
        <div class="text-center mb-6">
            <h1 class="text-4xl font-bold text-green-400">FutureTec <span class="text-blue-400">Server</span></h1>
        </div>

        <!-- Formulário de login -->
        <form action="{{ route('user.login') }}" method="POST" class="space-y-6">
            @csrf
            <!-- Campo E-mail -->
            <div>
                <label for="email" class="block text-sm font-medium mb-1">E-mail</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    placeholder="Digite seu e-mail" 
                    required>
            </div>
        
            <!-- Campo Senha -->
            <div>
                <label for="password" class="block text-sm font-medium mb-1">Senha</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-blue-500 focus:outline-none" 
                    placeholder="Digite sua senha" 
                    required>
            </div>
        
            <!-- Exibir erro -->
            @if ($errors->has('login_error'))
                <div class="text-red-500 text-sm">
                    {{ $errors->first('login_error') }}
                </div>
            @endif
        
            <!-- Botão Entrar -->
            <button 
                type="submit" 
                class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">
                Entrar
            </button>
        </form>

        <!-- Esqueceu a senha -->
        <div class="text-center mt-4">
            <a href="#" class="text-sm text-blue-400 hover:underline">Esqueceu sua senha?</a>
        </div>
    </div>

    <!-- Rodapé -->
    <footer class="absolute bottom-4 text-center w-full text-gray-500 text-sm">
        <p>Copyright &copy; 2024 <span class="text-blue-400">FutureTec telecom</span></p>
        <p class="text-gray-600">v2.11.5</p>
    </footer>
</body>
</html>

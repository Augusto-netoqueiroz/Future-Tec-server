<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WPPConnect - Iniciar Sessão</title>

    <!-- Incluir o script do Pusher e Laravel Echo -->
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.0.3"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.6.3"></script>

    <script>
        window.Pusher = require('pusher-js');
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: 'your-pusher-key',  // Substitua pela sua chave do Pusher
            wsHost: window.location.hostname,  // Ou o IP do seu servidor
            wsPort: 3089,  // A porta configurada para o Echo Server
            forceTLS: false,
            disableStats: true
        });

        // Escutando o canal e evento
        window.Echo.channel('your-channel')
            .listen('YourEvent', (event) => {
                console.log('Evento recebido:', event);
                // Aqui você pode adicionar lógica para manipular os dados recebidos
            });
    </script>
</head>
<body>
    <h1>Criar nova Sessão</h1>

    <form action="{{ route('wppconnect.start-session') }}" method="POST">
        @csrf
        <label for="session">Nome da Sessão:</label>
        <input type="text" id="session" name="session" required>
        <button type="submit">Iniciar Sessão</button>
    </form>

    <!-- Verifica se o QR Code foi gerado e exibe -->
    @if(isset($qrcode))
    <h2>QR Code Gerado:</h2>
    <img src="data:image/png;base64,{{ $qrcode }}" alt="QR Code" />
    @endif
</body>
</html>

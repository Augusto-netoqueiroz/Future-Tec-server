import './bootstrap';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'your_app_key',
    cluster: 'your_cluster',
    encrypted: true
});

echo.channel('session-channel')
    .listen('SessionStarted', (event) => {
        console.log('Evento recebido:', event);
        // Exiba a mensagem no frontend, como um alerta ou atualizar o estado da página.
    });

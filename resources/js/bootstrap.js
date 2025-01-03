import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
window.io = require('socket.io-client');

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: 'http://localhost:3000',
});


// Conexão com a nova instância do Socket.IO no namespace '/socketwapp' na porta 3080
window.EchoWapp = new Echo({
    broadcaster: 'socket.io',
    client: io('http://localhost:3080/socketwapp'),  // Endereço do servidor socketwapp
});

// Exemplo de como emitir um evento para o namespace socketwapp
window.EchoWapp.emit('event_wapp', { message: 'Testando a nova instância' });

// Exemplo de como escutar um evento do namespace socketwapp
window.EchoWapp.channel('socketwapp')
    .listen('.welcome', (event) => {
        console.log('Mensagem de boas-vindas:', event.message);
    });
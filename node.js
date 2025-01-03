const http = require('http');
const socketIo = require('socket.io');

// Crie o servidor HTTP
const server = http.createServer((req, res) => {
    res.writeHead(200, {'Content-Type': 'text/plain'});
    res.end('Servidor Socket.IO rodando na porta 3080');
});

// Crie a instância do Socket.IO para rodar na porta 3080
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

// Defina um namespace para a segunda instância (socketwapp)
const socketwappNamespace = io.of('/socketwapp');

// Evento de conexão para o namespace `socketwapp`
socketwappNamespace.on('connection', (socket) => {
    console.log('Usuário conectado ao socketwapp');

    // Evento personalizado no namespace
    socket.on('event_wapp', (data) => {
        console.log('Mensagem recebida no socketwapp:', data);
    });

    // Emitindo uma mensagem para o cliente
    socket.emit('welcome', { message: 'Bem-vindo ao socketwapp!' });
});

// O servidor vai rodar na porta 3080
server.listen(3080, () => {
    console.log('Servidor Socket.IO rodando na porta 3080');
});

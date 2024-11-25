import { io } from 'socket.io-client';

const socket = io('http://localhost:3000');

socket.on('connect', () => {
    console.log('Conectado ao servidor:', socket.id);
    socket.emit('message', { texto: 'Teste local' });
});

socket.on('message', (data) => {
    console.log('Mensagem recebida:', data);
});

socket.on('disconnect', () => {
    console.log('Desconectado do servidor');
});

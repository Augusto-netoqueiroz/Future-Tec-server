import AsteriskManager from 'asterisk-manager';

// Conectando com a porta AMI (5038) no Asterisk
const ami = new AsteriskManager(5038, 'localhost', 'admin', 'MKsx2377!@', true);

ami.on('managerevent', (event) => {
    console.log('Evento AMI recebido:', event);

    // Verifique o tipo de evento e execute ações específicas
    if (event.event === 'Newchannel') {
        console.log(`Nova chamada detectada: Canal: ${event.channel}, Ramal: ${event.extension}`);
    }

    if (event.event === 'Hangup') {
        console.log(`Chamada finalizada: Canal: ${event.channel}, Status: ${event.status}`);
    }

    if (event.event === 'Peerstatus') {
        console.log(`Status do ramal: Ramal: ${event.peer}, Status: ${event.status}`);
    }
});

ami.on('error', (err) => {
    console.error('Erro na conexão AMI:', err);
});

// Enviar um comando Ping para verificar se a conexão está funcionando
ami.action({
    action: 'Ping'
}, (err, res) => {
    if (err) {
        console.log('Erro ao enviar Ping:', err);
    } else {
        console.log('Resposta do Asterisk:', res);
    }
});

import mysql from "mysql2";
import AsteriskManager from 'asterisk-manager';

// Configurações de conexão AMI
const ami = new AsteriskAmi({ reconnect: true });
const amiConfig = {
    host: '127.0.0.1',
    port: 5038,
    username: 'admin', // Substitua pelo seu usuário AMI
    secret: 'MKsx2377!@',    // Substitua pela sua senha AMI
};

// Configuração de conexão com o banco de dados
const db = mysql.createPool({
    host: 'localhost',
    user: 'asterisk',       // Substitua pelo seu usuário do banco
    password: 'MKsx2377!@',     // Substitua pela sua senha do banco
    database: 'asterisk'  // Substitua pelo nome do banco de dados
});

// Função para salvar evento no banco de dados
async function salvarEvento(event) {
    try {
        // Atribui um valor para 'channel', caso não esteja presente no evento
        const channel = event.Channel || event.Uniqueid || "desconhecido"; 

        // Realiza a inserção no banco com o campo 'channel'
        const [result] = await db.execute(
            'INSERT INTO call_events (event_type, event_data, timestamp, channel) VALUES (?, ?, NOW(), ?)',
            [event.Event, JSON.stringify(event), channel]
        );
        console.log(`Evento ${event.Event} salvo com ID:`, result.insertId);
    } catch (error) {
        console.error('Erro ao salvar evento no banco:', error);
    }
}

// Captura de eventos específicos
ami.on('event', (event) => {
    console.log('Evento recebido:', event);

    // Filtrar eventos importantes
    if (event.Event === 'DialBegin') {
        console.log('Chamada iniciada:', event);
        salvarEvento(event);
    }

    if (event.Event === 'Hangup') {
        console.log('Chamada encerrada:', event);
        salvarEvento(event);
    }

    if (event.Event === 'NewState' && event.ChannelStateDesc === 'Ringing') {
        console.log('Ramal tocando:', event);
        salvarEvento(event);
    }

    if (event.Event === 'BridgeEnter') {
        console.log('Chamada conectada:', event);
        salvarEvento(event);
    }

    if (event.Event === 'BridgeLeave') {
        console.log('Chamada desconectada:', event);
        salvarEvento(event);
    }
});

// Tratamento de erros no AMI
ami.on('error', (error) => {
    console.error('Erro no AMI:', error);
});

// Conexão ao AMI
ami.connect(amiConfig.username, amiConfig.secret, { host: amiConfig.host, port: amiConfig.port })
    .then(() => {
        console.log('Conectado ao AMI com sucesso!');
    })
    .catch((error) => {
        console.error('Erro ao conectar ao AMI:', error);
    });

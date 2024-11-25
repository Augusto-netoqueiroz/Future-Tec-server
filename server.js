import { createServer } from 'http';
import { Server } from 'socket.io';
import AsteriskManager from 'asterisk-manager';
import odbc from 'odbc'; // Importar o pacote ODBC
import dotenv from 'dotenv'; // Para carregar variáveis do arquivo .env

// Carregar variáveis de ambiente
dotenv.config();

// Criar servidor HTTP
const httpServer = createServer();

// Criar servidor Socket.IO com configurações CORS
const io = new Server(httpServer, {
    cors: {
        origin: "http://93.127.212.237:8082", // Substitua pela URL do seu frontend
        methods: ['GET', 'POST'],
        allowedHeaders: ['Content-Type'],
        credentials: false, // Permitir cookies e headers de autenticação
    },
});

// Configurar conexão ODBC
const connectionString = process.env.ODBC_CONNECTION_STRING; // Conexão ODBC definida no .env
let connection;

// Conectar ao banco de dados ODBC
async function connectToDB() {
    try {
        connection = await odbc.connect(connectionString);
        console.log("Conexão ODBC estabelecida com sucesso!");
    } catch (error) {
        console.error("Erro ao conectar ao banco de dados ODBC:", error);
    }
}

// Criar instância do Asterisk Manager Interface (AMI)
const ami = new AsteriskManager(5038, 'localhost', 'admin', 'MKsx2377!@', true);

// Mapa para rastrear chamadas ativas
const activeCalls = new Map();
const loggedHangups = new Set(); // Rastreamento de chamadas finalizadas para evitar duplicação
let callIntervals = new Map();  // Map para gerenciar os intervalos de chamadas

// Capturar eventos do AMI
ami.on('managerevent', (event) => {
    // Emitir os logs para o frontend
    io.emit('new_event', event);

    // Quando o ramal inicia uma ligação (ligando)
    if (event.event === 'Newchannel' && event.privilege === 'call,all' && event.exten !== 's') {
        const chamador = event.calleridnum;  // Número do chamador
        const destinatario = event.exten;  // Número ou ramal do destinatário
        console.log(`status: ligando - Chamador: ${chamador}, Destinatário: ${destinatario}`);
    }

    // Quando o ramal está tocando
    if (event.event === 'Newstate' && event.channelstatedesc === 'Ringing') {
        const chamador = event.calleridnum;  // Número do chamador
        const destinatario = event.exten;  // Número ou ramal do destinatário
        console.log(`status: tocando - Chamador: ${chamador}, Ramal: ${destinatario}`);
    }

    // Quando a ligação é estabelecida (call answered)
    if (event.event === 'VarSet' && event.channelstatedesc === 'Up') {
        const callKey = event.channel; // Identificador único para a chamada

        // Verificar se o destinatário (exten) é válido
        if (event.exten && event.exten.trim() !== '') {
            const recipient = event.exten;

            if (!activeCalls.has(callKey)) {
                const startTime = Date.now();
                activeCalls.set(callKey, {
                    startTime,
                    originator: event.calleridnum || event.channel,
                    recipient,
                    isFinished: false
                });

                console.log(`status: em ligação - Chamador: ${event.calleridnum || event.channel}, Destinatário: ${recipient}`);

                // Atualizar a duração em tempo real a cada segundo
                const interval = setInterval(() => {
                    const callData = activeCalls.get(callKey);

                    if (callData && !callData.isFinished) {
                        const elapsed = ((Date.now() - callData.startTime) / 1000).toFixed(2);
                        console.log(`status: duração - ${elapsed}s - Chamador: ${callData.originator}, Destinatário: ${callData.recipient}`);
                    } else {
                        clearInterval(interval);
                        callIntervals.delete(callKey);
                    }
                }, 1000);

                callIntervals.set(callKey, interval);
            }
        }
    }

    // Quando a ligação é finalizada (Hangup)
    if ((event.event === 'HangupRequest' || event.event === 'Hangup') && event.channel) {
        const callKey = event.channel; // Identificador único para a chamada

        // Prevenir logs duplicados
        if (!loggedHangups.has(callKey)) {
            loggedHangups.add(callKey);

            if (activeCalls.has(callKey)) {
                const callData = activeCalls.get(callKey);

                if (callData && !callData.isFinished) {
                    const duration = ((Date.now() - callData.startTime) / 1000).toFixed(2);

                    console.log(`status: ligação encerrada por - ${event.channel} (Razão: ${event.cause})`);
                    console.log(`status: duração final - ${duration}s - Chamador: ${callData.originator}, Destinatário: ${callData.recipient}`);

                    // Atualizar a flag isFinished para que o contador pare
                    callData.isFinished = true;

                    // Limpar o intervalo para a duração da chamada
                    clearInterval(callIntervals.get(callKey));

                    // Remover a chamada ativa do mapa
                    activeCalls.delete(callKey);
                    callIntervals.delete(callKey);  // Remover o intervalo do mapa também

                    console.log(`status: chamada removida do mapa - ${callKey}`);
                }
            }
        }
    }

    // Caso o ramal que originou a chamada (201) desliga, garantir que o tempo de duração não continue
    if ((event.event === 'HangupRequest' || event.event === 'Hangup') && event.channel && activeCalls.has(event.channel)) {
        const callKey = event.channel;

        if (activeCalls.has(callKey)) {
            const callData = activeCalls.get(callKey);
            // Se a chamada foi finalizada, mas o tempo está sendo contado erroneamente, a correção seria parar o intervalo.
            if (callData && !callData.isFinished) {
                clearInterval(callIntervals.get(callKey)); // Garantir que o intervalo de tempo seja interrompido.
                activeCalls.delete(callKey); // Remover o registro da chamada ativa.
                callIntervals.delete(callKey); // Garantir a limpeza do intervalo.
                console.log(`status: tempo da chamada interrompido para ${callKey}`);
            }
        }
    }
});


// Lidar com erros de AMI
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

// Iniciar o servidor Socket.IO na porta 3000
httpServer.listen(3000, '0.0.0.0', () => {
    console.log('Servidor Socket.IO rodando na porta 3000...');
});

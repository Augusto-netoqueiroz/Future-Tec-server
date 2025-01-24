import { createServer } from 'http';
import { Server } from 'socket.io';
import AsteriskManager from 'asterisk-manager';
import odbc from 'odbc';
import dotenv from 'dotenv';

dotenv.config(); // Carregar variáveis de ambiente do arquivo .env

const httpServer = createServer();
const httpServerWapp = createServer();

const io = new Server(httpServer, {
    cors: {
        origin: "http://93.127.212.237:8082",
        methods: ['GET', 'POST'],
        allowedHeaders: ['Content-Type'],
        credentials: false,
    },
});

const ioWapp = new Server(httpServerWapp, {
    cors: {
        origin: "http://93.127.212.237:8082",
        methods: ['GET', 'POST'],
        allowedHeaders: ['Content-Type'],
        credentials: false,
    },
});

let connection; // Declaração da variável no escopo global

// Maps para rastreamento
const activeCalls = new Map();
const loggedHangups = new Set();
const callIntervals = new Map();
const agentStatus = new Map();
const queueStatus = new Map();
const channelStates = new Map();

async function connectToDB() {
    try {
        // Exibindo mensagem de início de conexão
        console.log("Iniciando conexão com o banco de dados...");

        // Configurações da conexão ODBC
        const connectionConfig = {
            connectionString: 'DSN=asterisk-connector', // Nome do DSN configurado no ODBC
            host: process.env.DB_HOST, // Host do banco de dados
            port: process.env.DB_PORT, // Porta do banco de dados
            database: process.env.DB_DATABASE, // Nome do banco de dados
            user: process.env.DB_USERNAME, // Usuário do banco de dados
            password: process.env.DB_PASSWORD // Senha do banco de dados
        };

        // Verificação de saída das configurações (para depuração)
        console.log("Configuração de conexão:", connectionConfig);

        // Tentativa de conexão ao banco de dados
        connection = await odbc.connect(connectionConfig);

        // Mensagem de sucesso na conexão
        console.log("Conexão ODBC estabelecida com sucesso!");

    } catch (error) {
        // Exibição de erros durante a conexão
        console.error("Erro ao conectar ao banco de dados ODBC:", error);
    }
}

// Chamando a função para testar a conexão
connectToDB();

// Configuração do AMI
const ami = new AsteriskManager(5038, 'localhost', 'admin', 'MKsx2377!@', true);

// Função para emitir eventos para todos os clientes
function emitToAll(eventName, data) {
    io.emit(eventName, data);
    ioWapp.emit(eventName, data);
}



// Função para atualizar status do canal
function updateChannelState(channel, state) {
    channelStates.set(channel, state);
    emitToAll('channel_state_update', { channel, state });
}

// Monitoramento de Eventos AMI
ami.on('managerevent', async (event) => {
    console.log("Evento recebido do AMI:", event.event, event);

    emitToAll('new_event', event);

    try {
        switch (event.event) {
            case 'Newchannel':
                console.log("Novo canal detectado:", event);
                handleNewChannel(event);
                break;
            case 'Newstate':
                console.log("Novo estado detectado:", event);
                handleNewState(event);
                break;
            case 'Bridge':
                console.log("Nova ponte detectada:", event);
                handleBridge(event);
                break;
            case 'Hangup':
                console.log("Desligamento detectado:", event);
                handleHangup(event);
                break;
            case 'AgentLogin':
                console.log("Login de agente detectado:", event);
                handleAgentLogin(event);
                break;
            case 'AgentLogoff':
                console.log("Logout de agente detectado:", event);
                handleAgentLogoff(event);
                break;
            case 'QueueMemberStatus':
                console.log("Status de membro de fila detectado:", event);
                handleQueueMemberStatus(event);
                break;
            case 'Hold':
                console.log("Chamada em espera detectada:", event);
                handleHold(event);
                break;
            case 'Unhold':
                console.log("Chamada retirada da espera detectada:", event);
                handleUnhold(event);
                break;
            default:
                console.log("Evento desconhecido:", event.event);
        }
    } catch (error) {
        console.error("Erro ao processar o evento AMI:", error);
    }
});

// Handlers específicos para cada tipo de evento
function handleNewChannel(event) {
    console.log("Processando novo canal:", event);

    if (event.privilege === 'call,all' && event.exten !== 's') {
        const callData = {
            channel: event.channel,
            caller: event.calleridnum,
            destination: event.exten,
            startTime: Date.now(),
            status: 'iniciando'
        };
        
        activeCalls.set(event.channel, callData);
        updateChannelState(event.channel, 'iniciando');
        emitToAll('call_started', callData);
        logAmiEventToDB('call_start', callData);
    }
}

function handleNewState(event) {
    console.log("Processando novo estado:", event);

    if (event.channelstatedesc === 'Ringing') {
        const callData = {
            channel: event.channel,
            caller: event.calleridnum,
            destination: event.connectedlinenum,
            status: 'tocando'
        };
        
        updateChannelState(event.channel, 'tocando');
        emitToAll('call_ringing', callData);
        logAmiEventToDB('call_ringing', callData);
    }
}

function handleBridge(event) {
    console.log("Processando evento de ponte:", event);

    if (event.bridgestate === 'Link') {
        const call = activeCalls.get(event.channel1);
        if (call) {
            call.status = 'em_conversa';
            call.bridgeTime = Date.now();
            
            updateChannelState(event.channel1, 'em_conversa');
            emitToAll('call_answered', {
                channel: event.channel1,
                caller: call.caller,
                destination: call.destination
            });

            startCallDurationTimer(event.channel1);
        }
    }
}

function handleHangup(event) {
    console.log("Processando desligamento:", event);

    if (!loggedHangups.has(event.channel)) {
        loggedHangups.add(event.channel);
        const call = activeCalls.get(event.channel);
        
        if (call) {
            const duration = ((Date.now() - call.startTime) / 1000).toFixed(2);
            const hangupData = {
                channel: event.channel,
                caller: call.caller,
                destination: call.destination,
                duration: duration,
                cause: event.cause
            };

            stopCallDurationTimer(event.channel);
            activeCalls.delete(event.channel);
            updateChannelState(event.channel, 'finalizada');
            emitToAll('call_ended', hangupData);
            logAmiEventToDB('call_end', hangupData);
        }
    }
}

function handleAgentLogin(event) {
    console.log("Processando login de agente:", event);

    const agentData = {
        agent: event.agent,
        loginTime: Date.now()
    };
    
    agentStatus.set(event.agent, agentData);
    emitToAll('agent_login', agentData);
    logAmiEventToDB('agent_login', agentData);
}

function handleAgentLogoff(event) {
    console.log("Processando logout de agente:", event);

    const agentData = agentStatus.get(event.agent);
    if (agentData) {
        const duration = ((Date.now() - agentData.loginTime) / 1000).toFixed(2);
        agentStatus.delete(event.agent);
        emitToAll('agent_logoff', {
            agent: event.agent,
            duration: duration
        });
    }
}

function handleQueueMemberStatus(event) {
    console.log("Processando status de membro de fila:", event);

    const queueData = {
        queue: event.queue,
        member: event.member,
        status: event.status
    };
    
    queueStatus.set(`${event.queue}-${event.member}`, queueData);
    emitToAll('queue_member_status', queueData);
}

function handleHold(event) {
    console.log("Processando chamada em espera:", event);

    const call = activeCalls.get(event.channel);
    if (call) {
        call.status = 'em_espera';
        updateChannelState(event.channel, 'em_espera');
        emitToAll('call_hold', {
            channel: event.channel,
            caller: call.caller,
            destination: call.destination
        });
    }
}

function handleUnhold(event) {
    console.log("Processando chamada retirada da espera:", event);

    const call = activeCalls.get(event.channel);
    if (call) {
        call.status = 'em_conversa';
        updateChannelState(event.channel, 'em_conversa');
        emitToAll('call_unhold', {
            channel: event.channel,
            caller: call.caller,
            destination: call.destination
        });
    }
}

// Funções de utilidade para timer de chamadas
function startCallDurationTimer(channel) {
    console.log("Iniciando temporizador de duração para canal:", channel);

    const interval = setInterval(() => {
        const call = activeCalls.get(channel);
        if (call && !call.isFinished) {
            const duration = ((Date.now() - call.startTime) / 1000).toFixed(2);
            emitToAll('call_duration_update', {
                channel: channel,
                duration: duration,
                caller: call.caller,
                destination: call.destination
            });
        } else {
            stopCallDurationTimer(channel);
        }
    }, 1000);
    
    callIntervals.set(channel, interval);
}

function stopCallDurationTimer(channel) {
    console.log("Parando temporizador de duração para canal:", channel);

    const interval = callIntervals.get(channel);
    if (interval) {
        clearInterval(interval);
        callIntervals.delete(channel);
    }
}

// Comandos AMI
function originateCall(extension, context, priority, callerid) {
    return new Promise((resolve, reject) => {
        ami.action({
            action: 'Originate',
            channel: `SIP/${extension}`,
            context: context,
            priority: priority,
            callerid: callerid
        }, (err, res) => {
            if (err) reject(err);
            else resolve(res);
        });
    });
}




// Monitoramento de Eventos AMI
ami.on('managerevent', async (event) => {
    console.log("Evento recebido do AMI:", event.event, event);

    emitToAll('new_event', event);
    await logAmiEventToDB(event.event, event); // Logando o evento na tabela ami_logs

    try {
        switch (event.event) {
            // [Restante do código de manipulação de eventos permanece inalterado]
        }
    } catch (error) {
        console.error("Erro ao processar o evento AMI:", error);
    }
});



// Conexão AMI
ami.on('connected', () => {
    console.log('Conexão com AMI estabelecida com sucesso!');
});

ami.on('error', (err) => {
    console.error('Erro na conexão AMI:', err);
    emitToAll('ami_error', { error: err.message });
});

// Verificando status da conexão AMI periodicamente
setInterval(() => {
    console.log("Verificando a conexão com a AMI...");

    ami.action({
        action: 'Ping'
    }, (err, res) => {
        if (err) {
            console.error('Erro no ping AMI:', err);
            emitToAll('ami_status', { status: 'disconnected' });
        } else {
            console.log('Ping bem-sucedido:', res);
            emitToAll('ami_status', { status: 'connected' });
        }
    });
}, 30000);

// Iniciar o servidor Socket.IO na porta 3000
httpServer.listen(3000, '0.0.0.0', () => {
    console.log('Servidor Socket.IO rodando na porta 3000...');
});

// Iniciar o servidor Socket.IO na porta 3080 (nova instância)
httpServerWapp.listen(3080, '0.0.0.0', () => {
    console.log('Servidor Socket.IO (SocketWapp) rodando na porta 3080...');
});
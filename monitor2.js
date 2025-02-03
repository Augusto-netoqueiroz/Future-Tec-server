import { config } from "dotenv";
import { Server } from "socket.io";
import mysql from "mysql2";
import AsteriskManager from 'asterisk-manager';

// Carregar variáveis do .env
config();

// Configuração do banco de dados MySQL
const db = mysql.createConnection({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE,
});

// Verificar conexão com o banco
db.connect((err) => {
    if (err) {
        console.error("❌ Erro ao conectar no banco de dados:", err.message);
        return;
    }
    console.log("✅ Conectado ao banco de dados MySQL!");
});

// Configuração do servidor Socket.io
const io = new Server(4000, {
    cors: {
        origin: "http://93.127.212.237:8082",
        methods: ["GET", "POST"],
    },
});

console.log("🚀 Socket.io server is running on port 4000");

io.on("connection", (socket) => {
    console.log(`🟢 Cliente conectado: ${socket.id}`);

    socket.on("disconnect", () => {
        console.log(`🔴 Cliente desconectado: ${socket.id}`);
    });
});

// Configurar conexão AMI
const ami = new AsteriskManager('5038', 'localhost', process.env.AMI_USERNAME, process.env.AMI_PASSWORD, true);

ami.on('connect', () => {
    console.log('📞 Conectado ao AMI');
    fetchAndEmitUnifiedData();
});

// Tratamento de erros de conexão AMI
ami.on('error', (err) => {
    console.error('❌ Erro de conexão AMI:', err);
});

function fetchAndEmitUnifiedData() {
    fetchQueueData();
}

function fetchQueueData() {
    console.log("🔄 Buscando dados das filas...");
    
    ami.action(
        { action: 'Command', command: 'queue show' },
        (err, res) => {
            if (err || !res?.output) {
                console.error("❌ Erro ao buscar dados das filas:", err);
                return;
            }

            
           
         
        }
    );
}

function fetchActiveCalls(queueData) {
    console.log("🔄 Buscando chamadas ativas...");

    ami.action(
        { action: "Command", command: "core show channels verbose" },
        (err, res) => {
            if (err || !res?.output) {
                console.error("❌ Erro ao buscar canais ativos:", err);
                return;
            }

            const activeChannels = res.output
                .filter(line => line.startsWith("SIP/"))
                .map(parseChannelLine)
                .filter(Boolean);

            console.log("📞 Canais ativos obtidos:", activeChannels);

            const response = { activeChannels, queueData };
            io.emit("fetch-unified-data-response", response);

            console.log("📡 Dados enviados ao backend:", response);

            setTimeout(fetchAndEmitUnifiedData, 1000);
        }
    );
}

function parseChannelLine(line) {
    const modifiedLine = line.replace("Outgoing Line", "_Outgoing_Line");

    const regex = /^(\S+)\s+(\S*)\s+(\S*)\s+(\d+)\s+(\S+)\s+(\S+)\s+(.{1,30}?)\s{2,}(\S*)\s{2,}(\S*)\s{2,}(\S*)$/;
    const match = modifiedLine.match(regex);

    if (!match) {
        console.warn("⚠️ Falha ao processar linha:", modifiedLine);
        return null;
    }

    const [_, channel, context, extension, priority, state, application, data, callerID, duration, uniqueID] = match;

    return {
        channel,
        context: context || null,
        extension: extension || null,
        priority,
        state: getCallState(line),
        application,
        data: data.trim().replace("_Outgoing_Line", "Outgoing Line"),
        callerID: callerID || null,
        duration: duration || null,
        uniqueID: uniqueID || null,
    };
}

function getCallState(line) {
    if (/Up/.test(line)) return "Em Chamada";
    if (/Ringing/.test(line)) return "Tocando";
    if (/Ring/.test(line)) return "Discando";
    if (/Dial/.test(line)) return "Discando";
    return "Disponível";
}

// Captura eventos AMI e exibe no console
ami.on('managerevent', (event) => {
    console.log('📢 Evento AMI recebido:', event);

    switch (event.event) {
        case 'Newchannel':
            console.log(`📞 Nova chamada detectada: Canal: ${event.channel}, Ramal: ${event.extension}`);
            break;
        case 'Hangup':
            console.log(`🔚 Chamada finalizada: Canal: ${event.channel}, Status: ${event.status}`);
            break;
        case 'Peerstatus':
            console.log(`📡 Status do ramal: Ramal: ${event.peer}, Status: ${event.status}`);
            break;
        default:
            break;
    }
});

process.on('SIGINT', () => {
    ami.disconnect();
    console.log("🔴 Desconectado do AMI. Encerrando o processo...");
    process.exit();
});

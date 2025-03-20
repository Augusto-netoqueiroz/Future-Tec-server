import { config } from "dotenv";
import { Server } from "socket.io";
import mysql from "mysql2";
import AsteriskManager from 'asterisk-manager';

config();

const db = mysql.createConnection({
    host: process.env.DB_HOST,
    port: process.env.DB_PORT,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_DATABASE,
});

db.connect((err) => {
    if (err) {
        console.error("Erro ao conectar no banco de dados:", err.message);
        return;
    }
    console.log("Conectado ao banco de dados MySQL!");
});

const io = new Server(4000, {
    cors: {
        origin: "http://93.127.212.237:8082",
        methods: ["GET", "POST"],
    },
});

console.log("Socket.io server is running on port 4000");

io.on("connection", (socket) => {
    console.log(`Cliente conectado: ${socket.id}`);

    socket.on("disconnect", () => {
        console.log(`Cliente desconectado: ${socket.id}`);
    });
});

const ami = new AsteriskManager('5038', 'localhost', process.env.AMI_USERNAME, process.env.AMI_PASSWORD, true);

ami.on('connect', () => {
    console.log('Conectado ao AMI');
});

ami.on('error', (err) => {
    console.error('Erro de conexão AMI:', err);
});

ami.on('event', (event) => {
    console.log("Evento recebido do AMI:", event);
    const translatedEvent = translateAmiEvent(event);
    console.log("Evento traduzido:", translatedEvent);
    io.emit("ami-event", translatedEvent);
});

function translateAmiEvent(event) {
    if (event.Event === "Newchannel") {
        console.log(`Traduzindo evento Newchannel: Ramal ${event.CallerIDNum} recebendo ligação de ${event.ConnectedLineNum}`);
        return { mensagem: `Ramal ${event.CallerIDNum} recebendo ligação de ${event.ConnectedLineNum}` };
    }
    if (event.Event === "QueueCallerJoin") {
        console.log(`Traduzindo evento QueueCallerJoin: Fila ${event.Queue} recebendo ligação de ${event.CallerIDNum}`);
        return { mensagem: `Fila ${event.Queue} recebendo ligação de ${event.CallerIDNum}` };
    }
    if (event.Event === "Hangup") {
        console.log(`Traduzindo evento Hangup: Chamada encerrada no ramal ${event.Channel}`);
        return { mensagem: `Chamada encerrada no ramal ${event.Channel}` };
    }
    console.log("Evento não identificado, enviando como 'Outro evento'");
    return { mensagem: "Outro evento", dados: event };
}

process.on('SIGINT', () => {
    console.log("Desconectando do AMI e encerrando processo...");
    ami.disconnect();
    process.exit();
});

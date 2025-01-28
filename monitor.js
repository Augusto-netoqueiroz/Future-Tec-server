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
        console.error("Erro ao conectar no banco de dados:", err.message);
        return;
    }
    console.log("Conectado ao banco de dados MySQL!");
});

// Criação do servidor Socket.io na porta 4000
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

// Configurar conexão AMI
const ami = new AsteriskManager('5038', 'localhost', process.env.AMI_USERNAME, process.env.AMI_PASSWORD, true);

// Conexão bem-sucedida com AMI
ami.on('connect', () => {
    console.log('Conectado ao AMI');

    // Executar comando para monitorar as filas periodicamente
    setInterval(() => {
        ami.action({
            action: 'Command',  
            command: 'queue show'
        }, (err, res) => {
            if (err) {
                console.error('Erro ao executar o comando:', err);
            } else {
                // Emitir resposta para todos os clientes conectados via socket.io
                io.emit('queue-data', res);
            }
        });
    }, 2000); // Intervalo de 5 segundos entre as execuções
});

// Tratamento de erros de conexão AMI
ami.on('error', (err) => {
    console.error('Erro de conexão AMI:', err);
});

function fetchAndEmitUnifiedData() {
    // Buscar ramais (sippeers)
    const querySippeers = `
    SELECT name, ipaddr, modo, user_id, user_name 
    FROM sippeers
    WHERE modo = 'ramal' AND LENGTH(ipaddr) > 6
    `;

    db.query(querySippeers, (err, sippersResults) => {
        if (err) {
            console.error("Erro ao buscar ramais no banco:", err);
            io.emit("fetch-data-error", { 
                type: "sippers",
                message: "Erro ao buscar dados dos ramais.", 
                details: err.message 
            });
            return;
        }

        // Adicionar dados das pausas aos ramais
        const userIds = sippersResults.map(sipper => sipper.user_id).filter(id => id);
        const pausesMap = {};

        const enrichSippeersData = () => {
            const enrichedResults = sippersResults.map(sipper => {
                const pauseData = pausesMap[sipper.user_id];
                let timeInPause = null;

                if (pauseData && pauseData.started_at) {
                    const pauseStart = new Date(pauseData.started_at);
                    const now = new Date();
                    const diffMs = now - pauseStart;
                    const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                    const diffMins = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                    const diffSecs = Math.floor((diffMs % (1000 * 60)) / 1000);

                    timeInPause = `${String(diffHrs).padStart(2, '0')}:${String(diffMins).padStart(2, '0')}:${String(diffSecs).padStart(2, '0')}`;
                }

                return {
                    ...sipper,
                    pause_name: pauseData?.pause_name || null,
                    started_at: pauseData?.started_at || null,
                    time_in_pause: timeInPause,
                };
            });

            fetchAndEmitRawChannels(enrichedResults); // Continuar com a lógica de canais
        };

        if (userIds.length > 0) {
            const queryPauses = `
            SELECT user_id, pause_name, started_at
            FROM user_pause_logs
            WHERE user_id IN (${userIds.map(() => '?').join(', ')})
            ORDER BY started_at DESC
            `;

            db.query(queryPauses, userIds, (err, pausesResults) => {
                if (err) {
                    console.error("Erro ao buscar dados das pausas:", err);
                    io.emit("fetch-data-error", { 
                        type: "sippers",
                        message: "Erro ao buscar dados das pausas.", 
                        details: err.message 
                    });
                    return;
                }

                pausesResults.forEach(pause => {
                    if (!pausesMap[pause.user_id]) {
                        pausesMap[pause.user_id] = {
                            pause_name: pause.pause_name,
                            started_at: pause.started_at,
                        };
                    }
                });

                enrichSippeersData();
            });
        } else {
            enrichSippeersData();
        }
    });
}

function fetchAndEmitRawChannels(enrichedSippeers) {
    // Enviar o comando "core show channels verbose"
    ami.action(
        {
            action: "Command",
            command: "core show channels verbose",
        },
        (err, res) => {
            if (err) {
                console.error("Erro ao buscar canais ativos:", err);
                io.emit("fetch-data-error", {
                    type: "channels",
                    message: "Erro ao buscar canais ativos.",
                    details: err.message,
                });
                return;
            }

            // Validar se há uma resposta válida e se contém a saída esperada
            if (!res || !Array.isArray(res.output)) {
                console.error("Formato inesperado da resposta do AMI:", res);
                io.emit("fetch-data-error", {
                    type: "channels",
                    message: "Formato inesperado da resposta do AMI.",
                    details: res,
                });
                return;
            }

          // Combine os dados dos ramais com os canais ativos
const activeChannels = res.output.filter(line => line.startsWith("SIP/")).map(line => {
    return line.trim(); // Mantém a string raw, sem separar em partes
});


            // Mapear os ramais com canais ativos
            const finalData = enrichedSippeers.map(sipper => {
                const activeChannel = activeChannels.find(channel => channel.channel.includes(sipper.name));
                return {
                    ...sipper,
                    call_state: activeChannel ? activeChannel.state : "Disponível",
                    call_duration: activeChannel ? activeChannel.duration : null,
                };
            });

            // Emitir os dados combinados
            io.emit("fetch-unified-data-response", finalData);
        }
    );
}

// Iniciar o polling combinado
setInterval(fetchAndEmitUnifiedData, 2000);



// Manter a conexão ativa e desconectar do AMI corretamente
process.on('SIGINT', () => {
    ami.disconnect();
    process.exit();
});""

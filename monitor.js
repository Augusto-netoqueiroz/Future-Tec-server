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
    }, 2000); // Intervalo de 2 segundos entre as execuções
});

// Tratamento de erros de conexão AMI
ami.on('error', (err) => {
    console.error('Erro de conexão AMI:', err);
});

// Função para buscar e emitir dados unificados
function fetchAndEmitUnifiedData() {
    const querySippeers = `
    SELECT name, ipaddr, modo, user_id, user_name, empresa_id 
    FROM sippeers
    WHERE modo = 'ramal' AND LENGTH(ipaddr) > 6
`;

    db.query(querySippeers, (err, sippersResults) => {
        if (err) {
            console.error("Erro ao buscar ramais no banco:", err);
            return;
        }

        const userIds = sippersResults.map(sipper => sipper.user_id).filter(id => id);
        const pausesMap = {};

        const enrichSippeersData = () => {
            const enrichedResults = sippersResults.map(sipper => {
                const pauseData = pausesMap[sipper.user_id];
                let timeInPause = "00:00:00";
            
                if (pauseData?.started_at) {
                    const pauseStart = new Date(pauseData.started_at);
                    const now = new Date();
            
                    if (!isNaN(pauseStart)) {
                        const diffMs = now - pauseStart;
                        const hours = Math.floor(diffMs / (1000 * 60 * 60));
                        const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((diffMs % (1000 * 60)) / 1000);
            
                        timeInPause = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    }
                }
            
                return {
                    ...sipper,
                    pause_name: pauseData?.pause_name || null,
                    started_at: pauseData?.started_at || null,
                    time_in_pause: timeInPause,
                    empresa_id: sipper.empresa_id  // ✅ Adicionando empresa_id ao objeto final
                };
            });
            

            fetchAndEmitRawChannels(enrichedResults); // Continuação
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

// Função para processar o estado da chamada
function getCallState(line) {
    if (/Up/.test(line)) return "Em Chamada";
    if (/Ringing/.test(line)) return "Tocando";
    if (/Ring/.test(line)) return "Discando";
    if (/Dial/.test(line)) return "Discando";
    return "Disponível";
}

// Função para buscar e emitir dados dos canais ativos
function fetchAndEmitRawChannels(enrichedSippeers) {
    ami.action(
        {
            action: "Command",
            command: "core show channels verbose",
        },
        (err, res) => {
            if (err) {
                console.error("Erro ao buscar canais ativos:", err);
                return;
            }

            if (!res || !Array.isArray(res.output)) {
                console.error("Formato inesperado da resposta do AMI:", res);
                return;
            }

            const activeChannels = res.output
            .filter(line => line.startsWith("SIP/"))
            .map(line => {
                const modifiedLine = line.replace("Outgoing Line", "_Outgoing_Line");
        
                const regex = /^(\S+)\s+(\S*)\s+(\S*)\s+(\d+)\s+(\S+)\s+(\S+)\s+(.{1,30}?)\s{2,}(\S*)\s{2,}(\S*)\s{2,}(\S*)$/;
                const match = modifiedLine.match(regex);
        
                if (!match) {
                    console.warn("Falha ao processar linha:", modifiedLine);
                    return null;
                }
        
                const [
                    _,   // match[0] é a linha completa, descartamos
                    channel, context, extension, priority, state, application, data, callerID, duration, uniqueID
                ] = match;
        
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
                    uniqueID: uniqueID || null
                };
            }).filter(Boolean);

            const finalData = enrichedSippeers.map(sipper => {
    const ramal = sipper.name.replace("SIP/", ""); // Remove "SIP/" se existir
    const queueName = queueMapping[ramal] || null;
    
    // 🔹 Busca o canal ativo correspondente ao ramal
    const activeChannel = activeChannels.find(ch => ch.channel.includes(sipper.name));

    //console.log(`🔍 Verificando Ramal: ${sipper.name} (${ramal}), Fila: ${queueName}`);

    return {
        ...sipper,
        call_state: activeChannel ? activeChannel.state : "Disponível",
        call_duration: activeChannel ? activeChannel.duration : null,
        calling_from: activeChannel ? sipper.name : null,
        calling_to: activeChannel ? activeChannel.extension : null,
        uniqueID: activeChannel ? activeChannel.uniqueID : null,
        queueName: queueName  // 🔹 Adiciona a fila associada ao ramal, se existir
    };
});


            fetchQueueData(finalData);
            fetchAndEmitQueueWithChannels(finalData);
        }
    );
}

// Função para buscar dados das filas
function fetchQueueData(finalData) {
    ami.action(
        {
            action: 'Command',
            command: 'queue show'
        },
        (err, res) => {
            if (err) {
                console.error("Erro ao buscar dados das filas:", err);
                return;
            }

            const queueData = res?.output || [];
            const processedQueueData = processQueueData(queueData);

            // Obter os nomes das filas únicas para buscar os respectivos empresa_id
            const queueNames = [...new Set(processedQueueData.map(q => q.queueName))];

            if (queueNames.length === 0) {
                emitirDados(finalData, processedQueueData);
                return;
            }

            // Buscar os empresa_id das filas no banco de dados
            const placeholders = queueNames.map(() => '?').join(', ');
            const query = `SELECT name, empresa_id FROM queues WHERE name IN (${placeholders})`;

            db.query(query, queueNames, (err, results) => {
                if (err) {
                    console.error("Erro ao buscar empresa_id das filas:", err);
                    emitirDados(finalData, processedQueueData);
                    return;
                }

                // Criar um mapa para relacionar queueName -> empresa_id
                const empresaMap = {};
                results.forEach(row => {
                    empresaMap[row.name] = row.empresa_id;
                });

                // Adicionar empresa_id às filas processadas
                const enrichedQueueData = processedQueueData.map(queue => ({
                    ...queue,
                    empresa_id: empresaMap[queue.queueName] || null  // Se não encontrar, retorna null
                }));

                // Emitir os dados com empresa_id incluído
                emitirDados(finalData, enrichedQueueData);
            });
        }
    );
}

// Função para emitir os dados finais via Socket.io
function emitirDados(sippeers, queueData) {
    io.emit("fetch-unified-data-response", {
        sippeers,
        queueData
    });

    // Próximo ciclo de execução
    setTimeout(fetchAndEmitUnifiedData, 1000);
}


// Função para processar as filas
function processQueueData(queueData) {
    const filteredQueueData = [];
    let currentQueue = null;
    let callers = [];

    queueData.forEach(line => {
        if (line.match(/^\S+ has \d+ calls/)) {
            if (currentQueue && callers.length > 0) {
                filteredQueueData.push({
                    queueName: currentQueue,
                    callers: callers
                });
            }

            callers = [];
            currentQueue = line.split(' has ')[0];
        }

        if (line.match(/^\s+\d+\.\s+SIP/)) {
            const callerData = extractCallerData(line);
            if (callerData) {
                callers.push(callerData);
            }
        }
    });

    if (currentQueue && callers.length > 0) {
        filteredQueueData.push({
            queueName: currentQueue,
            callers: callers
        });
    }

    return filteredQueueData;
}

// Função para extrair os dados do caller
function extractCallerData(line) {
    // Ajuste da expressão regular para lidar com variações
    const regex = /^\s+(\d+)\.\s+SIP\/([^-\s]+)-?\S*\s+\(wait:\s+(\S+),\s+prio:\s+(\d+)\)/;
    const match = line.match(regex);

    if (match) {
        return {
            priority: match[1],
            caller: match[2],  // Agora vai capturar apenas o que está entre SIP/ e -
            waitTime: match[3]
        };
    }

    console.warn("Não foi possível extrair dados do caller:", line);  // Adicionando um log para verificar o erro
    return null;
}


// Inicia o loop com 1 execução por segundo
fetchAndEmitUnifiedData();

let queueMapping = {}; // Guarda ramais em ligação e suas filas



function fetchAndEmitQueueWithChannels(callback) {
    ami.action(
        {
            action: 'Command',
            command: 'queue show'
        },
        (err, res) => {
            if (err) {
                console.error("Erro ao buscar dados das filas para associação com ramais:", err);
                return;
            }

            const queueData = res?.output || [];
            queueMapping = {}; // Resetar antes de preencher
            let currentQueue = null;
            let queueNames = new Set();

            queueData.forEach(line => {
                const queueMatch = line.match(/^(\S+)\s+has\s+\d+\s+calls/);
                if (queueMatch) {
                    currentQueue = queueMatch[1]; // Nome da fila
                    queueNames.add(currentQueue);
                }

                if (currentQueue && line.includes("(in call)")) {
                    const ramalMatch = line.match(/SIP\/(\d+)/);
                    if (ramalMatch) {
                        const ramal = ramalMatch[1];
                        queueMapping[ramal] = { queueName: currentQueue };
                    }
                }
            });

            // 🔍 Buscar empresa_id de cada queueName
            if (queueNames.size > 0) {
                const query = `SELECT name, empresa_id FROM queues WHERE name IN (${Array.from(queueNames).map(() => '?').join(', ')})`;
                db.query(query, Array.from(queueNames), (err, results) => {
                    if (err) {
                        console.error("Erro ao buscar empresa_id das filas:", err);
                        return;
                    }

                    // Atualizar queueMapping com empresa_id
                    results.forEach(row => {
                        Object.keys(queueMapping).forEach(ramal => {
                            if (queueMapping[ramal].queueName === row.name) {
                                queueMapping[ramal].empresa_id = row.empresa_id;
                            }
                        });
                    });

                    // Verifica se callback é uma função antes de chamar
                    if (typeof callback === "function") {
                        callback();
                    }
                });
            } else {
                // Se não houver filas, apenas executa o callback
                if (typeof callback === "function") {
                    callback();
                }
            }
        }
    );
}


// Manter a conexão ativa e desconectar do AMI corretamente
process.on('SIGINT', () => {
    ami.disconnect();
    process.exit();
});

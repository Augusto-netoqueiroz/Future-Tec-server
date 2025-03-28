// Adaptado para usar apenas eventos AMI, mantendo a estrutura original com logs detalhados e correções de estado
import fs from 'fs';
import https from 'https';
import { config } from 'dotenv';
import { Server } from 'socket.io';
import mysql from 'mysql2';
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
  if (err) return console.error('Erro ao conectar no banco de dados:', err.message);
  console.log('Conectado ao banco de dados MySQL!');
});

const sslOptions = {
  key: fs.readFileSync('/var/www/projetolaravel/certificados/fttelecom.cloud/key.pem'),
  cert: fs.readFileSync('/var/www/projetolaravel/certificados/fttelecom.cloud/fullchain.pem'),
};

const httpsServer = https.createServer(sslOptions);
const io = new Server(httpsServer, {
  cors: { origin: 'https://fttelecom.cloud', methods: ['GET', 'POST'] },
});

httpsServer.listen(4000, () => {
  console.log('🔐 Servidor HTTPS com Socket.io rodando na porta 4000');
});

io.on('connection', (socket) => {
  console.log(`Cliente conectado: ${socket.id}`);
  socket.on('disconnect', () => console.log(`Cliente desconectado: ${socket.id}`));
});

const ami = new AsteriskManager('5038', 'localhost', process.env.AMI_USERNAME, process.env.AMI_PASSWORD, true);

const activeChannels = {};
const queueState = {};
let queueMapping = {}; // ramal -> { queueName, empresa_id }

ami.on('event', (event) => {
  // Captura adicional para compatibilidade com ODBC
  if (event.Event === 'DialBegin') {
    console.log('📞 DialBegin detectado:', event);
    const uid = event.Uniqueid;
    activeChannels[uid] = {
      channel: event.DestChannel || event.Channel,
      context: event.Context || null,
      extension: event.DestExtension || null,
      priority: event.Priority || null,
      state: 'Discando',
      application: 'Dial',
      data: null,
      callerID: event.CallerIDNum,
      duration: '00:00',
      uniqueID: uid,
    };
  }

  if (event.Event === 'DialEnd') {
    console.log('📞 DialEnd detectado:', event);
    const uid = event.Uniqueid;
    if (activeChannels[uid]) {
      activeChannels[uid].state = event.DialStatus === 'ANSWER' ? 'Em Chamada' : 'Não Atendido';
    }
  }

  if (event.Event === 'BridgeCreate') {
    console.log('🔗 BridgeCreate detectado:', event);
  }

  if (event.Event === 'OriginateResponse') {
    console.log('📤 OriginateResponse recebido:', event);
  }

  const uid = event.Uniqueid;
  console.log('📥 Evento AMI recebido:', event.Event, '| Canal:', event.Channel, '| UID:', uid);

  switch (event.Event) {
    case 'Newchannel':
      if (event.Channel.startsWith('SIP/')) {
        console.log('🔄 Novo canal SIP detectado:', event.Channel);
        activeChannels[uid] = {
          channel: event.Channel,
          context: event.Context || null,
          extension: event.Extension || null,
          priority: event.Priority || null,
          state: 'Criando canal',
          application: null,
          data: null,
          callerID: event.CallerIDNum,
          duration: '00:00',
          uniqueID: uid,
        };
      }
      break;

    case 'Newstate':
      if (activeChannels[uid]) {
        console.log(`📞 Estado do canal atualizado (${uid}):`, event.ChannelStateDesc || event.ChannelState);
        activeChannels[uid].state = event.ChannelStateDesc || mapStateFromCode(event.ChannelState);
      } else {
        console.log(`⚠️ Canal não encontrado para estado:`, event);
      }
      break;

    case 'BridgeEnter':
      if (activeChannels[uid]) {
        console.log(`🔗 Início de ligação (${uid}) entre ${event.CallerID1} e ${event.CallerID2}`);
        activeChannels[uid].state = 'Em Chamada';
        activeChannels[uid].connectedTo = event.CallerID2;
      } else {
        console.log(`⚠️ Canal não encontrado para BridgeEnter:`, event);
      }
      break;

    case 'Hangup':
      console.log(`❌ Canal encerrado (${uid}):`, event.Channel);
      if (activeChannels[uid]) delete activeChannels[uid];
      break;

    case 'QueueCallerJoin':
      console.log(`📥 Chamador entrou na fila ${event.Queue}: ${event.CallerIDNum}`);
      if (!queueState[event.Queue]) queueState[event.Queue] = [];
      queueState[event.Queue].push({
        caller: event.CallerIDNum,
        waitTime: '00:00',
        priority: event.Priority || '0',
      });
      break;

    case 'QueueCallerLeave':
      console.log(`📤 Chamador saiu da fila ${event.Queue}: ${event.CallerIDNum}`);
      if (queueState[event.Queue]) {
        queueState[event.Queue] = queueState[event.Queue].filter(c => c.caller !== event.CallerIDNum);
      }
      break;
  }
});

function mapStateFromCode(code) {
  switch (parseInt(code)) {
    case 0: return 'Idle';
    case 1: return 'Down';
    case 2: return 'Rsrvd';
    case 3: return 'OffHook';
    case 4: return 'Dialing';
    case 5: return 'Ring';
    case 6: return 'Ringing';
    case 7: return 'Up';
    case 8: return 'Busy';
    default: return 'Desconhecido';
  }
}

function getCallState(desc) {
  const value = desc?.toLowerCase() || '';
  console.log('🧪 Analisando estado bruto do canal:', value);

  if (value.includes('up')) return 'Em Chamada';
  if (value.includes('ringing')) return 'Tocando';
  if (value.includes('dial') || value.includes('progress') || value.includes('offhook')) return 'Discando';
  if (value.includes('busy')) return 'Ocupado';

  return 'Disponível';
}

function fetchAndEmitUnifiedData() {
  console.log('📊 activeChannels atuais:', activeChannels);

  db.query(
    `SELECT name, ipaddr, modo, user_id, user_name, empresa_id FROM sippeers WHERE modo = 'ramal' AND LENGTH(ipaddr) > 6`,
    (err, sippersResults) => {
      if (err) return console.error('Erro ao buscar ramais no banco:', err);

      const userIds = sippersResults.map(s => s.user_id).filter(Boolean);
      const pausesMap = {};

      const enrichSippeers = () => {
        const enriched = sippersResults.map(sipper => {
          const pause = pausesMap[sipper.user_id];
          let timeInPause = '00:00:00';

          if (pause?.started_at) {
            const diffMs = new Date() - new Date(pause.started_at);
            const h = String(Math.floor(diffMs / 3600000)).padStart(2, '0');
            const m = String(Math.floor((diffMs % 3600000) / 60000)).padStart(2, '0');
            const s = String(Math.floor((diffMs % 60000) / 1000)).padStart(2, '0');
            timeInPause = `${h}:${m}:${s}`;
          }

          const ramal = sipper.name.replace('SIP/', '');
          const active = Object.values(activeChannels).find(ch => ch.channel.includes(sipper.name) || ch.channel.includes(`SIP/${ramal}-`));
          const queueInfo = queueMapping[ramal] || {};

          if (active) {
            console.log(`🔍 Match ativo para ramal ${sipper.name}: estado = ${active.state}, UID = ${active.uniqueID}`);
          }

          return {
            ...sipper,
            pause_name: pause?.pause_name || null,
            started_at: pause?.started_at || null,
            time_in_pause: timeInPause,
            call_state: active ? getCallState(active.state) : 'Disponível',
            call_duration: active?.duration || null,
            calling_from: active ? sipper.name : null,
            calling_to: active?.connectedTo || null,
            uniqueID: active?.uniqueID || null,
            queueName: queueInfo.queueName || null,
            channel: active?.channel || null,
          };
        });

        fetchQueueData(enriched);
      };

      if (userIds.length === 0) return enrichSippeers();

      db.query(
        `SELECT user_id, pause_name, started_at FROM user_pause_logs WHERE user_id IN (${userIds.map(() => '?').join(', ')}) ORDER BY started_at DESC`,
        userIds,
        (err, pauseResults) => {
          if (err) return console.error('Erro ao buscar pausas:', err);
          pauseResults.forEach(p => {
            if (!pausesMap[p.user_id]) {
              pausesMap[p.user_id] = p;
            }
          });
          enrichSippeers();
        }
      );
    }
  );
}

function fetchQueueData(finalData) {
  const queueData = Object.entries(queueState).map(([queueName, callers]) => ({
    queueName,
    callers
  }));

  const queueNames = queueData.map(q => q.queueName);
  if (queueNames.length === 0) return emitirDados(finalData, queueData);

  const query = `SELECT name, empresa_id FROM queues WHERE name IN (${queueNames.map(() => '?').join(', ')})`;
  db.query(query, queueNames, (err, results) => {
    if (err) return emitirDados(finalData, queueData);
    const empresaMap = {};
    results.forEach(row => (empresaMap[row.name] = row.empresa_id));

    const enrichedQueueData = queueData.map(queue => ({
      ...queue,
      empresa_id: empresaMap[queue.queueName] || null,
    }));

    emitirDados(finalData, enrichedQueueData);
  });
}

function emitirDados(sippeers, queueData) {
  console.log('📡 Emitindo dados atualizados para o frontend:', {
    sippeers,
    queueData
  });
  io.emit('fetch-unified-data-response', { sippeers, queueData });
  setTimeout(fetchAndEmitUnifiedData, 1000);
}

fetchAndEmitUnifiedData();

process.on('SIGINT', () => {
  console.log('Desconectando...');
  ami.disconnect();
  process.exit();
});

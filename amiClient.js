import express from 'express';
import AsteriskManager from 'asterisk-manager';

// Conectar ao Asterisk Manager Interface (AMI)
const ami = new AsteriskManager(5038, 'localhost', 'admin', 'MKsx2377!@', true);

// Criar o servidor Express
const app = express();
app.use(express.json()); // Permite que o servidor aceite JSON no corpo das requisições

// Endpoint para pausar/despausar o agente
app.post('/pause-agent', (req, res) => {
    const { agent, paused, reason } = req.body;

    // Valida os parâmetros obrigatórios
    if (!agent || typeof paused === 'undefined') {
        return res.status(400).json({
            success: false,
            error: "Os parâmetros 'agent' e 'paused' são obrigatórios."
        });
    }

    // Status de pausa: 'true' ou 'false'
    const pauseStatus = paused ? 'true' : 'false';

    // Enviar comando AMI para pausar/despausar
    ami.action({
        action: 'QueuePause',
        interface: agent,       // Identificador do agente (exemplo: 'SIP/1001' ou 'Agent/1001')
        paused: pauseStatus,    // 'true' ou 'false'
        reason: reason || ''    // Motivo opcional
    }, (err, response) => {
        if (err) {
            console.error('Erro ao enviar comando AMI:', err);
            return res.status(500).json({
                success: false,
                error: 'Erro ao enviar comando AMI',
                details: err.message
            });
        }

        // Resposta do AMI
        console.log('Resposta AMI:', response);
        return res.json({
            success: true,
            message: 'Comando executado com sucesso',
            response
        });
    });
});

// Exemplo de outro endpoint para testar a conexão com o Asterisk
app.post('/ping', (req, res) => {
    ami.action({
        action: 'Ping'
    }, (err, response) => {
        if (err) {
            console.error('Falha no ping AMI:', err);
            return res.status(500).json({
                success: false,
                error: 'Falha no ping AMI',
                details: err.message
            });
        }
        res.json({
            success: true,
            message: 'Ping bem-sucedido',
            response
        });
    });
});

// Captura eventos AMI e exibe no console
ami.on('managerevent', (event) => {
    console.log('Evento AMI recebido:', event);

    // Ações específicas com base nos eventos
    switch (event.event) {
        case 'Newchannel':
            console.log(`Nova chamada detectada: Canal: ${event.channel}, Ramal: ${event.extension}`);
            break;
        case 'Hangup':
            console.log(`Chamada finalizada: Canal: ${event.channel}, Status: ${event.status}`);
            break;
        case 'Peerstatus':
            console.log(`Status do ramal: Ramal: ${event.peer}, Status: ${event.status}`);
            break;
        default:
            break;
    }
});

// Captura erros na conexão AMI
ami.on('error', (err) => {
    console.error('Erro na conexão AMI:', err);
});

// Inicia o servidor na porta 3000
const PORT = 3000;
app.listen(PORT, () => {
    console.log(`Servidor Node.js rodando na porta ${PORT}`);
});

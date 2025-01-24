<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMI Channels</title>
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.5.1/dist/socket.io.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>AMI: Core Show Channels</h2>
        
        <!-- Exibição de Canais Ativos -->
        <h4>Canais Ativos</h4>
        <table id="channels-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Canal</th>
                    <th>Contexto</th>
                    <th>Extensão</th>
                    <th>Prioridade</th>
                    <th>Estado</th>
                    <th>Aplicação</th>
                    <th>Dados</th>
                    <th>Caller ID</th>
                    <th>Duração</th>
                    <th>Código de Conta</th>
                </tr>
            </thead>
            <tbody>
                <!-- Os dados dos canais serão inseridos aqui -->
            </tbody>
        </table>
        
        <!-- Exibição dos Ramais -->
        <h4>Ramais</h4>
        <table id="sippers-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>IP</th>
                    <th>Modo</th>
                    <th>ID do Usuário</th>
                    <th>Nome do Usuário</th>
                    <th>Pausa</th>
                    <th>Início da Pausa</th>
                    <th>Tempo em Pausa</th>
                </tr>
            </thead>
            <tbody>
                <!-- Os dados dos ramais serão inseridos aqui -->
            </tbody>
        </table>
    </div>

    <script>
        // Conectar ao servidor Socket.IO
         const socket = io("http://93.127.212.237:4000");  // Altere para a URL correta do seu servidor Socket.IO

        // Escutar os eventos emitidos pelo servidor para canais ativos
        socket.on('active-channels', (channels) => {
            console.log('Canais Ativos:', channels);
            updateActiveChannels(channels);
        });

        // Escutar os eventos emitidos pelo servidor para ramais
        socket.on('fetch-sippers-response', (data) => {
            console.log('Dados dos Ramais:', data);
            updateSippersData(data);
        });

        // Função para atualizar os dados dos canais ativos
        function updateActiveChannels(channels) {
            const tableBody = document.querySelector('#channels-table tbody');
            tableBody.innerHTML = ''; // Limpa a tabela antes de adicionar os novos dados

            channels.forEach(channel => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${channel.channel}</td>
                    <td>${channel.context}</td>
                    <td>${channel.extension}</td>
                    <td>${channel.priority}</td>
                    <td>${channel.state}</td>
                    <td>${channel.application}</td>
                    <td>${channel.data || ''}</td>
                    <td>${channel.callerID}</td>
                    <td>${channel.duration}</td>
                    <td>${channel.accountCode || ''}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Função para atualizar os dados dos ramais
        function updateSippersData(data) {
            const tableBody = document.querySelector('#sippers-table tbody');
            tableBody.innerHTML = ''; // Limpa a tabela antes de adicionar os novos dados

            data.forEach(sipper => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${sipper.name}</td>
                    <td>${sipper.ipaddr}</td>
                    <td>${sipper.modo}</td>
                    <td>${sipper.user_id}</td>
                    <td>${sipper.user_name}</td>
                    <td>${sipper.pause_name || ''}</td>
                    <td>${sipper.started_at || ''}</td>
                    <td>${sipper.time_in_pause || ''}</td>
                `;
                tableBody.appendChild(row);
            });
        }

        // Escutar evento de erro
        socket.on('fetch-data-error', (error) => {
            console.error('Erro ao buscar dados:', error);
            alert(`Erro: ${error.message}`);
        });
    </script>
</body>
</html>

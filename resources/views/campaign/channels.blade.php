@extends('day.layout')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMI: Canais Ativos e Ramais</title>
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.5.1/dist/socket.io.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>AMI: Canais Ativos e Ramais</h2>

        <!-- Tabela de Canais Ativos -->
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
                <!-- Os dados serão preenchidos via Socket.IO -->
            </tbody>
        </table>

        
    </div>

    <script>
        // Conectar ao servidor Socket.IO na porta 4000
        const socket = io("http://93.127.212.237:4000"); // Ajuste a URL para o seu servidor Socket.IO

        // Escutar os eventos de canais ativos e atualizar a tabela
        socket.on('active-channels', (channels) => {
            console.log('Canais Ativos:', channels);  // Para debug, para ver os dados no console
            updateChannelsTable(channels);
        });

        // Função para atualizar a tabela de canais ativos
        function updateChannelsTable(channels) {
            const tableBody = document.querySelector('#channels-table tbody');
            tableBody.innerHTML = '';  // Limpar tabela antes de preencher

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

        // Escutar os eventos dos ramais e atualizar a tabela
        socket.on('fetch-sippers-response', (sippers) => {
            console.log('Ramais:', sippers);  // Para debug
            updateSippersTable(sippers);
        });

        // Função para atualizar a tabela de ramais
        function updateSippersTable(sippers) {
            const tableBody = document.querySelector('#sippers-table tbody');
            tableBody.innerHTML = '';  // Limpar tabela antes de preencher

            sippers.forEach(sipper => {
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
    </script>
</body>
@endsection

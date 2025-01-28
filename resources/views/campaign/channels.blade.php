@extends('day.layout')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMI: Canais Ativos e Ramais</title>
    <script src="https://cdn.jsdelivr.net/npm/socket.io-client@4.5.1/dist/socket.io.min.js"></script>
    <style>
        body {
            background-color: #F9FAFB; /* Fundo cinza claro suave */
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #FFFFFF; /* Fundo branco */
            margin-top: 20px;
        }
        .table {
            background-color: #FFFFFF;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        .table thead {
            background-color: #4A90E2; /* Azul vibrante */
            color: #FFFFFF;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.05em;
        }
        .table tbody tr {
            border-bottom: 1px solid #EDEDED; /* Separação sutil entre linhas */
        }
        .table tbody tr:last-child {
            border-bottom: none; /* Remover borda inferior na última linha */
        }
        .table tbody tr:nth-child(odd) {
            background-color: #F8FAFD; /* Fundo alternado suave */
        }
        .table tbody tr:hover {
            background-color: #E3F2FD; /* Azul claro para hover */
        }
        .table td, .table th {
            padding: 12px;
            text-align: center;
            font-size: 0.95rem;
        }
        .table tbody td.highlight {
            font-weight: bold;
            color: #333333; /* Cor escura para ênfase */
        }
        .header-title {
            font-weight: bold;
            color: #333333;
            font-size: 1.5rem;
        }
        .text-muted {
            font-size: 0.9rem;
            color: #6C757D;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="text-center mb-4">
            <h2 class="header-title">Monitoramento de Canais</h2>
        </div>

        <!-- Card da tabela -->
        <div class="card p-4">
            <div class="card-body">
                <h4 class="header-title">Canais Ativos</h4>
                <p class="text-muted">Os dados são atualizados automaticamente em tempo real.</p>

                <!-- Tabela Responsiva -->
                <div class="table-responsive">
                    <table id="channels-table" class="table align-middle">
                        <thead>
                            <tr>
                                <th class="highlight">Canal</th>
                                <th class="highlight">Contexto</th>
                                <th class="highlight">Extensão</th>
                                <th class="highlight">Prioridade</th>
                                <th class="highlight">Estado</th>
                                <th class="highlight">Aplicação</th>
                                <th class="highlight">Dados</th>
                                <th class="highlight">Caller ID</th>
                                <th class="highlight">Duração</th>
                                <th class="highlight">Código de Conta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="loading-row">
                                <td colspan="10" class="text-center text-muted">Carregando dados...</td>
                            </tr>
                            <!-- Os dados serão preenchidos dinamicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const socket = io("http://93.127.212.237:4000");

        // Escutar eventos de canais ativos
        socket.on('canais-ativos', (channels) => {
            console.log('Canais Ativos:', channels);
            updateChannelsTable(channels);
        });

        function updateChannelsTable(channels) {
            const tableBody = document.querySelector('#channels-table tbody');
            tableBody.innerHTML = ''; // Limpar tabela antes de atualizar

            if (channels.length === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.innerHTML = `
                    <td colspan="10" class="text-center text-muted">Nenhum canal ativo no momento.</td>
                `;
                tableBody.appendChild(emptyRow);
                return;
            }

            // Preenchendo a tabela
            channels.forEach(channel => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${channel.channel || ''}</td>
                    <td>${channel.context || ''}</td>
                    <td class="highlight">${channel.extension || ''}</td>
                    <td>${channel.priority || ''}</td>
                    <td>${channel.state || ''}</td>
                    <td>${channel.application || ''}</td>
                    <td>${channel.data || ''}</td>
                    <td>${channel.callerID || ''}</td>
                    <td class="highlight">${channel.duration || ''}</td>
                    <td>${channel.accountCode || ''}</td>
                `;
                tableBody.appendChild(row);
            });
        }
    </script>
</body>
@endsection

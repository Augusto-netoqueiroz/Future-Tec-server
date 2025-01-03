@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #f8f9fa;
    }
    .card-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
    }
    .ramal-card {
        width: 250px;
        padding: 15px;
        border-radius: 8px;
        color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    .status-online {
        background-color: #28a745; /* Verde */
        color: #fff;
    }
    .status-offline {
        background-color: #dc3545; /* Vermelho */
        color: #fff;
    }
    .status-ringing {
        background-color: #ffc107; /* Amarelo */
        color: #212529;
    }
    .status-in-call {
        background-color: #6f42c1; /* Roxo */
    }
    .status-available {
        background-color: #17a2b8; /* Azul */
    }
</style>

<div class="container mt-5">
    <h1 class="text-center">Dashboard de Ramais</h1>
    <div id="ramais-container" class="card-container mt-4">
        <!-- Os cartões dos ramais serão inseridos aqui dinamicamente -->
    </div>
</div>

<script>
    const socket = io('http://93.127.212.237:3000'); // URL do servidor Socket.IO

    const ramais = {}; // Objeto para rastrear o status dos ramais

    // Função para consultar o estado dos ramais via AJAX
    function consultarEstado() {
        $.ajax({
            url: '/consulta-estado', // Endpoint de consulta
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                response.forEach(({ ramal, estado, atendente }) => {
                    if (!ramais[ramal]) {
                        ramais[ramal] = {
                            estado: estado === 1 ? "Online" : "Offline",
                            status: "Disponível",
                            destino: null,
                            atendente: atendente || null, // Armazenando o nome do atendente
                            ultimaAtualizacao: new Date().toLocaleString()
                        };
                    } else {
                        ramais[ramal].estado = estado === 1 ? "Online" : "Offline";
                        ramais[ramal].atendente = atendente || ramais[ramal].atendente; // Atualiza o atendente caso tenha valor
                    }
                });
                atualizarCartoes();
            },
            error: function (error) {
                console.error("Erro ao consultar estados:", error);
            }
        });
    }

    // Função para atualizar os cartões dos ramais
    function atualizarCartoes() {
        const container = $("#ramais-container");
        container.empty();

        Object.keys(ramais).forEach(ramal => {
            const { estado, status, destino, atendente, ultimaAtualizacao } = ramais[ramal];
            
            // Define a classe de cor com base no estado do ramal
            let classeEstado = estado === "Online" ? "status-online" : "status-offline";

            let descricaoChamada = "";
            if (status === "Ligando") {
                descricaoChamada = `Ligando para: ${destino}`;
            } else if (status === "Tocando" || status === "Em Ligação") {
                descricaoChamada = `Em ligação com: ${destino}`;
            }

            let atendenteInfo = atendente ? `<p>Atendente: ${atendente}</p>` : "";

            container.append(`
                <div class="ramal-card ${classeEstado}">
                    <h5>Ramal: ${ramal}</h5>
                    <p>Estado: ${estado}</p>
                    <p>Status: ${status}</p>
                    <p>${descricaoChamada}</p>
                    ${atendenteInfo}
                    <p>Última Atualização: ${ultimaAtualizacao}</p>
                </div>
            `);
        });
    }

    // Processar eventos WebSocket para atualizar o status em tempo real
    socket.on('new_event', (evento) => {
        const { event, channel, exten, duration } = evento;

        const ramal = channel?.split("/")[1]?.split("-")[0];
        const destino = exten;

        if (!ramal) return;

        switch (event) {
            case "Newchannel":
                if (!ramais[ramal]) {
                    ramais[ramal] = { estado: "Online", status: "Ligando", destino: destino, ultimaAtualizacao: "" };
                }
                ramais[ramal].status = "Ligando";
                ramais[ramal].destino = destino;
                ramais[ramal].ultimaAtualizacao = new Date().toLocaleString();
                break;

            case "Newstate":
                if (destino && !ramais[destino]) {
                    ramais[destino] = { estado: "Online", status: "Tocando", destino: ramal, ultimaAtualizacao: "" };
                }
                if (destino) {
                    ramais[destino].status = "Tocando";
                    ramais[destino].destino = ramal;
                    ramais[destino].ultimaAtualizacao = new Date().toLocaleString();
                }
                break;

            case "VarSet":
                if (ramais[ramal]) {
                    ramais[ramal].status = duration ? `Em Ligação (${duration}s)` : "Em Ligação";
                    ramais[ramal].destino = destino;
                    ramais[ramal].ultimaAtualizacao = new Date().toLocaleString();
                }
                break;

            case "Hangup":
                if (ramais[ramal]) {
                    ramais[ramal].status = "Disponível";
                    ramais[ramal].destino = null;
                    ramais[ramal].ultimaAtualizacao = new Date().toLocaleString();
                }
                if (destino && ramais[destino]) {
                    ramais[destino].status = "Disponível";
                    ramais[destino].destino = null;
                    ramais[destino].ultimaAtualizacao = new Date().toLocaleString();
                }
                break;

            default:
                return;
        }

        atualizarCartoes();
    });

    // Executa imediatamente ao carregar a página
    consultarEstado();

    // Inicializa a consulta periódica de estado
    setInterval(consultarEstado, 5000);
</script>
@endsection

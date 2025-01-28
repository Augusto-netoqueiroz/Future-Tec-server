@extends('day.layout')

@section('title', 'Monitor')

@section('content')
<div class="container mt-5">
    <div class="row">
        <!-- Seção de Ramais -->
        <div class="col-md-8">
            <h1 class="text-primary">Monitor de Ramais e Ligações</h1>
            <!-- Container para os cards -->
            <div class="row" id="sippers-cards">
                <!-- Os cards serão adicionados aqui via JavaScript -->
            </div>
        </div>

        <!-- Seção de Filas -->
        <div class="col-md-3 d-flex justify-content-end">
            <div>
                <div class="d-flex justify-content-end mb-2">
                    <div class="form-check form-switch" style="transform: scale(1.3);">
                        <input class="form-check-input" type="checkbox" id="queue-toggle">
                        <label class="form-check-label text-secondary fw-bold ms-2" for="queue-toggle">Mostrar</label>
                    </div>
                </div>
                <div id="queue-section" class="queue-section p-3 shadow-sm rounded" 
                     style="background: linear-gradient(to right, #f8f9fa, #eaecef); border: 1px solid #ddd; font-size: 0.9rem; display: none;">
                    <h3 class="text-secondary">Ligações em Fila</h3>
                    <div id="queue-table" class="queue-table bg-white rounded p-2" style="max-height: 280px; overflow-y: auto;">
                        <!-- Os dados das filas serão adicionados aqui via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script>
    const socket = io("http://93.127.212.237:4000");

    const cardTimers = {};
    let activeChannels = {};

    // Atualizar dados dos ramais (fetch-sippers-response)
    socket.on('fetch-unified-data-response', (data) => {
        console.log("Recebido evento fetch-sippers-response:", data);

        const cardsContainer = document.querySelector("#sippers-cards");
        cardsContainer.innerHTML = ""; // Limpa os cards existentes

        // Renderizar cards para cada ramal
        data.forEach((sipper) => {
            const card = document.createElement("div");
            card.classList.add("col-md-6", "mb-4");
            card.id = `card-${sipper.name}`; // ID do card baseado no nome do ramal

            card.innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${sipper.name}</h5>
                        <span class="badge">${sipper.user_name || "Desconhecido"}</span>
                        <p class="card-text mt-3">
                            <strong>Status:</strong> <span class="status">Disponível</span><br>
                            <strong>Ligação:</strong> <span class="call-info"></span><br>
                            <strong>Tempo de Pausa:</strong> <span class="time">${sipper.time_in_pause || "00:00:00"}</span>
                        </p>
                    </div>
                </div>
            `;
            cardsContainer.appendChild(card);
        });
    });

    // Atualizar status de ligações ativas (raw-canais)
    socket.on("raw-canais", (data) => {
        console.log("Recebido evento raw-canais:", data);

        const channels = data.slice(1, -2).map((line) => line.trim());

        channels.forEach((rawChannel) => {
            const [channel, , , , state] = rawChannel.trim().split(/\s+/);
            const extension = extractExtension(channel);

            if (!extension) return;

            activeChannels[extension] = state;

            const card = document.querySelector(`#card-${extension}`);
            if (card) {
                const statusElement = card.querySelector(".status");
                const callInfoElement = card.querySelector(".call-info");

                // Atualiza o status do card
                if (state === "Up") {
                    statusElement.textContent = "Em Chamada";
                    callInfoElement.textContent = "Ligação ativa";
                    card.classList.add("call");
                    card.classList.remove("ringing", "ring");
                } else if (state === "Ringing") {
                    statusElement.textContent = "Tocando";
                    callInfoElement.textContent = "Ligação tocando";
                    card.classList.add("ringing");
                    card.classList.remove("call", "ring");
                } else if (state === "Ring") {
                    statusElement.textContent = "Chamada em Andamento";
                    callInfoElement.textContent = "Conectando...";
                    card.classList.add("ring");
                    card.classList.remove("call", "ringing");
                }

                // Timer para restaurar estado padrão
                clearTimeout(cardTimers[extension]);
                cardTimers[extension] = setTimeout(() => {
                    restoreDefaultState([extension]);
                }, 5000);
            }
        });

        // Restaura estados padrão de ramais sem eventos
        const activeExtensions = Object.keys(activeChannels);
        restoreDefaultState(activeExtensions);
    });

    // Função para restaurar estados dos cards
    function restoreDefaultState(activeExtensions) {
        const allCards = document.querySelectorAll("[id^='card-']");

        allCards.forEach((card) => {
            const extension = card.id.split('-')[1];

            if (!activeExtensions.includes(extension)) {
                if (!cardTimers[extension]) {
                    const statusElement = card.querySelector('.status');
                    const callInfoElement = card.querySelector('.call-info');

                    // Restaurar estado padrão
                    statusElement.textContent = "Disponível";
                    callInfoElement.textContent = "";
                    card.classList.remove("call", "ringing", "ring");
                }
            }
        });
    }

    // Função para extrair a extensão do canal
    function extractExtension(channel) {
        const match = channel.match(/^SIP\/(\d+)-/);
        return match ? match[1] : null;
    }
</script>

<style>
    /* Estilo dos cards */
    .card {
        background-color: #17a2b8;
        color: #ffffff;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .card.ringing {
        background-color: #ffc107; /* Amarelo para Ringing */
        animation: shake 0.3s infinite;
    }

    .card.ring {
        background-color: #007bff; /* Azul para Ring */
    }

    .card.call {
        background-color: #0056b3; /* Azul escuro para Call */
    }

    @keyframes shake {
        0%, 100% {
            transform: translateX(0);
        }
        25% {
            transform: translateX(-5px);
        }
        50% {
            transform: translateX(5px);
        }
        75% {
            transform: translateX(-5px);
        }
    }
</style>
@endsection

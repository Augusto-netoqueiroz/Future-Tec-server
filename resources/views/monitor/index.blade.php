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

// Atualizar dados unificados
socket.on('fetch-unified-data-response', (data) => {
    console.log("Recebido evento fetch-unified-data-response:", data);

    atualizarRamais(data.sippeers);
    atualizarFilas(data.queueData);
});

// Atualizar ramais
function atualizarRamais(sippeers) {
    const cardsContainer = document.querySelector("#sippers-cards");
    cardsContainer.innerHTML = ""; // Limpa os cards existentes

    sippeers.forEach((sipper) => {
        const card = document.createElement("div");
        card.classList.add("col-md-6", "mb-4");
        card.id = `card-${sipper.name}`; // ID do card baseado no nome do ramal

        card.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${sipper.name}</h5>
                    <span class="badge">${sipper.user_name || "Desconhecido"}</span>
                    <p class="card-text mt-3">
                        <strong>Status:</strong> <span class="status">${sipper.call_state}</span><br>
                        <strong>Ligação:</strong> <span class="call-info">${sipper.call_duration || ""}</span><br>
                        <strong>Tempo de Pausa:</strong> <span class="time">${sipper.time_in_pause || "00:00:00"}</span>
                    </p>
                </div>
            </div>
        `;
        cardsContainer.appendChild(card);

        // Atualiza a cor do card conforme o status da chamada
        atualizarEstadoDoCard(sipper.name, sipper.call_state, sipper.pause_name);
    });
}

// Atualizar estado dos cards com base na chamada
// Atualizar estado dos cards com base na chamada e pausa
function atualizarEstadoDoCard(extension, callState, pauseName) {
    const cardContainer = document.querySelector(`#card-${extension}`);
    if (!cardContainer) return;

    const card = cardContainer.querySelector(".card");
    const statusElement = card.querySelector(".status");
    const callInfoElement = card.querySelector(".call-info");

    // Remover todas as classes antes de adicionar a nova
    card.classList.remove("call", "ringing", "ring", "shake", "paused");

    if (pauseName && pauseName !== "Disponível") {
        // Se o ramal estiver em pausa, exibe a pausa e muda a cor do card
        statusElement.textContent = `Pausado (${pauseName})`;
        callInfoElement.textContent = "Em pausa";
        card.classList.add("paused"); // Adiciona a classe CSS para pausa
    } else if (callState === "Em Chamada") {
        statusElement.textContent = "Em Chamada";
        callInfoElement.textContent = "Ligação ativa";
        card.classList.add("call");
    } else if (callState === "Tocando") {
        statusElement.textContent = "Tocando";
        callInfoElement.textContent = "Ligação tocando";
        card.classList.add("ringing", "shake");
    } else if (callState === "Chamada em Andamento") {
        statusElement.textContent = "Conectando...";
        callInfoElement.textContent = "Ligação em progresso";
        card.classList.add("ring");
    } else if (callState === "Discando") {
        statusElement.textContent = "Discando";
        callInfoElement.textContent = "A discagem está em andamento";
        card.classList.add("ring");
    } else {
        statusElement.textContent = "Disponível";
        callInfoElement.textContent = "";
    }
}


// Atualizar dados das filas
function atualizarFilas(queueData) {
    const queueTable = document.querySelector("#queue-table");
    queueTable.innerHTML = ""; // Limpa os dados antigos

    if (queueData.length === 0) {
        queueTable.innerHTML = "<p class='text-muted'>Nenhuma chamada na fila.</p>";
        return;
    }

    queueData.forEach((line) => {
        const p = document.createElement("p");
        p.textContent = line;
        queueTable.appendChild(p);
    });
}

// Toggle de exibição das filas
document.querySelector("#queue-toggle").addEventListener("change", (event) => {
    document.querySelector("#queue-section").style.display = event.target.checked ? "block" : "none";
});





</script>

<style>
 .card {
    background-color: #1ea965; /* Verde mais moderno */
    color: white;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
    font-size: 1rem;
    min-width: 220px;
    max-width: 250px;
    position: relative;
}

.card-body {
    padding: 10px;
}

.card-title {
    font-size: 1.2rem;
    font-weight: bold;
}

.card .status {
    font-weight: bold;
    display: block;
    font-size: 0.9rem;
}

.call-info {
    font-size: 0.85rem;
}

.badge {
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #007bff;
    color: white;
    font-size: 0.8rem;
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
}

/* Ícone do telefone */
.card::before {
    content: '📞';
    font-size: 1.5rem;
    position: absolute;
    top: -10px;
    left: 10px;
}

/* Cores para os estados */
.ringing {
    background-color: #ffc107 !important; /* Amarelo */
}

.call {
    background-color: #dc3545 !important; /* Vermelho */
}

.ring {
    background-color: #007bff !important; /* Azul */
}

.paused {
    background-color: #f39c12 !important; /* Cor laranja para pausas */
    color: white;
}


/* Animação de tremor */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 90% { transform: translateX(-2px); }
    20%, 80% { transform: translateX(2px); }
    30%, 50%, 70% { transform: translateX(-4px); }
    40%, 60% { transform: translateX(4px); }
}

.shake {
    animation: shake 0.4s infinite;
}

</style>
@endsection

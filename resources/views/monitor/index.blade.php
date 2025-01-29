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
        atualizarEstadoDoCard(sipper.name, sipper.call_state);
    });
}

// Atualizar estado dos cards com base na chamada
function atualizarEstadoDoCard(extension, callState) {
    const card = document.querySelector(`#card-${extension}`);
    if (!card) return;

    const statusElement = card.querySelector(".status");
    const callInfoElement = card.querySelector(".call-info");

    if (callState === "Em Chamada") {
        statusElement.textContent = "Em Chamada";
        callInfoElement.textContent = "Ligação ativa";
        card.classList.add("call");
        card.classList.remove("ringing", "ring");
    } else if (callState === "Tocando") {
        statusElement.textContent = "Tocando";
        callInfoElement.textContent = "Ligação tocando";
        card.classList.add("ringing");
        card.classList.remove("call", "ring");
    } else if (callState === "Chamada em Andamento") {
        statusElement.textContent = "Conectando...";
        callInfoElement.textContent = "Ligação em progresso";
        card.classList.add("ring");
        card.classList.remove("call", "ringing");
    } else {
        statusElement.textContent = "Disponível";
        callInfoElement.textContent = "";
        card.classList.remove("call", "ringing", "ring");
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

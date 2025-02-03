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

    socket.on('fetch-unified-data-response', (data) => {
        console.log("Recebido evento fetch-unified-data-response:", data);

        atualizarRamais(data.sippeers);
        atualizarFilas(data.queueData);
    });

  function atualizarRamais(sippeers) {
    const cardsContainer = document.querySelector("#sippers-cards");
    cardsContainer.innerHTML = "";

    // Criar um mapa de chamadas ativas baseado no uniqueID
    const chamadasAtivas = {};

    sippeers.forEach((sipper) => {
        if (sipper.uniqueID) {
            if (!chamadasAtivas[sipper.uniqueID]) {
                chamadasAtivas[sipper.uniqueID] = [];
            }
            chamadasAtivas[sipper.uniqueID].push(sipper);
        }
    });

    sippeers.forEach((sipper) => {
        const card = document.createElement("div");
        card.classList.add("col-md-6", "mb-4");
        card.id = `card-${sipper.name}`;

        let callingFrom = sipper.calling_from || sipper.name; // Origem sempre será o próprio ramal
        let callingTo = sipper.calling_to || "Desconhecido"; // Destino

        // Se callingTo estiver vazio ou for "Desconhecido", ajusta a ligação para garantir que callingFrom venha antes
        const ligacao = callingTo !== "Desconhecido" ? `${callingFrom} => ${callingTo}` : `${callingFrom} => ${callingFrom}`;

        card.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${sipper.name}</h5>
                    <span class="badge">${sipper.user_name || "Desconhecido"}</span>
                    <p class="card-text mt-3">
                        <strong>Status:</strong> <span class="status">${sipper.call_state}</span><br>
                        <strong>Ligação:</strong> <span class="call-info">${ligacao}</span><br>
                        <strong>Tempo de Pausa:</strong> <span class="time">${sipper.time_in_pause || "00:00:00"}</span><br>
                        <strong>Duração:</strong> <span class="call-info">${sipper.call_duration || ""}</span><br>
                    </p>
                </div>
            </div>
        `;

        cardsContainer.appendChild(card);
        atualizarEstadoDoCard(sipper.name, sipper.call_state, callingFrom, callingTo);
    });
}


   function atualizarEstadoDoCard(extension, callState, calling_from, calling_to) {
    const cardContainer = document.querySelector(`#card-${extension}`);
    if (!cardContainer) return;

    const card = cardContainer.querySelector(".card");
    const statusElement = card.querySelector(".status");
    const callInfoElement = card.querySelector(".call-info");
    let iconElement = card.querySelector(".icon");

    // Se o elemento de ícone não existir, cria um novo dentro do card
    if (!iconElement) {
        iconElement = document.createElement("span");
        iconElement.classList.add("icon");
        card.prepend(iconElement); // Adiciona no topo do card
    }

    card.classList.remove("call", "ringing", "ring", "shake");

    if (callState === "Em Chamada") {
        statusElement.textContent = "Em Chamada";
        callInfoElement.textContent = `${calling_from} => ${calling_to}`;
        card.classList.add("call");
        iconElement.textContent = "🔴";
    } else if (callState === "Tocando") {
        statusElement.textContent = "Tocando";
        callInfoElement.textContent = `${calling_to} => ${calling_from}`;
        card.classList.add("ringing", "shake");
        iconElement.textContent = "📳";
    } else if (callState === "Chamada em Andamento") {
        statusElement.textContent = "Conectando...";
        callInfoElement.textContent = `${calling_from} => ${calling_to}`;
        card.classList.add("ring");
        iconElement.textContent = "🔄";
    } else if (callState === "Discando") {
        statusElement.textContent = "Discando";
        callInfoElement.textContent = `${calling_from} => ${calling_to}`;
        card.classList.add("ring");
        iconElement.textContent = "📞";
    } else {
        statusElement.textContent = "Disponível";
        callInfoElement.textContent = "";
        iconElement.textContent = "✅";
    }
}

    function atualizarFilas(queueData) {
        const queueTable = document.querySelector("#queue-table");
        queueTable.innerHTML = "";

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

    document.querySelector("#queue-toggle").addEventListener("change", (event) => {
        document.querySelector("#queue-section").style.display = event.target.checked ? "block" : "none";
    });

</script>

<style>
.card {
    background-color: #1ea965;
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
    left: 80%;
    transform: translateX(-50%);
    background: #007bff;
    color: white;
    font-size: 0.8rem;
    padding: 5px 10px;
    border-radius: 15px;
    font-weight: bold;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
}

.card::before {
    content: '';
    font-size: 1.5rem;
    position: absolute;
    top: -10px;
    left: 10px;
}

.ringing {
    background-color: #ffc107 !important;
}

.call {
    background-color: #dc3545 !important;
}

.ring {
    background-color: #007bff !important;
}

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

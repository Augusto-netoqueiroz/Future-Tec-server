@extends('day.layout')

@section('title', 'Monitor')

@section('content')
<div class="container mt-5">
    <div class="row align-items-center">
        <!-- Cards Fixos -->
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-4">
                    <button class="fixed-card btn btn-primary w-100" data-toggle="modal" data-target="#modalRecebidas">
                        <div class="card-body text-center">
                            <h5 class="card-title">Ligações Recebidas</h5>
                            <h3>{{ $dadosChamadas->total_recebidas ?? 0 }}</h3>
                        </div>
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="fixed-card btn btn-success w-100" data-toggle="modal" data-target="#modalAtendidas">
                        <div class="card-body text-center">
                            <h5 class="card-title">Ligações Atendidas</h5>
                            <h3>{{ $dadosChamadas->total_atendidas ?? 0 }}</h3>
                        </div>
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="fixed-card btn btn-danger w-100" data-toggle="modal" data-target="#modalPerdidas">
                        <div class="card-body text-center">
                            <h5 class="card-title">Ligações Perdidas</h5>
                            <h3>{{ $dadosChamadas->total_perdidas ?? 0 }}</h3>
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Toggle da Fila (alinhado à direita) -->
        <div class="col-md-3 d-flex justify-content-end">
            <div>
                <div class="d-flex justify-content-end mb-2">
                    <div class="form-check form-switch" style="transform: scale(1.3);">
                        <input class="form-check-input" type="checkbox" id="queue-toggle">
                        <label class="form-check-label text-secondary fw-bold ms-2" for="queue-toggle">Mostrar Fila</label>
                    </div>
                </div>
                <div id="queue-section" class="queue-section p-3 shadow-sm rounded d-none" 
                     style="background: linear-gradient(to right, #f8f9fa, #eaecef); border: 1px solid #ddd; font-size: 0.9rem; transition: max-height 0.5s ease-in-out;">
                    <h3 class="text-secondary">Ligações em Fila</h3>
                    <div id="queue-table" class="queue-table bg-white rounded p-2" style="max-height: 280px; overflow-y: auto;">
                        <!-- Dados das filas serão carregados via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Separador -->
    <hr class="my-4">

    <!-- Container para os Cards Dinâmicos -->
    <div class="row">
        <div class="col-12">
            <div id="dynamic-cards-container" class="p-3 bg-white shadow-sm rounded" style="min-height: 150px; transition: min-height 0.5s ease-in-out;">
                <h4 class="text-secondary">Ramais Ativos</h4>
                <div class="row" id="sippers-cards">
                    <!-- Os cards dinâmicos serão adicionados aqui via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modais -->
@foreach(['Recebidas', 'Atendidas', 'Perdidas'] as $tipo)
<div class="modal fade" id="modal{{ $tipo }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ligações {{ $tipo }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Data/Hora</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Fila</th>
                            <th>Duração</th>
                        </tr>
                    </thead>
                    <tbody id="extrato{{ $tipo }}">
                        <tr><td colspan="4">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endforeach

<script>
    document.getElementById('queue-toggle').addEventListener('change', function() {
        let queueSection = document.getElementById('queue-section');
        let dynamicCardsContainer = document.getElementById('dynamic-cards-container');

        if (this.checked) {
            queueSection.classList.remove('d-none');
            dynamicCardsContainer.style.minHeight = "100px"; // Reduz altura
        } else {
            queueSection.classList.add('d-none');
            dynamicCardsContainer.style.minHeight = "150px"; // Retorna ao padrão
        }
    });
</script>



<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const empresaId = {{ Auth::user()->empresa_id }};
</script>

<script>
   const socket = io("http://93.127.212.237:4000");

socket.on('fetch-unified-data-response', (data) => {
    console.log("Recebido evento fetch-unified-data-response:", data);

    // Filtra os ramais para exibir apenas os da empresa do usuário
    const sippeersFiltrados = data.sippeers.filter(sipper => sipper.empresa_id == empresaId);

    atualizarRamais(sippeersFiltrados);
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

        let callingFrom = sipper.calling_from || sipper.name;
        let callingTo = sipper.calling_to || "";

        // Se calling_to for null, tentamos ajustar com o outro ramal ativo
        if (!callingTo && chamadasAtivas[sipper.uniqueID]?.length > 1) {
            const outraParte = chamadasAtivas[sipper.uniqueID].find(c => c.name !== sipper.name);
            if (outraParte) {
                callingTo = outraParte.name;
            }
        }

        let badgeHTML = sipper.user_name && sipper.user_name !== "Desconhecido" 
          ? `<span class="badge">${sipper.user_name}</span>` 
          : "";

        card.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${sipper.name}</h5>
                    ${badgeHTML}
                    <p class="card-text mt-3">
                        <strong></strong> <span class="status">${sipper.call_state}</span><br>
                        <strong></strong> <span class="call-info">${callingFrom} => ${callingTo || "Desconhecido"}</span><br>
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

    // Filtra as filas que pertencem à empresa do usuário
    const filasFiltradas = queueData.filter(queue => queue.empresa_id == empresaId);

    if (filasFiltradas.length === 0) {
        queueTable.innerHTML = "<p class='text-muted'>Nenhuma chamada na fila para sua empresa.</p>";
        return;
    }

    filasFiltradas.forEach((queue) => {
        // Cria o nome da fila
        const queueNameElement = document.createElement("h3");
        queueNameElement.textContent = queue.queueName;
        queueTable.appendChild(queueNameElement);

        // Verifica se há callers na fila
        if (queue.callers.length > 0) {
            queue.callers.forEach((caller) => {
                // Cria um elemento de caller com as informações necessárias
                const callerInfo = document.createElement("div");
                callerInfo.classList.add("caller-info");
                callerInfo.innerHTML = `
                    <p><strong>Prioridade:</strong> ${caller.priority}</p>
                    <p><strong>Caller:</strong> ${caller.caller}</p>
                    <p><strong>Tempo de Espera:</strong> ${caller.waitTime}</p>
                `;
                queueTable.appendChild(callerInfo);
            });
        } else {
            // Se não houver callers na fila
            const noCallersMessage = document.createElement("p");
            noCallersMessage.classList.add("text-muted");
            noCallersMessage.textContent = "Sem callers na fila.";
            queueTable.appendChild(noCallersMessage);
        }
    });
}


// Exemplo de como ativar/desativar a exibição da seção de filas com o toggle
document.querySelector("#queue-toggle").addEventListener("change", (event) => {
    document.querySelector("#queue-section").style.display = event.target.checked ? "block" : "none";
});




</script>
<script>
      $(document).ready(function() {
    $('.fixed-card').click(function() {
        let modalId = $(this).attr('data-target') || $(this).attr('data-bs-target'); // Pega o ID correto
        let tipo = modalId.replace('#modal', ''); // Remove o prefixo "modal"

        $.ajax({
            url: '/extrato-chamadas/' + tipo.toLowerCase(),
            method: 'GET',
            success: function(data) {
                let tbody = $('#extrato' + tipo);
                tbody.empty();
                if (data.length > 0) {
                    data.forEach(chamada => {
                        tbody.append(`<tr>
                            <td>${chamada.datetime}</td>
                            <td>${chamada.origem}</td>
                            <td>${chamada.destino}</td>
                            <td>${chamada.fila}</td>
                            <td>${chamada.duracao}</td>
                        </tr>`);
                    });
                } else {
                    tbody.append(`<tr><td colspan="4">Nenhuma chamada encontrada.</td></tr>`);
                }

                // Agora abre o modal
                var modal = new bootstrap.Modal(document.getElementById('modal' + tipo));
                modal.show();
            },
            error: function() {
                alert('Erro ao carregar os dados.');
            }
        });
    });
});
 
    </script>



<style>
.card {
    background-color: #1ea965;
    color: white;
    border-radius: 10px;
    padding: 3px; /* Reduzindo ainda mais o espaçamento interno */
    text-align: center;
    box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.15);
    font-size: 0.75rem; /* Diminuindo a fonte */
    min-width: 250px; /* Reduzindo a largura mínima */
    max-width: 250px; /* Diminuindo a largura máxima */
    position: relative;
}

.card-body {
    padding: 5px; /* Reduzindo o espaçamento interno */
}

.card-title {
    font-size: 1rem; /* Diminuindo o título */
}

.card .status {
    font-size: 0.8rem;
}

.call-info {
    font-size: 0.75rem; /* Reduzindo informações da chamada */
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
            background-color: #dc3545;
            color: white;
            animation: blink 1s infinite alternate;
        }

.ring {
    background-color: #007bff !important;
}

@keyframes blink {
            from { opacity: 1; }
            to { opacity: 0.9; }
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
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

        <!-- Toggle da Fila -->
        <div class="col-md-3 d-flex justify-content-end">
            <div>
                <div class="form-check form-switch mb-2" style="transform: scale(1.2);">
                    <input class="form-check-input" type="checkbox" id="queue-toggle">
                    <label class="form-check-label ms-2" for="queue-toggle">Mostrar Fila</label>
                </div>
                <div id="queue-section" class="queue-section p-3 shadow-sm rounded d-none" 
                    style="background: linear-gradient(to right, #f8f9fa, #eaecef); border: 1px solid #ddd; font-size: 0.9rem;">
                    <h5 class="text-secondary">Ligações em Fila</h5>
                    <div id="queue-table" class="bg-white rounded p-2" style="max-height: 280px; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>

    <hr class="my-4">

  <!-- Ramais e Fila lado a lado (Fila à direita) -->
<div class="row mt-4">
    <!-- Ramais Ativos à esquerda -->
    <div class="col-md-8" id="ramais-coluna">
        <div id="dynamic-cards-container" class="p-3 bg-white shadow-sm rounded" style="min-height: 150px;">
            <h4 class="text-secondary">Ramais Ativos</h4>
            <div class="row" id="sippers-cards"></div>
        </div>
    </div>

    <!-- Fila à direita -->
    <div class="col-md-4 d-none" id="queue-column">
        <div id="queue-section" class="queue-section p-3 shadow-sm rounded" 
            style="background: linear-gradient(to right, #f8f9fa, #eaecef); border: 1px solid #ddd; font-size: 0.9rem; max-height: 600px; overflow-y: auto;">
            <h5 class="text-secondary">Ligações em Fila</h5>
            <div id="queue-table" class="bg-white rounded p-2"></div>
        </div>
    </div>
</div>


<!-- Modais -->
@foreach(['Recebidas', 'Atendidas', 'Perdidas'] as $tipo)
<div class="modal fade" id="modal{{ $tipo }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-dark">Ligações {{ $tipo }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <label for="qtd-registros-{{ $tipo }}">Mostrar:</label>
                        <select class="form-select d-inline-block w-auto qtd-registros" data-tipo="{{ $tipo }}">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="30">30</option>
                        </select> registros
                    </div>
                    <div class="spinner-border spinner-border-sm text-primary d-none" role="status" id="spinner{{ $tipo }}">
                        <span class="sr-only">Carregando...</span>
                    </div>
                </div>

                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Data/Hora</th>
                            <th>Origem</th>
                            <th>Destino</th>
                            <th>Fila</th>
                            <th>Duração</th>
                        </tr>
                    </thead>
                    <tbody id="extrato{{ $tipo }}">
                        <tr><td colspan="5">Carregando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- Scripts externos --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
const empresaId = {{ Auth::user()->empresa_id }};
const socket = io("https://fttelecom.cloud:4000");

// Dados socket.io
socket.on('fetch-unified-data-response', (data) => {
    const sippeersFiltrados = data.sippeers.filter(sipper => sipper.empresa_id == empresaId);
    atualizarRamais(sippeersFiltrados);
    atualizarFilas(data.queueData);
});

function atualizarRamais(sippeers) {
    const cardsContainer = document.querySelector("#sippers-cards");
    cardsContainer.innerHTML = "";

    const chamadasAtivas = {};
    sippeers.forEach((sipper) => {
        if (sipper.uniqueID) {
            if (!chamadasAtivas[sipper.uniqueID]) chamadasAtivas[sipper.uniqueID] = [];
            chamadasAtivas[sipper.uniqueID].push(sipper);
        }
    });

    sippeers.forEach((sipper) => {
        const card = document.createElement("div");
        card.classList.add("col-md-6", "mb-4");
        card.id = `card-${sipper.name}`;

        let callingFrom = sipper.calling_from || sipper.name;
        let callingTo = sipper.calling_to || "";

        if (!callingTo && chamadasAtivas[sipper.uniqueID]?.length > 1) {
            const outraParte = chamadasAtivas[sipper.uniqueID].find(c => c.name !== sipper.name);
            if (outraParte) callingTo = outraParte.name;
        }

        const badgeHTML = sipper.user_name && sipper.user_name !== "Desconhecido"
            ? `<span class="badge">${sipper.user_name}</span>` : "";

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
    const card = document.querySelector(`#card-${extension} .card`);
    const statusEl = card.querySelector(".status");
    const infoEl = card.querySelector(".call-info");

    let iconEl = card.querySelector(".icon");
    if (!iconEl) {
        iconEl = document.createElement("span");
        iconEl.classList.add("icon");
        card.prepend(iconEl);
    }

    card.classList.remove("call", "ringing", "ring", "shake");

    switch (callState) {
        case "Em Chamada":
            statusEl.textContent = "Em Chamada";
            infoEl.textContent = `${calling_from} => ${calling_to}`;
            card.classList.add("call");
            iconEl.textContent = "🔴";
            break;
        case "Tocando":
            statusEl.textContent = "Tocando";
            infoEl.textContent = `${calling_to} => ${calling_from}`;
            card.classList.add("ringing", "shake");
            iconEl.textContent = "📳";
            break;
        case "Chamada em Andamento":
            statusEl.textContent = "Conectando...";
            infoEl.textContent = `${calling_from} => ${calling_to}`;
            card.classList.add("ring");
            iconEl.textContent = "🔄";
            break;
        case "Discando":
            statusEl.textContent = "Discando";
            infoEl.textContent = `${calling_from} => ${calling_to}`;
            card.classList.add("ring");
            iconEl.textContent = "📞";
            break;
        default:
            statusEl.textContent = "Disponível";
            infoEl.textContent = "";
            iconEl.textContent = "✅";
    }
}

function atualizarFilas(queueData) {
    const queueTable = document.querySelector("#queue-table");
    queueTable.innerHTML = "";

    const filas = queueData.filter(queue => queue.empresa_id == empresaId);
    if (!filas.length) {
        queueTable.innerHTML = "<p class='text-muted'>Nenhuma chamada na fila para sua empresa.</p>";
        return;
    }

    filas.forEach((queue) => {
        const queueName = document.createElement("h5");
        queueName.textContent = queue.queueName;
        queueTable.appendChild(queueName);

        if (queue.callers.length) {
            queue.callers.forEach((caller) => {
                const div = document.createElement("div");
                div.classList.add("caller-info");
                div.innerHTML = `
                    <p><strong>Prioridade:</strong> ${caller.priority}</p>
                    <p><strong>Caller:</strong> ${caller.caller}</p>
                    <p><strong>Tempo de Espera:</strong> ${caller.waitTime}</p>
                `;
                queueTable.appendChild(div);
            });
        } else {
            queueTable.innerHTML += "<p class='text-muted'>Sem callers na fila.</p>";
        }
    });
}

// Toggle fila
document.getElementById('queue-toggle').addEventListener('change', function () {
    const filaColuna = document.getElementById('queue-column');
    filaColuna.classList.toggle('d-none', !this.checked);
});

</script>

{{-- Extrato de chamadas com spinner e select --}}
<script>
$(document).ready(function () {
    function carregarChamadas(tipo, qtd = 10) {
        const tbody = $('#extrato' + tipo);
        const spinner = $('#spinner' + tipo);
        tbody.html('');
        spinner.removeClass('d-none');

        $.ajax({
            url: `/extrato-chamadas/${tipo.toLowerCase()}?limit=${qtd}`,
            method: 'GET',
            success: function (data) {
                if (data.length > 0) {
                    data.forEach(chamada => {
                        const dataHora = new Date(chamada.datetime).toLocaleString('pt-BR');
                        tbody.append(`
                            <tr>
                                <td>${dataHora}</td>
                                <td>${chamada.origem}</td>
                                <td>${chamada.destino}</td>
                                <td>${chamada.fila}</td>
                                <td>${chamada.duracao} seg</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`<tr><td colspan="5" class="text-center">Nenhuma chamada encontrada hoje.</td></tr>`);
                }
            },
            error: function () {
                tbody.append(`<tr><td colspan="5" class="text-danger text-center">Erro ao carregar os dados.</td></tr>`);
            },
            complete: function () {
                spinner.addClass('d-none');
            }
        });
    }

    $('.fixed-card').click(function () {
        const modalId = $(this).data('target') || $(this).data('bs-target');
        const tipo = modalId.replace('#modal', '');
        const qtd = $(`.qtd-registros[data-tipo="${tipo}"]`).val() || 10;

        carregarChamadas(tipo, qtd);

        const modal = new bootstrap.Modal(document.getElementById('modal' + tipo));
        modal.show();
    });

    $('.qtd-registros').on('change', function () {
        const tipo = $(this).data('tipo');
        const qtd = $(this).val();
        carregarChamadas(tipo, qtd);
    });
});
</script>

<style>
.card {
    background-color: #1ea965;
    color: white;
    border-radius: 10px;
    padding: 3px;
    text-align: center;
    box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.15);
    font-size: 0.75rem;
    min-width: 250px;
    max-width: 250px;
    position: relative;
}

.card-body {
    padding: 5px;
}

.card-title {
    font-size: 1rem;
}

.card .status {
    font-size: 0.8rem;
}

.call-info {
    font-size: 0.75rem;
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

@extends('day.layout')

@section('title', 'Painel de Atendimento')

@section('content')
<div class="container mt-3">
    @if (session('error'))
        <div class="alert alert-danger w-100">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <h1 class="fs-2 fw-bold text-center mb-4">Painel de Atendimento</h1>

    @php
        $ramalDoUsuario = $ramaisOnline->firstWhere('user_name', auth()->user()->name);
    @endphp

    <div class="d-flex justify-content-between mb-3">
        @if ($ramalDoUsuario)
            <form action="{{ route('desassociar-ramal') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    Desassociar Ramal ({{ $ramalDoUsuario->name }})
                </button>
            </form>
        @else
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ramalModal">
                Associar Ramal
            </button>
        @endif

        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#minhasLigacoesModal">
            Minhas Ligações
        </button>
    </div>

    <div id="painelChamada" class="card mt-4 p-3 shadow-sm" style="display: none;">
        <h3 class="fs-5 fw-bold">Última Chamada</h3>
        <p><strong>Usuário:</strong> <span id="infoUsuario"></span></p>
        <p><strong>Ramal:</strong> <span id="infoRamal"></span></p>
        <p><strong>Número:</strong> <span id="infoNumero"></span></p>
        <p><strong>Fila:</strong> <span id="infoFila"></span></p>
        <p><strong>Tempo:</strong> <span id="infoTempo"></span></p>
        <p><strong>Canal:</strong> <span id="infoChannel"></span></p>
        <p><strong>Protocolo:</strong> <span id="infoProtocolo"></span></p>
    </div>
</div>

<!-- Modal de Associar Ramal -->
<div class="modal fade" id="ramalModal" tabindex="-1" aria-labelledby="ramalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Escolha um Ramal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                    @foreach ($ramaisOnline as $ramal)
                        @if (!$ramal->user_name)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $ramal->name }}</span>
                                <form action="{{ route('associar-ramal') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="ramal_id" value="{{ $ramal->id }}">
                                    <button type="submit" class="btn btn-sm btn-success">Associar</button>
                                </form>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Minhas Ligações -->
<div class="modal fade" id="minhasLigacoesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Minhas Ligações</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group" id="listaLigacoes"></ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script>
    const socket = io("https://fttelecom.cloud:4000");
    const usuarioAutenticado = "{{ auth()->user()->name }}";
    const csrfToken = "{{ csrf_token() }}";
    const status = "Em Chamada";

    const chamadasAtivas = {};
    let ultimaChamadaExibida = null;

    document.addEventListener("DOMContentLoaded", () => {
        carregarTodasLigacoes();

        const ultima = localStorage.getItem("ultimaChamada");
        if (ultima) {
            const call = JSON.parse(ultima);
            exibirPainelChamada(call);
        }
    });

    socket.on('fetch-unified-data-response', (data) => {
        console.log("Dados Recebidos:", data);

        if (!data.sippeers) {
            console.error("Sippeers não encontrado!");
            return;
        }

        const chamadas = data.sippeers.filter(call => call.user_name === usuarioAutenticado && call.call_state === status );
        console.log(`Chamadas encontradas para ${usuarioAutenticado}:`, chamadas);

        if (chamadas.length) {
            chamadas.forEach(call => salvarLigacao(call));
        }
    });

    function salvarLigacao(call) {
        if (!call || !call.user_name || !call.channel) {
            console.error("Call inválida:", call);
            return;
        }

        const callId = call.channel;

        if (!chamadasAtivas[callId]) {
            chamadasAtivas[callId] = {
                salvaInicial: false,
                ultimaLigacao: call,
                ativa: true
            };

            registrarLigacao(call);
            chamadasAtivas[callId].salvaInicial = true;
            exibirPainelChamada(call);
        } else {
            chamadasAtivas[callId].ultimaLigacao = call;
        }

        if (call.call_state !== "Em Chamada" && chamadasAtivas[callId].ativa) {
            registrarLigacao(chamadasAtivas[callId].ultimaLigacao);
            chamadasAtivas[callId].ativa = false;
        }
    }

    function exibirPainelChamada(call) {
    if (!call) return;

    ultimaChamadaExibida = call;

    document.getElementById("infoUsuario").textContent = call.user_name || "-";
    document.getElementById("infoRamal").textContent = call.name || call.ramal || "-";
    document.getElementById("infoNumero").textContent = call.calling_to || "-";
    document.getElementById("infoFila").textContent = call.queueName || call.queue_name || "-";
    document.getElementById("infoTempo").textContent = call.call_duration || "00:00";
    document.getElementById("infoChannel").textContent = call.channel || "-";
    document.getElementById("infoProtocolo").textContent = call.protocolo || "-"; // já vem direto

    document.getElementById("painelChamada").style.display = "block";
    localStorage.setItem("ultimaChamada", JSON.stringify(call));
}


    function registrarLigacao(call) {
        axios.post("/calls/store", {
            user_name: call.user_name,
            ramal: call.name,
            calling_to: call.calling_to,
            queue_name: call.queueName,
            call_duration: call.call_duration || "00:00",
            channel: call.channel,
            protocolo: call.protocolo // <-- Aqui está o ajuste importante
        }, {
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "Content-Type": "application/json"
            }
        })
    }

    async function carregarTodasLigacoes() {
    try {
        const response = await fetch("/calls/user");
        const ligacoes = await response.json();
        const lista = document.getElementById("listaLigacoes");
        lista.innerHTML = "";

        ligacoes.forEach(call => {
            let item = document.createElement("li");
            item.classList.add("list-group-item");
            item.innerHTML = `
                <strong>${call.user_name}</strong> - ${call.ramal} - ${call.calling_to} - ${call.queue_name} - ${call.call_duration}
                <br>
                <small>Canal: ${call.channel}</small><br>
                <small>Protocolo: ${call.protocolo || '-'}</small>
            `;
            lista.appendChild(item);
        });

        // Exibir a última chamada no painel principal
        if (ligacoes.length > 0) {
            exibirPainelChamada(ligacoes[0]); // a mais recente
        }

    } catch (error) {
        console.error("Erro ao carregar ligações:", error);
    }
}

</script>
@endsection

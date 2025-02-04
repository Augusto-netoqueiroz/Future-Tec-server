@extends('day.layout')

@section('title', 'Painel de Atendimento')

@section('content')
<div class="container mt-2">
    <!-- Mensagens de erro ou sucesso -->
    @if (session('error'))
        <div class="alert alert-danger w-100">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success w-100">
            {{ session('success') }}
        </div>
    @endif

    <!-- Título -->
    <h1 class="fs-2 fw-bold text-center mb-5">Bem-vindo ao Painel de Atendimento</h1>

    <!-- Seção de Chamadas Ativas -->
    <h2 class="fs-4 fw-bold mb-4">Chamadas Ativas</h2>
    <div id="chamadas-ativas" class="alert alert-info">Nenhuma chamada ativa</div>

    <!-- Tabela de Ramais -->
    <h2 class="fs-4 fw-bold mb-4">Ramais Disponíveis para Associações</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Ramal</th>
                    <th>Status</th>
                    <th>Atendente</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($ramaisOnline as $ramal)
                    <tr>
                        <td>{{ $ramal->name }}</td>
                        <td>
                            @if ($ramal->user_name)
                                <span class="badge bg-success">Associado</span>
                            @else
                                <span class="badge bg-secondary">Livre</span>
                            @endif
                        </td>
                        <td>{{ $ramal->user_name ?? 'Nenhum atendente' }}</td>
                        <td>
                            @if (!$ramal->user_name)
                                <form action="{{ route('associar-ramal') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="ramal_id" value="{{ $ramal->id }}">
                                    <button type="submit" class="btn btn-primary btn-sm">Associar a mim</button>
                                </form>
                            @else
                                <form action="{{ route('desassociar-ramal') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Desassociar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Importação do Socket.IO -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script>
    const socket = io("http://93.127.212.237:4000");

    socket.on('fetch-unified-data-response', (data) => {
        console.log("Recebido evento fetch-unified-data-response:", data);
        atualizarChamadas(data.sippeers, data.queueData);
    });

   function atualizarChamadas(sippeers, queueData) {
    const chamadasAtivasDiv = document.getElementById("chamadas-ativas");
    let chamadas = [];
    

    if (sippeers && sippeers.length > 0) {
    sippeers.forEach(call => {
        if (call.user_name === usuarioAutenticado && call.call_state.trim().toLowerCase().includes("em chamada")) { 
            chamadas.push({
                usuario: call.user_name,
                ramal: call.name,
                numero: call.calling_to,
                status: call.call_state,
                fila: call.queueName ?? "Sem fila",
                tempo: call.call_duration ?? "0s"
            });
        }
    });
}


    if (queueData && queueData.length > 0) {
        queueData.forEach(queue => {
            queue.callers.forEach(caller => {
                chamadas.push({
                    agente: queue.queueName,
                    numero: caller.caller,
                    status: `Na fila (${caller.waitTime})`
                });
            });
        });
    }

    if (chamadas.length > 0) {
        let html = '<ul class="list-group">';
        chamadas.forEach(call => {
            html += `<li class="list-group-item">${call.usuario} - ${call.ramal} - ${call.numero} - ${call.fila} - ${call.tempo} (${call.status})</li>`;
        });
        html += '</ul>';
        chamadasAtivasDiv.innerHTML = html;
    } else {
        chamadasAtivasDiv.innerHTML = "Nenhuma chamada ativa";
    }
}

</script>

<script>
    const usuarioAutenticado = "{{ auth()->user()->name }}";
</script>
@endsection

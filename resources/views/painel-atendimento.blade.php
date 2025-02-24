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

    <div class="d-flex justify-content-end mb-3">
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
    </div>

    <h2 class="fs-4 fw-bold mb-3">Chamadas Ativas</h2>
    <div class="table-responsive">
        <table class="table table-striped table-hover tabela-chamadas">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Ramal</th>
                    <th>Número</th>
                    <th>Fila</th>
                    <th>Tempo</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="chamadas-ativas">
                <tr>
                    <td colspan="6" class="text-center text-muted">Nenhuma chamada ativa</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.6.0/socket.io.min.js"></script>
<script>
    const socket = io("http://93.127.212.237:4000");
    const usuarioAutenticado = "{{ auth()->user()->name }}";

    socket.on('fetch-unified-data-response', (data) => {
        atualizarChamadas(data.sippeers);
    });

    function atualizarChamadas(sippeers) {
        const chamadasAtivasTbody = document.getElementById("chamadas-ativas");
        chamadasAtivasTbody.innerHTML = '';

        let chamadas = sippeers?.filter(call => 
            call.user_name === usuarioAutenticado && call.call_state?.toLowerCase().includes("em chamada")
        ) || [];

        if (chamadas.length > 0) {
            chamadas.forEach(call => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${call.user_name}</td>
                    <td>${call.name}</td>
                    <td>${call.calling_to}</td>
                    <td>${call.queueName || "Sem fila"}</td>
                    <td>${call.call_duration}</td>
                    <td>${call.call_state}</td>
                `;
                chamadasAtivasTbody.appendChild(row);
            });
        } else {
            chamadasAtivasTbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">Nenhuma chamada ativa</td></tr>`;
        }
    }
</script>

<style>
    .tabela-chamadas {
        width: 100%;
        max-width: 100%;
        border-collapse: collapse;
    }

    .tabela-chamadas th {
        background-color: #007bff;
        color: white;
        text-align: center;
        padding: 10px;
    }

    .tabela-chamadas td {
        text-align: center;
        padding: 10px;
    }

    .modal-body .list-group-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>
@endsection

@extends('day.layout')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Relatório de Ligações</h1>

    <form id="filterForm" class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-3 mb-3">
                <input type="text" id="search" name="search" class="form-control form-control-sm" placeholder="Pesquisar por Origem, Destino ou Unique ID">
            </div>
            <div class="col-md-2 mb-3">
                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 mb-3">
                <input type="date" id="end_date" name="end_date" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 mb-3">
                <select id="ramal" name="ramal" class="form-control form-control-sm">
                    <option value="">Todos os Ramais</option>
                    @foreach($ramais as $ramal)
                        <option value="{{ $ramal }}">{{ $ramal }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3 d-flex">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                <button type="button" id="clearFilters" class="btn btn-secondary btn-sm ms-2 w-100">Limpar</button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>Data</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Unique ID</th>
                    <th>Duração</th>
                    <th>Status</th>
                    <th>Lastdata</th>
                    <th>Agente</th>
                    <th>Gravação</th>
                </tr>
            </thead>
            <tbody id="table-body">
                @foreach($chamadas as $chamada)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($chamada->calldate)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $chamada->src }}</td>
                        <td>{{ $chamada->dst }}</td>
                        <td>{{ $chamada->uniqueid }}</td>
                        <td>{{ $chamada->duration }} segundos</td>
                        <td>{{ $chamada->disposition }}</td>
                        <td>{{ $chamada->lastdata }}</td>
                        <td>{{ $chamada->Agente }}</td>
                        <td>
                            @if($chamada->recordingfile)
                                <a href="{{ url('gravacoes/' . $chamada->recordingfile) }}" class="btn btn-success btn-sm" download>Baixar</a>
                            @else
                                <span class="text-muted">Sem gravação</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div id="pagination-links">
        {{ $chamadas->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
</div>

<script>
document.getElementById("filterForm").addEventListener("submit", function(event) {
    event.preventDefault();

    let formData = new FormData(this);
    let queryString = new URLSearchParams(formData).toString();

    fetch("{{ route('relatorios.ligacoes') }}?" + queryString, {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(response => response.json())
    .then(data => {
        atualizarTabela(data.chamadas);
    })
    .catch(error => console.error("Erro ao buscar os dados:", error));
});

function atualizarTabela(chamadas) {
    let tableBody = document.getElementById("table-body");
    tableBody.innerHTML = "";

    chamadas.data.forEach(chamada => {
        let data = new Date(chamada.calldate).toLocaleString('pt-BR');
        let row = `
            <tr>
                <td>${data}</td>
                <td>${chamada.src}</td>
                <td>${chamada.dst}</td>
                <td>${chamada.uniqueid}</td>
                <td>${chamada.duration} segundos</td>
                <td>${chamada.disposition}</td>
                <td>${chamada.lastdata}</td>
                <td>${chamada.Agente}</td>
                <td>${chamada.recordingfile ? `<a href="/gravacoes/${chamada.recordingfile}" class="btn btn-success btn-sm" download>Baixar</a>` : '<span class="text-muted">Sem gravação</span>'}</td>
            </tr>`;
        tableBody.innerHTML += row;
    });

    // Atualizar paginação (opcional: pode carregar dinamicamente também)
    document.getElementById("pagination-links").innerHTML = "";
}

document.getElementById("clearFilters").addEventListener("click", function() {
    document.getElementById("filterForm").reset();

    fetch("{{ route('relatorios.ligacoes') }}", {
        headers: { "X-Requested-With": "XMLHttpRequest" }
    })
    .then(response => response.json())
    .then(data => {
        atualizarTabela(data.chamadas);
    })
    .catch(error => console.error("Erro ao buscar os dados:", error));
});
</script>
@endsection

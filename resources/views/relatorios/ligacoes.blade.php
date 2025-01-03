@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Relatório de Ligações</h1>

    <!-- Filtro e Pesquisa -->
    <form method="GET" action="{{ route('relatorios.ligacoes') }}" class="mb-4">
        <div class="row align-items-center">
            <!-- Campo de Pesquisa -->
            <div class="col-md-3 mb-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Pesquisar por Origem, Destino ou Unique ID" value="{{ request()->input('search') }}">
            </div>
            <!-- Filtro de Data Inicial -->
            <div class="col-md-2 mb-3">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request()->input('start_date') }}">
            </div>
            <!-- Filtro de Data Final -->
            <div class="col-md-2 mb-3">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request()->input('end_date') }}">
            </div>
            <!-- Botão de Filtro -->
            <div class="col-md-2 mb-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Tabela de Chamadas -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm">
            <thead class="thead-dark">
                <tr>
                    <th>Data</th>
                    <th>Origem</th>
                    <th>Destino</th>
                    <th>Unique ID</th>
                    <th>Duração</th>
                    <th>Agente</th>
                    <th>Gravação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($chamadas as $chamada)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($chamada->calldate)->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $chamada->src }}</td>
                        <td>{{ $chamada->dst }}</td>
                        <td>{{ $chamada->uniqueid }}</td>
                        <td>{{ $chamada->duration }} segundos</td>
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

    <!-- Paginação -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <span>Exibindo {{ $chamadas->count() }} de {{ $chamadas->total() }} registros</span>
        </div>
        <div>
            {{ $chamadas->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>
@endsection


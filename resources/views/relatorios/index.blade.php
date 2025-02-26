@extends('day.layout')

@section('content')
<div class="container">
    <h2>Relatório de Atividades</h2>

    <!-- Filtros -->
    <form id="filtro-form">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="acao" id="acao" class="form-control" placeholder="Filtrar por Ação">
            </div>
            <div class="col-md-3">
                <input type="text" name="descricao" id="descricao" class="form-control" placeholder="Filtrar por Descrição">
            </div>
            <div class="col-md-3">
                <input type="date" name="data_inicio" id="data_inicio" class="form-control">
            </div>
            <div class="col-md-3">
                <input type="date" name="data_fim" id="data_fim" class="form-control">
            </div>
            <div class="col-md-3 mt-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Tabela de Registros -->
    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Descrição</th>
                <th>IP</th>
                <th>Data</th>
            </tr>
        </thead>
        <tbody id="tabela-corpo">
            @foreach($atividades as $atividade)
                <tr>
                    <td>{{ $atividade->id }}</td>
                    <td>{{ $atividade->usuario }}</td>
                    <td>{{ $atividade->acao }}</td>
                    <td>{{ $atividade->descricao }}</td>
                    <td>{{ $atividade->ip }}</td>
                    <td>{{ $atividade->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Paginação -->
        <div>
            {{ $atividades->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
</div>

<script>
document.getElementById('filtro-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    let params = new URLSearchParams(new FormData(this)).toString();
    window.location.href = "{{ route('relatorios.index') }}?" + params;
});
</script>
@endsection

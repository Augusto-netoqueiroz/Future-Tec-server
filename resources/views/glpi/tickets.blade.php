@extends('day.layout')

@section('title', 'Lista de Tickets')

@section('content')
<div class="container mt-4">
    <h3 class="fw-bold text-primary"><i class="fas fa-list"></i> Lista de Tickets</h3>

    {{-- Exibir mensagem de erro, se houver --}}
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    {{-- Formulário de Filtro --}}
    <form id="filterForm" class="mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Usuário</label>
                <select name="user_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($users as $user)
                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Entidade</label>
                <select name="entity_id" class="form-select">
                    <option value="">Todas</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity['id'] }}">{{ $entity['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Data Inicial</label>
                <input type="date" name="start_date" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Data Final</label>
                <input type="date" name="end_date" class="form-control">
            </div>

            <div class="col-md-12 text-end">
                <button type="submit" id="filterButton" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <span id="loading" class="text-primary" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                </span>
            </div>
        </div>
    </form>

    {{-- Tabela de Tickets --}}
    <div class="table-responsive">
    <table class="table table-bordered table-striped">
    <thead class="table-primary">
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Requerente</th>
            <th>Categoria</th>
            <th>Origem</th>
            <th>status</th>
            <th>Descrição</th>
            <th>Entidade</th>
            <th>Data de Abertura</th>
        </tr>
    </thead>
    <tbody id="ticketTableBody">
        <tr>
            <td colspan="8" class="text-center">Nenhum ticket encontrado. Use o filtro acima.</td>
        </tr>
    </tbody>
</table>

    </div>
</div>

{{-- Script para filtrar tickets via AJAX --}}
<script>
  document.getElementById('filterForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Evita recarregar a página

    let formData = new FormData(this);
    let queryString = new URLSearchParams(formData).toString();
    let url = "{{ route('glpi.tickets.filter') }}" + '?' + queryString;

    document.getElementById('filterButton').disabled = true;
    document.getElementById('loading').style.display = 'inline';

    fetch(url, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log("JSON Recebido:", data); // Depuração

        let tableBody = document.getElementById('ticketTableBody');
        tableBody.innerHTML = ''; // Limpa a tabela

        if (!data.data || data.data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center">Nenhum ticket encontrado.</td></tr>';
        } else {
            let rows = data.data.map(ticket => `
                <tr>
                    <td>${ticket.id}</td>
                    <td>${ticket.titulo}</td>
                    <td>${ticket.requerente}</td>
                    <td>${ticket.categoria}</td>
                    <td>${ticket.origem}</td>
                    <td>${ticket.status}</td>
                    <td>${ticket.descricao}</td>
                    <td>${ticket.entidade}</td>
                    <td>${ticket.data_abertura}</td>
                </tr>
            `).join('');

            tableBody.innerHTML = rows;
        }

        document.getElementById('filterButton').disabled = false;
        document.getElementById('loading').style.display = 'none';
    })
    .catch(error => {
        console.error('Erro no fetch:', error);
        document.getElementById('filterButton').disabled = false;
        document.getElementById('loading').style.display = 'none';
    });
});


</script>

@endsection

@extends('day.layout')

@section('title', 'Lista de Tickets')

@section('content')
<div class="container mt-4">
    <h3 class="fw-bold text-primary"><i class="fas fa-list"></i> Lista de Tickets</h3>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                <button type="submit" id="filterButton" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <span id="loading" class="text-primary" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Carregando...
                </span>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>Ações</th>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Requerente</th>
                    <th>Categoria</th>
                    <th>Origem</th>
                    <th>Status</th>
                    <th>Descrição</th>
                    <th>Entidade</th>
                    <th>Data de Abertura</th>
                </tr>
            </thead>
            <tbody id="ticketTableBody">
                <tr>
                    <td colspan="10" class="text-center">Nenhum ticket encontrado. Use o filtro acima.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <nav>
        <ul class="pagination justify-content-center" id="pagination"></ul>
    </nav>

</div>

<script>
document.getElementById('filterForm').addEventListener('submit', function(event) {
    event.preventDefault();
    loadTickets(1);
});

function loadTickets(page) {
    let formData = new FormData(document.getElementById('filterForm'));
    formData.append('page', page);
    let queryString = new URLSearchParams(formData).toString();
    let url = "{{ route('glpi.tickets.filter') }}" + '?' + queryString;

    document.getElementById('filterButton').disabled = true;
    document.getElementById('loading').style.display = 'inline';

    fetch(url, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        let tableBody = document.getElementById('ticketTableBody');
        tableBody.innerHTML = '';

        if (!data.data || data.data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="10" class="text-center">Nenhum ticket encontrado.</td></tr>';
        } else {
            let rows = data.data.map(ticket => {
                let descricaoCurta = ticket.descricao.length > 50 ? ticket.descricao.substring(0, 50) + "..." : ticket.descricao;
                return `
                    <tr>
                        <td>
                            <a href="/tickets/${ticket.id}/edit" class="btn btn-warning btn-xs" style="scale: 0.8;">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="/tickets/${ticket.id}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-xs" style="scale: 0.8;" onclick="return confirm('Tem certeza que deseja excluir este ticket?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                        <td>${ticket.id}</td>
                        <td>${ticket.titulo}</td>
                        <td>${ticket.requerente}</td>
                        <td>${ticket.categoria}</td>
                        <td>${ticket.origem}</td>
                        <td>${ticket.status}</td>
                        <td title="${ticket.descricao}">${descricaoCurta}</td>
                        <td>${ticket.entidade}</td>
                        <td>${ticket.data_abertura}</td>
                    </tr>
                `;
            }).join('');

            tableBody.innerHTML = rows;
        }

        updatePagination(data.pagination);

        document.getElementById('filterButton').disabled = false;
        document.getElementById('loading').style.display = 'none';
    })
    .catch(error => {
        console.error('Erro no fetch:', error);
        document.getElementById('filterButton').disabled = false;
        document.getElementById('loading').style.display = 'none';
    });
}

function updatePagination(pagination) {
    let paginationContainer = document.getElementById('pagination');
    paginationContainer.innerHTML = '';

    if (!pagination || pagination.total_pages <= 1) return;

    let prevDisabled = pagination.current_page === 1 ? 'disabled' : '';
    let nextDisabled = pagination.current_page === pagination.total_pages ? 'disabled' : '';

    paginationContainer.innerHTML += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" onclick="loadTickets(${pagination.current_page - 1}); return false;">Anterior</a>
        </li>
    `;

    for (let i = 1; i <= pagination.total_pages; i++) {
        let active = i === pagination.current_page ? 'active' : '';
        paginationContainer.innerHTML += `
            <li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadTickets(${i}); return false;">${i}</a>
            </li>
        `;
    }

    paginationContainer.innerHTML += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" onclick="loadTickets(${pagination.current_page + 1}); return false;">Próximo</a>
        </li>
    `;
}

loadTickets(1);
</script>

@endsection

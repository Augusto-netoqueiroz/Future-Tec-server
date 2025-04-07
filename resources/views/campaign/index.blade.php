@extends('day.layout') 

@section('content')

<div class="container mt-5">
    <h1>Campanhas Criadas</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <table class="table table-striped table-hover align-middle mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Data Início</th>
            <th>Data Fim</th>
            <th>Áudio</th>
            <th>Status</th>
            <th>Total</th>
            <th>Concluídos</th>
            <th>Atendidos</th>
            <th>Não Atendidos</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <a href="{{ route('campaign.criar') }}" class="btn btn-primary mt-3">Criar Nova Campanha</a>
        @foreach ($campaigns as $campaign)
            <tr>
                <td>{{ $campaign->id }}</td>
                <td>{{ $campaign->name }}</td>
                <td>{{ $campaign->start_date }}</td>
                <td>{{ $campaign->end_date }}</td>
                <td>{{ $campaign->audio_file }}</td>
                <td>
                    @if ($campaign->status == 'pending')
                        <span class="badge bg-warning">Pendente</span>
                    @elseif ($campaign->status == 'in_progress')
                        <span class="badge bg-primary">Em Progresso</span>
                    @elseif ($campaign->status == 'stopped')
                        <span class="badge bg-secondary">Parada</span>
                    @else
                        <span class="badge bg-success">Concluída</span>
                    @endif
                </td>
                <td>{{ $campaign->total }}</td>
                <td>{{ $campaign->concluídos ?? $campaign->concluidos }}</td>
                <td>{{ $campaign->atendidos }}</td>
                <td>{{ $campaign->nao_atendidos }}</td>
                <td class="text-center">
                    <!-- Botões de ação com base no status -->
                    <div class="d-flex justify-content-center gap-2">
                        @if ($campaign->status == 'pending' || $campaign->status == 'stopped')
                            <button class="btn btn-sm btn-success start-campaign" data-id="{{ $campaign->id }}">Iniciar</button>
                        @elseif ($campaign->status == 'in_progress')
                            <button class="btn btn-sm btn-danger stop-campaign" data-id="{{ $campaign->id }}">Parar</button>
                        @endif
                        <button class="btn btn-sm btn-warning reset-campaign" data-id="{{ $campaign->id }}">Resetar</button>
                        <a href="{{ route('campaign.delete', $campaign->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta campanha?')">Excluir</a>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

    
    <!-- Paginação -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <span>Exibindo {{ $campaigns->count() }} de {{ $campaigns->total() }} registros</span>
        </div>
        <div>
            {{ $campaigns->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".start-campaign").forEach(button => {
        button.addEventListener("click", function() {
            let campaignId = this.getAttribute("data-id");

            if (!confirm("Tem certeza que deseja iniciar esta campanha?")) {
                return;
            }

            fetch(`/campaign/${campaignId}/start`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                alert("Erro ao iniciar a campanha!");
                console.error(error);
            });
        });
    });

    document.querySelectorAll(".stop-campaign").forEach(button => {
        button.addEventListener("click", function() {
            let campaignId = this.getAttribute("data-id");

            if (!confirm("Tem certeza que deseja parar esta campanha?")) {
                return;
            }

            fetch(`/campaign/${campaignId}/stop`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                alert("Erro ao parar a campanha!");
                console.error(error);
            });
        });
    });

    document.querySelectorAll(".reset-campaign").forEach(button => {
        button.addEventListener("click", function() {
            let campaignId = this.getAttribute("data-id");

            if (!confirm("Tem certeza que deseja resetar esta campanha?")) {
                return;
            }

            fetch(`/campaign/${campaignId}/restart`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(error => {
                alert("Erro ao resetar a campanha!");
                console.error(error);
            });
        });
    });
});
</script>

@endsection

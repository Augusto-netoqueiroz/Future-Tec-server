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

        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nome</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Áudio</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
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
                            @else
                                <span class="badge bg-success">Concluída</span>
                            @endif
                        </td>
                        <td>
                            @if ($campaign->status == 'pending')
                            <button class="btn btn-sm btn-success start-campaign" data-id="{{ $campaign->id }}">Iniciar</button>
                            @endif

                            <a href="{{ route('campaign.show', $campaign->id) }}" class="btn btn-sm btn-info">Ver</a>
                            <a href="{{ route('campaign.delete', $campaign->id) }}" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta campanha?')">Excluir</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('campaign.create') }}" class="btn btn-primary mt-3">Criar Nova Campanha</a>
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
                    location.reload(); // Atualiza a página para mudar o status da campanha
                })
                .catch(error => {
                    alert("Erro ao iniciar a campanha!");
                    console.error(error);
                });
            });
        });
    });
</script>

    
    
    @endsection

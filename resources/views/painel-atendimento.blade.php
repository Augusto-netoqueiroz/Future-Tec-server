@extends('day.layout')

@section('title', 'Painel de Atendimento')

@section('content')
<div class="container-xxl py-8">
    <!-- Mensagens de erro ou sucesso -->
    @if (session('error'))
        <div class="alert alert-danger w-100 mt-3">
            {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success w-100 mt-3">
            {{ session('success') }}
        </div>
    @endif

    <!-- Título -->
    <h1 class="fs-2 fw-bold text-center mt-5">Bem-vindo ao Painel de Atendimento</h1>

    <!-- Tabela de Ramais -->
    <h2 class="fs-4 fw-bold mt-8 mb-4">Ramais Disponíveis para Associações</h2>
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
                                    <button type="submit" class="btn btn-primary">Associar a mim</button>
                                </form>
                            @else
                                <form action="{{ route('desassociar-ramal') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Desassociar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@extends('day.layout')

@section('title', "Gerenciar Fila: {{ $fila->name }}")

@section('content')
<div class="container-xxl py-8">
    <!-- Título da página -->
    <h1 class="fs-2 fw-bold text-center mb-6">Gerenciar Fila: {{ $fila->name }}</h1>

    <!-- Membros Associados -->
    <div class="mb-6">
        <h2 class="fs-4 fw-bold mb-4">Membros Associados</h2>
        @if($members->isNotEmpty())
            <ul class="list-group">
                @foreach($members as $member)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $member->membername }}</strong><br>
                            Prioridade: {{ $member->penalty }} | Pausado: {{ $member->paused ? 'Sim' : 'Não' }}
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Formulário para alterar estado -->
                            <form action="{{ route('filas.updateMemberState', [$fila->id, $member->uniqueid]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="paused" value="{{ $member->paused ? 0 : 1 }}">
                                <input type="hidden" name="penalty" value="{{ $member->penalty }}">
                                <button type="submit" class="btn btn-warning btn-sm">{{ $member->paused ? 'Ativar' : 'Pausar' }}</button>
                            </form>
                            <!-- Formulário para remover membro -->
                            <form action="{{ route('filas.removeMember', [$fila->id, $member->user_id]) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover este membro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-muted">Nenhum membro associado.</p>
        @endif
    </div>

    <!-- Associar Novos Usuários -->
    <div>
        <h2 class="fs-4 fw-bold mb-4">Associar Usuários à Fila</h2>
        @if($users->isNotEmpty())
            <form action="{{ route('filas.associateMember', $fila->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    @foreach($users as $user)
                        <div class="form-check">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user-{{ $user->id }}" class="form-check-input">
                            <label for="user-{{ $user->id }}" class="form-check-label">{{ $user->name }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="form-group mb-3">
                    <label for="penalty" class="form-label">Prioridade</label>
                    <input type="number" name="penalty" id="penalty" class="form-control" placeholder="0" value="0" required>
                </div>
                <div class="form-group mb-3">
                    <label for="paused" class="form-label">Pausado</label>
                    <select name="paused" id="paused" class="form-control">
                        <option value="0" selected>Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Associar Selecionados</button>
            </form>
        @else
            <p class="text-muted">Todos os usuários já estão associados à fila.</p>
        @endif
    </div>
</div>
@endsection

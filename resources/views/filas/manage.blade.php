@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Gerenciar Fila: {{ $fila->name }}</h1>

    {{-- Membros Associados --}}
    <div class="mt-4">
        <h3>Membros Associados</h3>
        <ul class="list-group">
            @forelse($members as $member)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $member->membername }}</strong><br>
                        Prioridade: {{ $member->penalty }} | Pausado: {{ $member->paused ? 'Sim' : 'Não' }}
                    </div>
                    <div>
                        {{-- Formulário para alterar prioridade/estado --}}
                        <form action="{{ route('filas.updateMemberState', [$fila->id, $member->uniqueid]) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="paused" value="{{ $member->paused ? 0 : 1 }}">
                            <input type="hidden" name="penalty" value="{{ $member->penalty }}">
                            <button type="submit" class="btn btn-warning btn-sm">{{ $member->paused ? 'Ativar' : 'Pausar' }}</button>
                        </form>
                        {{-- Formulário para remover membro --}}
                        <form action="{{ route('filas.removeMember', [$fila->id, $member->user_id]) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-muted">Nenhum membro associado.</li>
            @endforelse
        </ul>
    </div>

  {{-- Associar Novos Usuários --}}
<div class="mt-5">
    <h3>Associar Usuários à Fila</h3>
    @if($users->isNotEmpty())
        <form action="{{ route('filas.associateMember', $fila->id) }}" method="POST">
            @csrf
            <div class="form-group">
                @foreach($users as $user)
                    <div class="form-check">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user-{{ $user->id }}" class="form-check-input">
                        <label for="user-{{ $user->id }}" class="form-check-label">{{ $user->name }}</label>
                    </div>
                @endforeach
            </div>
            <div class="form-group mt-3">
                <label for="penalty">Prioridade</label>
                <input type="number" name="penalty" id="penalty" class="form-control" placeholder="0" value="0" required>
            </div>
            <div class="form-group mt-3">
                <label for="paused">Pausado</label>
                <select name="paused" id="paused" class="form-control">
                    <option value="0" selected>Não</option>
                    <option value="1">Sim</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Associar Selecionados</button>
        </form>
    @else
        <p class="text-muted">Todos os usuários já estão associados à fila.</p>
    @endif
</div>

</div>
@endsection

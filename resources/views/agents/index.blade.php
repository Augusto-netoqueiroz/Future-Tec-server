@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Vincular Usuários a Agentes</h1>

    <!-- Mensagem de sucesso -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Formulário para vincular usuário a agente -->
    <form action="{{ route('agents.store') }}" method="POST" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="user_id" class="form-label">Usuário:</label>
            <select name="user_id" id="user_id" class="form-control" required>
                <option value="">Selecione um usuário</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="agent" class="form-label">Agente (Ex: Agent/1001):</label>
            <input type="text" name="agent" id="agent" class="form-control" placeholder="Agent/XXXX" required>
        </div>
        <button type="submit" class="btn btn-primary">Vincular</button>
    </form>

    <!-- Lista de associações existentes -->
    <h2>Associações Existentes</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Agente</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($userAgents as $userAgent)
                <tr>
                    <td>{{ $userAgent->user->name }}</td>
                    <td>{{ $userAgent->agent }}</td>
                    <td>
                        <!-- Botões de Pausar e Despausar -->
                        <form action="{{ route('agents.pause') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="agent" value="{{ $userAgent->agent }}">
                            <input type="hidden" name="paused" value="1"> <!-- 1 para pausar -->
                            <button type="submit" class="btn btn-warning btn-sm">Pausar</button>
                        </form>
                        
                        <form action="{{ route('agents.pause') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="agent" value="{{ $userAgent->agent }}">
                            <input type="hidden" name="paused" value="0"> <!-- 0 para despausar -->
                            <button type="submit" class="btn btn-success btn-sm">Despausar</button>
                        </form>

                        <!-- Botão de Remover -->
                        <form action="{{ route('agents.destroy', $userAgent->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja remover esta associação?')">Remover</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Nenhuma associação encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

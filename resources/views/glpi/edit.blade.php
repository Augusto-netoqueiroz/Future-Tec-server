@extends('day.layout')

@section('content')
<div class="container">
    <h2>Editar Ticket #{{ $ticket['id'] ?? '' }}</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('tickets.update', $ticket['id'] ?? 0) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="title" class="form-label">Título</label>
            <input type="text" id="title" name="title" class="form-control" value="{{ $ticket['name'] ?? '' }}" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Descrição</label>
            <textarea id="description" name="description" class="form-control" rows="4" required>{{ $ticket['content'] ?? '' }}</textarea>
        </div>
        
        <!-- Status -->
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-control">
                @foreach([1 => 'Novo', 2 => 'Em andamento (atribuído)', 3 => 'Em andamento (planejado)', 4 => 'Pendente', 5 => 'Solucionado', 6 => 'Fechado'] as $key => $value)
                    <option value="{{ $key }}" {{ (isset($ticket['status']) && $ticket['status'] == $key) ? 'selected' : '' }}>
                        {{ $value }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Categoria -->
        <div class="mb-3">
            <label for="category" class="form-label">Categoria</label>
            <select id="category" name="category" class="form-control">
                @foreach($categories ?? [] as $category)
                    <option value="{{ $category['id'] }}" {{ (isset($ticket['itilcategories_id']) && $ticket['itilcategories_id'] == $category['id']) ? 'selected' : '' }}>
                        {{ $category['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Entidade -->
        <div class="mb-3">
            <label for="entity" class="form-label">Entidade</label>
            <select id="entity" name="entity" class="form-control">
                @foreach($entities ?? [] as $entity)
                    <option value="{{ $entity['id'] }}" {{ (isset($ticket['entities_id']) && $ticket['entities_id'] == $entity['id']) ? 'selected' : '' }}>
                        {{ $entity['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Usuário Responsável -->
        <div class="mb-3">
            <label for="user" class="form-label">Usuário Responsável</label>
            <select id="user" name="user" class="form-control">
                @foreach($users ?? [] as $user)
                    <option value="{{ $user['id'] }}" {{ (isset($ticket['users_id_recipient']) && $ticket['users_id_recipient'] == $user['id']) ? 'selected' : '' }}>
                        {{ $user['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
    <h1>Editar Usuário</h1>

    <!-- Exibindo as mensagens de sucesso ou erro -->
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

    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (Deixe em branco para não alterar)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <select class="form-control" id="cargo" name="cargo" required>
                <option value="Administrador" {{ old('cargo', $user->cargo) == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                <option value="Gerente" {{ old('cargo', $user->cargo) == 'Gerente' ? 'selected' : '' }}>Gerente</option>
                <option value="Operador" {{ old('cargo', $user->cargo) == 'Operador' ? 'selected' : '' }}>Operador</option>
                <option value="Outro" {{ old('cargo', $user->cargo) == 'Outro' ? 'selected' : '' }}>Outro</option>
            </select>
        </div>

        <button type="submit" class="btn btn-warning">Atualizar</button>
    </form>
@endsection

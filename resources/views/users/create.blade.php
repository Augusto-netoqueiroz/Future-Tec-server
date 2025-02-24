@extends('day.layout')

@section('title', 'Novo Usuário')

@section('content')
<div class="container mt-5">
    <h1 class="fw-bold text-primary mb-4">Cadastrar Novo Usuário</h1>

    {{-- Exibindo mensagens de erro de validação --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        {{-- Senha --}}
        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        {{-- Confirmação de Senha --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Senha</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        {{-- Cargo --}}
        <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <select class="form-control" id="cargo" name="cargo" required>
                <option value="Administrador" {{ old('cargo') == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                <option value="Gerente" {{ old('cargo') == 'Gerente' ? 'selected' : '' }}>Gerente</option>
                <option value="Supervisor" {{ old('cargo') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                <option value="Operador" {{ old('cargo') == 'Operador' ? 'selected' : '' }}>Operador</option>
            </select>
        </div>

        {{-- Avatar --}}
        <div class="mb-3">
            <label for="avatar" class="form-label">Avatar (opcional)</label>
            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-primary">Cadastrar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

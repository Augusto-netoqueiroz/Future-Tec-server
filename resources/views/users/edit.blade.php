@extends('day.layout')

@section('title', 'Editar Usuário')

@section('content')
<div class="container mt-5">
    <h1 class="fw-bold text-primary mb-4">Editar Usuário</h1>

    {{-- Exibindo mensagens de sucesso ou erro --}}
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center">
            <i class="ki-duotone ki-check fs-2x text-success me-3"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center">
            <i class="ki-duotone ki-warning fs-2x text-danger me-3"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

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

    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
        </div>

        {{-- Nova Senha --}}
        <div class="mb-3">
            <label for="password" class="form-label">Nova Senha (Deixe em branco para não alterar)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>

        {{-- Confirmação da Senha --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirme a Nova Senha</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>

        {{-- Cargo --}}
        <div class="mb-3">
            <label for="cargo" class="form-label">Cargo</label>
            <select class="form-control" id="cargo" name="cargo" required>
                <option value="Administrador" {{ old('cargo', $user->cargo) == 'Administrador' ? 'selected' : '' }}>Administrador</option>
                <option value="Gerente" {{ old('cargo', $user->cargo) == 'Gerente' ? 'selected' : '' }}>Gerente</option>
                <option value="Operador" {{ old('cargo', $user->cargo) == 'Operador' ? 'selected' : '' }}>Operador</option>
                <option value="Outro" {{ old('cargo', $user->cargo) == 'Outro' ? 'selected' : '' }}>Outro</option>
            </select>
        </div>

        {{-- Avatar Atual --}}
        <div class="mb-3">
            <label for="avatar" class="form-label">Avatar Atual</label>
            <div>
                @if ($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar do usuário" class="img-thumbnail" style="max-width: 150px;">
                @else
                    <p class="text-muted">Nenhum avatar definido.</p>
                @endif
            </div>
        </div>

        {{-- Novo Avatar --}}
        <div class="mb-3">
            <label for="avatar" class="form-label">Novo Avatar (opcional)</label>
            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
        </div>

        {{-- Botões --}}
        <button type="submit" class="btn btn-warning">Atualizar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Lista de Usuários')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Lista de Usuários</h1>

    {{-- Exibe mensagens de sucesso ou erro --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Botão para adicionar novo usuário --}}
    <div class="mb-3 text-end">
        <a href="{{ route('users.create') }}" class="btn btn-primary">Novo Usuário</a>
    </div>

    {{-- Tabela de usuários --}}
    <table class="table table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Cargo</th>
                <th>Estado</th>
                <th>IP</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->cargo }}</td>
                    <td>
                        <span class="badge 
                            @if($user->is_online) 
                                bg-success 
                            @else 
                                bg-danger 
                            @endif
                        ">
                            {{ $user->is_online ? 'Online' : 'Offline' }}
                        </span>
                    </td>
                    <td>{{ $user->ip_address ?? 'N/A' }}</td>
                    <td>
                        {{-- Botão de editar --}}
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Editar</a>

                        {{-- Botão de derrubar sessão (aparece apenas se o usuário estiver online) --}}
                        @if($user->is_online)
                            <form action="{{ route('users.logoutUser', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Tem certeza que deseja derrubar a sessão deste usuário?');">
                                    Derrubar Sessão
                                </button>
                            </form>
                        @endif

                        {{-- Botão de deletar --}}
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar este usuário?');">
                                Deletar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Adiciona paginação, caso exista --}}
@if(method_exists($users, 'links'))
    <div class="mt-3">
        {{ $users->links() }}
    </div>
@endif
@endsection

@extends('day.layout')

@section('title', 'Lista de Usuários')

@section('content')
<div class="container mt-5">
    <!-- Título da página -->
    <h1 class="fw-bold text-primary mb-4">Lista de Usuários</h1>

    {{-- Exibe mensagens de sucesso ou erro --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center p-3 mb-4">
            <i class="ki-duotone ki-check fs-4 text-success me-3"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center p-3 mb-4">
            <i class="ki-duotone ki-warning fs-4 text-danger me-3"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Botão para adicionar novo usuário --}}
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="ki-duotone ki-plus me-2"></i>Novo Usuário
        </a>
    </div>

    {{-- Tabela de usuários estilizada --}}
    <div class="card card-flush">
        <div class="card-body pt-0">
            <table class="table align-middle table-row-dashed fs-6 gy-4" id="userTable">
                <thead class="table-light text-start fw-bold">
                    <tr>
                        <th>Avatar</th>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Cargo</th>
                        <th>Estado</th>
                        <th>IP</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                @if ($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Avatar" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <span class="text-muted">Sem Avatar</span>
                                @endif
                            </td>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->cargo }}</td>
                            <td>
                                <span class="badge {{ $user->is_online ? 'badge-light-success' : 'badge-light-danger' }}">
                                    {{ $user->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </td>
                            <td>{{ $user->ip_address ?? 'N/A' }}</td>
                            <td class="text-end">
                                {{-- Botão Editar --}}
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning me-2">
                                    <i class="ki-duotone ki-pencil me-1"></i>Editar
                                </a>

                                {{-- Botão Logout (caso online) --}}
                                @if($user->is_online)
                                    <form action="{{ route('users.logoutUser', $user->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-secondary me-2" onclick="return confirm('Tem certeza que deseja derrubar a sessão deste usuário?');">
                                            <i class="ki-duotone ki-logout me-1"></i>Logout
                                        </button>
                                    </form>
                                @endif

                                {{-- Botão Deletar --}}
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja deletar este usuário?');">
                                        <i class="ki-duotone ki-trash me-1"></i>Deletar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    @if(method_exists($users, 'links'))
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- Scripts para DataTables --}}
@section('scripts')
<script src="/assets/plugins/custom/datatables/datatables.bundle.js"></script>
<script>
    $(document).ready(function() {
        $('#userTable').DataTable({
            responsive: true,
            language: {
                url: '/assets/plugins/custom/datatables/i18n/pt-BR.json'
            }
        });
    });
</script>
@endsection
@endsection

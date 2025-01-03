@extends('layouts.app')

@section('title', 'Gerenciar Permissões')

@section('content')
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2 class="text-center">Gerenciar Permissões</h2>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form id="permissions-form" action="{{ route('permissions.update') }}" method="POST">
                @csrf

                @foreach ($modules as $module => $routes)
                    <div class="card mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h4 class="mb-0">{{ $module }}</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center" style="width: 25%;">Rota</th>
                                        <th class="text-center" style="width: 35%;">Descrição</th>
                                        @foreach ($cargos as $cargo)
                                            <th class="text-center" style="width: {{ 40 / count($cargos) }}%;">
                                                {{ ucfirst($cargo) }}
                                                <input type="checkbox" class="select-column" data-cargo="{{ $cargo }}">
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($routes as $route => $description)
                                        <tr>
                                            <td class="align-middle text-center">{{ $route }}</td>
                                            <td class="align-middle text-center">{{ $description }}</td>
                                            @foreach ($cargos as $cargo)
                                                <td class="align-middle text-center">
                                                    <input 
                                                        type="checkbox" 
                                                        name="permissions[{{ $cargo }}][{{ $route }}]" 
                                                        value="1" 
                                                        class="form-check-input permission-checkbox permissions-{{ $cargo }}"
                                                        @if (isset($permissions[$cargo]) && 
                                                            ($permissions[$cargo]->firstWhere('page', $route)?->allowed ?? 0) == 1)
                                                            checked
                                                        @endif
                                                    >
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-lg">Salvar Permissões</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    .table {
        margin-bottom: 0;
        border-radius: 8px;
        overflow: hidden;
    }

    .permission-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .permission-checkbox:checked {
        accent-color: #28a745; /* Verde */
    }

    .permission-checkbox:not(:checked) {
        accent-color: #dc3545; /* Vermelho */
    }

    .select-column {
        transform: scale(1.5);
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Selecionar ou desmarcar todas as checkboxes de uma coluna
        document.querySelectorAll('.select-column').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const cargo = this.dataset.cargo;
                const checkboxes = document.querySelectorAll(`.permissions-${cargo}`);
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
            });
        });

        // Certifica que as colunas "Selecionar Todos" refletem o estado atual das checkboxes
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const cargo = this.classList[1].split('-')[1];
                const columnCheckboxes = document.querySelectorAll(`.permissions-${cargo}`);
                const columnSelectAll = document.querySelector(`.select-column[data-cargo="${cargo}"]`);
                columnSelectAll.checked = Array.from(columnCheckboxes).every(cb => cb.checked);
            });
        });

        // Inicializa o estado dos checkboxes "Selecionar Todos"
        document.querySelectorAll('.select-column').forEach(selectAllCheckbox => {
            const cargo = selectAllCheckbox.dataset.cargo;
            const checkboxes = document.querySelectorAll(`.permissions-${cargo}`);
            selectAllCheckbox.checked = Array.from(checkboxes).every(cb => cb.checked);
        });
    });
</script>
@endpush

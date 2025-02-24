@extends('day.layout')

@section('content')
    <div class="container">
        <h1>Rotas de Discagem</h1>
        
        <!-- Botão para criar nova rota -->
        <a href="{{ route('rotas.create') }}" class="btn btn-primary mb-3">Criar Nova Rota</a>

        <!-- Mensagem de status -->
        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Lista de rotas -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Contexto</th>
                    <th>Discagem</th>
                    <th>Destino</th>
                    <th>Tipo de Discagem</th>
                    <th>Empresa</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rotas as $rota)
                    <tr>
                        <td>{{ $rota->context }}</td>
                        <td>{{ $rota->exten }}</td>
                        <td>{{ $rota->appdata }}</td>
                        <td>{{ $rota->tipo_discagem }}</td>
                        <td>{{ Auth::user()->empresa->id }} - {{ Auth::user()->empresa->nome }}</td>
                        <td>
                            <!-- Botão de editar -->
                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $rota->id }}">Editar</button>

                            <!-- Formulário de deletar -->
                            <form action="{{ route('rotas.destroy', $rota->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta rota?')">Excluir</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal de edição -->
                    <div class="modal fade" id="editModal{{ $rota->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $rota->id }}" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel{{ $rota->id }}">Editar Rota</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="{{ route('rotas.update', $rota->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="contexto_discagem_{{ $rota->id }}">Contexto de Discagem</label>
                                            <input type="text" class="form-control" id="contexto_discagem_{{ $rota->id }}" name="contexto_discagem" value="{{ old('contexto_discagem', $rota->context) }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="discagem_{{ $rota->id }}">Discagem</label>
                                            <input type="text" class="form-control" id="discagem_{{ $rota->id }}" name="discagem" value="{{ old('discagem', $rota->exten) }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="destino_{{ $rota->id }}">Destino</label>
                                            <input type="text" class="form-control" id="destino_{{ $rota->id }}" name="destino" value="{{ old('destino', $rota->appdata) }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="tipo_discagem_{{ $rota->id }}">Tipo de Discagem</label>
                                            <select class="form-control" id="tipo_discagem_{{ $rota->id }}" name="tipo_discagem" required>
                                                <option value="ramal" {{ old('tipo_discagem', $rota->tipo_discagem) == 'ramal' ? 'selected' : '' }}>Ramal</option>
                                                <option value="fila" {{ old('tipo_discagem', $rota->tipo_discagem) == 'fila' ? 'selected' : '' }}>Fila</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Atualizar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Scripts necessários para o modal -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection

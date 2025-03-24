@extends('day.layout')

@section('title', 'Criar Ticket')

@section('content')



<div class="container mt-5 d-flex justify-content-center align-items-center vh-100">

    <div class="card shadow-lg p-4" style="width: 500px; border-radius: 12px;">
        
        {{-- Título --}}
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary"><i class="fas fa-ticket-alt"></i> Criar Novo Ticket</h3>
        </div>

        {{-- Exibir mensagens de sucesso ou erro --}}
        @if(session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
        @endif

        {{-- Formulário --}}
        <form action="{{ route('glpi.createTicket') }}" method="POST">
            @csrf

            {{-- Título --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Título do Chamado:</label>
                <input type="text" name="title" class="form-control rounded-3" required>
            </div>

            {{-- Descrição --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Descrição:</label>
                <textarea name="description" class="form-control rounded-3" rows="3" required></textarea>
            </div>

            {{-- Categoria --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Categoria:</label>
                <select name="category_id" class="form-select rounded-3" required>
                    <option value="">Selecione uma categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category['id'] }}">{{ $category['completename'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status do Chamado:</label>
                    <div class="d-flex gap-3">
                        <input type="radio" class="btn-check" name="status" id="status_pendente" value="pendente" required>
                        <label class="btn btn-outline-warning fw-bold" for="status_pendente">
                            <i class="fas fa-exclamation-circle"></i> Pendente
                        </label>

                        <input type="radio" class="btn-check" name="status" id="status_solucionado" value="solucionado">
                        <label class="btn btn-outline-success fw-bold" for="status_solucionado">
                            <i class="fas fa-check-circle"></i> Solucionado
                        </label>
                    </div>
                </div>

                {{-- Origem do Chamado --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Origem do Chamado:</label>
                    <div class="d-flex gap-3">
                        <input type="radio" class="btn-check" name="requesttypes_id" id="origem_telefone" value="9" required>
                        <label class="btn btn-outline-primary fw-bold" for="origem_telefone">
                            <i class="fas fa-phone"></i> Telefone
                        </label>

                        <input type="radio" class="btn-check" name="requesttypes_id" id="origem_chat" value="10">
                        <label class="btn btn-outline-info fw-bold" for="origem_chat">
                            <i class="fas fa-comments"></i> Chat
                        </label>
                    </div>
                </div>




            {{-- Usuário --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Usuário Responsável:</label>
                <select name="user_id" class="form-select rounded-3" required>
                    <option value="">Selecione um usuário</option>
                    @foreach($users as $user)
                        <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Entidade --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Entidade:</label>
                <select name="entities_id" class="form-select rounded-3" required>
                    <option value="">Selecione uma Entidade</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity['id'] }}">{{ $entity['name'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Botão de Envio --}}
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="fas fa-paper-plane"></i> Criar Ticket
                </button>
            </div>
        </form>

    </div>
</div>



@endsection

@extends('day.layout')

@section('content')
<div class="container mt-5">
    <h1>Configurar Campanha de URA Ativa</h1>

    <!-- Mensagem de sucesso -->
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Mensagem de erro geral -->
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <!-- Mensagens de erro de validação -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Ocorreram os seguintes erros:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Mensagens sobre números inválidos -->
    @if (session('invalid_numbers'))
        <div class="alert alert-warning">
            <strong>Números inválidos encontrados:</strong>
            <ul>
                @foreach (session('invalid_numbers') as $invalid)
                    <li>{{ $invalid['phone_number'] }} - {{ $invalid['reason'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulário de criação de campanha -->
    <form action="{{ route('campaign.store') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Campanha</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

                    <div class="mb-3">
                <label for="batch_size" class="form-label">Quantidade de Chamadas Simultâneas (1 a 100)</label>
                <input type="number" 
                    name="batch_size" 
                    class="form-control @error('batch_size') is-invalid @enderror" 
                    id="batch_size" 
                    value="{{ old('batch_size', 5) }}" 
                    required 
                    min="1" 
                    max="100">
                @error('batch_size')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

        <div class="mb-3">
            <label for="context" class="form-label">Contexto</label>
            <input type="text" name="context" class="form-control @error('context') is-invalid @enderror" id="context" value="{{ old('context', 'default') }}" required>
            @error('context')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="extension" class="form-label">Extensão</label>
            <input type="text" name="extension" class="form-control @error('extension') is-invalid @enderror" id="extension" value="{{ old('extension', 's') }}" required>
            @error('extension')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">Prioridade</label>
            <input type="number" name="priority" class="form-control @error('priority') is-invalid @enderror" id="priority" value="{{ old('priority', 1) }}" required>
            @error('priority')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">Data e Hora de Início</label>
            <input type="datetime-local" name="start_date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" value="{{ old('start_date') }}" required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">Data e Hora de Fim</label>
            <input type="datetime-local" name="end_date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" value="{{ old('end_date') }}" required>
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="numbers_file" class="form-label">Arquivo de Números (CSV ou TXT) - Tamanho Maximo: 2 Mb</label>
            <input type="file" name="numbers_file" class="form-control @error('numbers_file') is-invalid @enderror" id="numbers_file" required>
            @error('numbers_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="audio_file" class="form-label">Arquivo de Áudio (MP3 ou WAV) - Tamanho Maximo: 2 Mb</label>
            <input type="file" name="audio_file" class="form-control @error('audio_file') is-invalid @enderror" id="audio_file" required>
            @error('audio_file')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Iniciar Campanha</button>
    </form>
</div>

<script>
    // Desabilitar o botão de envio após o clique
    document.querySelector('form').addEventListener('submit', function () {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Enviando...';
    });
</script>
@endsection

@extends('day.layout')

@section('content')
<div class="container mt-5">
    <h1>Configurar Campanha de URA Ativa</h1>

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

    <form action="{{ route('campaign.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Campanha</label>
            <input type="text" name="name" class="form-control" id="name" required>
        </div>

        <div class="mb-3">
            <label for="context" class="form-label">Contexto</label>
            <input type="text" name="context" class="form-control" id="context" required value="default">
        </div>

        <div class="mb-3">
            <label for="extension" class="form-label">Extensão</label>
            <input type="text" name="extension" class="form-control" id="extension" required value="s">
        </div>

        <div class="mb-3">
            <label for="priority" class="form-label">Prioridade</label>
            <input type="number" name="priority" class="form-control" id="priority" required value="1">
        </div>

        <div class="mb-3">
    <label for="start_date" class="form-label">Data e Hora de Início</label>
    <input type="datetime-local" name="start_date" class="form-control" id="start_date" required>
            </div>

            <div class="mb-3">
                <label for="end_date" class="form-label">Data e Hora de Fim</label>
                <input type="datetime-local" name="end_date" class="form-control" id="end_date" required>
            </div>


        <div class="mb-3">
            <label for="numbers_file" class="form-label">Arquivo de Números (CSV ou TXT)</label>
            <input type="file" name="numbers_file" class="form-control" id="numbers_file" required>
        </div>

        <div class="mb-3">
            <label for="audio_file" class="form-label">Arquivo de Áudio (MP3 ou WAV)</label>
            <input type="file" name="audio_file" class="form-control" id="audio_file" required>
        </div>

        <button type="submit" class="btn btn-primary">Iniciar Campanha</button>
    </form>
</div>
@endsection

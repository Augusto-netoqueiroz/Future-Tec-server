@extends('day.layout')

@section('content')
    <div class="container">
        <h2>Mensagens do Discord</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Protocolo</th>
                    <th>Cliente</th>
                    <th>CPF</th>
                    <th>Quem Ligou</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Status</th>
                    <th>ATT</th>
                    <th>Telefone</th>
                    <th>Endereço</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($messages as $message)
                    <tr>
                        <td>{{ $message->empresa }}</td>
                        <td>{{ $message->protocolo }}</td>
                        <td>{{ $message->cliente }}</td>
                        <td>{{ $message->cpf }}</td>
                        <td>{{ $message->quem_ligou }}</td>
                        <td>{{ $message->descricao }}</td>
                        <td>{{ $message->categoria }}</td>
                        <td>{{ $message->status }}</td>
                        <td>{{ $message->att }}</td>
                        <td>{{ $message->telefone }}</td>
                        <td>{{ $message->endereco }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

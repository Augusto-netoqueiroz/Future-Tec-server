<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - WPPConnect</title>
</head>
<body>
    <h1>Selecione um Chat ou Grupo</h1>
    @foreach($chats as $chat)
        <div>
            <h3>{{ $chat['name'] }}</h3>
            <p>{{ $chat['status'] }}</p>
        </div>
    @endforeach
</body>
</html>

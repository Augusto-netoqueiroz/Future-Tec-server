<?php
// Configurações AMI
$host = "127.0.0.1";
$port = 5038;
$username = "admin";
$password = "MKsx2377!@";
$context = "rotapentagono";
$priority = 1;
$logFile = "/var/log/asterisk/php_ami.log";

// Função para log
function logMessage($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    $log = "[$timestamp] $message" . PHP_EOL;
    echo $log;
    file_put_contents($logFile, $log, FILE_APPEND);
}

// Conectar ao AMI
function connectAMI() {
    global $host, $port, $username, $password;
    $socket = fsockopen($host, $port, $errno, $errstr, 10);
    if (!$socket) {
        logMessage("ERRO: Falha na conexão ao AMI - $errstr ($errno)");
        return false;
    }
    logMessage("Conectado ao Asterisk AMI.");

    // Login
    fputs($socket, "Action: Login\r\nUsername: $username\r\nSecret: $password\r\nEvents: off\r\n\r\n");
    $response = "";
    while (!feof($socket)) {
        $line = fgets($socket, 4096);
        $response .= $line;
        if (strpos($line, "Message: Authentication accepted") !== false) {
            logMessage("Login bem-sucedido.");
            return $socket;
        }
    }
    logMessage("ERRO: Falha ao autenticar.");
    fclose($socket);
    return false;
}

// Fazer chamada
function originateCall($socket, $callee) {
    global $context, $priority;
    logMessage("Iniciando chamada para $callee...");

    $originate = "Action: Originate\r\n" .
                 "Channel: SIP/Pentagono/$callee\r\n" .
                 "Exten: $callee\r\n" .
                 "Context: $context\r\n" .
                 "Priority: $priority\r\n" .
                 "Callerid: $callee\r\n" .
                 "Timeout: 30000\r\n" .
                 "Async: yes\r\n\r\n";

    fputs($socket, $originate);

    // Ler resposta
    $response = "";
    while (!feof($socket)) {
        $line = fgets($socket, 4096);
        $response .= $line;
        if (strpos($line, "Response: Success") !== false || strpos($line, "Response: Error") !== false) {
            break;
        }
    }

    if (strpos($response, "Response: Success") !== false) {
        logMessage("Chamada para $callee iniciada com sucesso.");
        return true;
    } else {
        logMessage("ERRO: Falha ao iniciar chamada para $callee.");
        return false;
    }
}

// Buscar números no banco de dados
$pdo = new PDO("mysql:host=localhost;dbname=asterisk", "asterisk", "MKsx2377!@");
$query = $pdo->query("SELECT phone_number FROM campaign_contacts WHERE status = 'pending' LIMIT 10");
$contacts = $query->fetchAll(PDO::FETCH_ASSOC);

if (count($contacts) === 0) {
    logMessage("Nenhum número pendente encontrado.");
    exit;
}

// Conectar ao AMI
$socket = connectAMI();
if (!$socket) exit;

// Iniciar chamadas para todos os números
foreach ($contacts as $contact) {
    $success = originateCall($socket, $contact['phone_number']);
    if ($success) {
        // Atualizar status no banco de dados
        $stmt = $pdo->prepare("UPDATE campaign_contacts SET status = 'called' WHERE phone_number = ?");
        $stmt->execute([$contact['phone_number']]);
    }
}

// Logout e fechar conexão
logMessage("Finalizando conexões.");
fputs($socket, "Action: Logoff\r\n\r\n");
fclose($socket);
?>

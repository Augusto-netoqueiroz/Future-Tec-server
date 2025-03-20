<?php
require_once "vendor/autoload.php";

use magnusbilling\api\magnusBilling;

$magnusBilling = new MagnusBilling('X9vT7KqPz3LmA2WdB5YfN8JcR4GhV6Xs', '3fYpLzA9KdV7XqW2N6JmG5TbX8R4ChtP');

// Definindo a URL do MagnusBilling
$magnusBilling->public_url = "http://painel.liguetalk.com.br/mbilling"; 

$magnusBilling->setFilter('active', '1', 'eq', 'numeric');

// Inicializando a variável de página
$page = 1;
$resultsPerPage = 25; // Cada página contém 25 resultados
$allUsers = [];
$totalRecords = 0;

// Nome do arquivo de log
$logFile = 'users_log.txt';

// Abrir o arquivo de log para escrita (ou criar se não existir)
$log = fopen($logFile, 'a'); 

// Verificar se o arquivo foi aberto corretamente
if (!$log) {
    die("Não foi possível abrir o arquivo de log.");
}

do {
    // Executar a requisição com o número da página atual
    $result = $magnusBilling->read('user', $page);

    if (isset($result['rows']) && !empty($result['rows'])) {
        // Agregar os resultados na lista
        $allUsers = array_merge($allUsers, $result['rows']);
        $totalRecords = $result['count'] ?? 0; // Total de registros disponíveis
    }

    // Verifica se há mais páginas para buscar
    $page++;

} while (count($allUsers) < $totalRecords);

// Escrever a quantidade de usuários encontrados no arquivo de log
fwrite($log, "Total de usuários encontrados: " . $totalRecords . "\n");

// Iterar sobre todos os usuários encontrados e registrar no log
foreach ($allUsers as $index => $user) {
    // Verificar se o 'Plan ID' é "Ilimitado Clientes" e pular este usuário
    if (isset($user['idPlanname']) && $user['idPlanname'] == "Ilimitado Clientes") {
        continue; // Ignora o usuário e passa para o próximo
    }

    fwrite($log, "Resultado " . ($index + 1) . ":\n");
    fwrite($log, "Username: " . ($user['username'] ?? 'N/A') . "\n");
    fwrite($log, "Credit: " . ($user['credit'] ?? 'N/A') . "\n");
    fwrite($log, "Credit Notification: " . ($user['credit_notification'] ?? 'N/A') . "\n");
    fwrite($log, "Plan ID: " . ($user['idPlanname'] ?? 'N/A') . "\n");
    fwrite($log, "Email: " . ($user['email'] ?? 'N/A') . "\n");
    fwrite($log, "Phone: " . ($user['mobile'] ?? 'N/A') . "\n");
    fwrite($log, "Notificação: " . ($user['credit_notification_daily'] ?? 'N/A') . "\n");
    fwrite($log, "-----------------------------\n");
}

// Fechar o arquivo de log após terminar
fclose($log);

echo "Dados registrados no arquivo de log com sucesso!\n";

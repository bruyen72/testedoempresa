<?php
/**
 * Webhook Deploy - UOL Host Windows Otimizado
 * Compatível com PHP 7.4+ e IIS
 */

// Configurações de erro e timeout
ini_set('max_execution_time', 30);
ini_set('memory_limit', '128M');
error_reporting(E_ALL & ~E_NOTICE);

// Headers essenciais
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: https://github.com');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Hub-Signature, X-Hub-Signature-256');

// Configurações do webhook
$config = array(
    'secret' => '', // Deixe vazio por enquanto
    'branch' => 'main', // ou 'master'
    'repo_path' => __DIR__,
    'log_file' => 'webhook.log',
    'allowed_ips' => array(
        // IPs do GitHub (opcional)
        '140.82.112.0/20',
        '185.199.108.0/22',
        '192.30.252.0/22'
    )
);

/**
 * Função de log segura
 */
function writeLog($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    // Lock do arquivo para evitar conflitos
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Valida assinatura GitHub (se secret estiver definido)
 */
function validateSignature($payload, $signature, $secret) {
    if (empty($secret)) return true; // Skip se não tem secret
    
    $expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    return hash_equals($expected, $signature);
}

/**
 * Executa deploy
 */
function executeDeploy($repoPath) {
    $commands = array(
        "cd /d \"$repoPath\"",
        "git fetch origin",
        "git reset --hard origin/main", // ou origin/master
        "git clean -fd"
    );
    
    $output = array();
    $success = true;
    
    foreach ($commands as $cmd) {
        $result = array();
        $returnCode = 0;
        
        exec($cmd . " 2>&1", $result, $returnCode);
        
        $output[] = array(
            'command' => $cmd,
            'output' => $result,
            'return_code' => $returnCode
        );
        
        if ($returnCode !== 0) {
            $success = false;
        }
    }
    
    return array('success' => $success, 'output' => $output);
}

// Inicia processamento
writeLog("=== Webhook iniciado ===", $config['log_file']);
writeLog("Method: " . $_SERVER['REQUEST_METHOD'], $config['log_file']);
writeLog("User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), $config['log_file']);
writeLog("IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown'), $config['log_file']);

try {
    // Verifica método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = array(
            'status' => 'ready',
            'message' => 'Webhook endpoint ativo',
            'php_version' => phpversion(),
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'timestamp' => date('c')
        );
        
        writeLog("GET request - Status check", $config['log_file']);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Recebe payload
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    
    writeLog("Payload size: " . strlen($payload) . " bytes", $config['log_file']);
    
    // Valida assinatura (se configurada)
    if (!validateSignature($payload, $signature, $config['secret'])) {
        http_response_code(401);
        echo json_encode(array('error' => 'Invalid signature'));
        writeLog("ERRO: Assinatura inválida", $config['log_file']);
        exit;
    }
    
    // Decodifica JSON
    $data = json_decode($payload, true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid JSON'));
        writeLog("ERRO: JSON inválido", $config['log_file']);
        exit;
    }
    
    // Verifica se é push para branch correta
    $ref = $data['ref'] ?? '';
    $expectedRef = "refs/heads/" . $config['branch'];
    
    writeLog("Branch recebida: $ref", $config['log_file']);
    writeLog("Branch esperada: $expectedRef", $config['log_file']);
    
    if ($ref !== $expectedRef) {
        $response = array(
            'status' => 'ignored',
            'message' => "Branch $ref ignorada",
            'expected' => $expectedRef
        );
        
        writeLog("Branch ignorada: $ref", $config['log_file']);
        echo json_encode($response);
        exit;
    }
    
    // Executa deploy
    writeLog("=== Iniciando deploy ===", $config['log_file']);
    
    $deployResult = executeDeploy($config['repo_path']);
    
    if ($deployResult['success']) {
        $response = array(
            'status' => 'success',
            'message' => 'Deploy executado com sucesso',
            'timestamp' => date('c'),
            'details' => $deployResult['output']
        );
        
        writeLog("Deploy SUCCESS", $config['log_file']);
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Erro durante deploy',
            'timestamp' => date('c'),
            'details' => $deployResult['output']
        );
        
        writeLog("Deploy ERROR", $config['log_file']);
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    
    $errorResponse = array(
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    );
    
    writeLog("EXCEPTION: " . $e->getMessage(), $config['log_file']);
    echo json_encode($errorResponse);
}

writeLog("=== Webhook finalizado ===", $config['log_file']);
?>

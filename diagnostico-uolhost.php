<?php
// diagnostico-uolhost.php - Diagnóstico completo UOL Host
header('Content-Type: text/plain; charset=utf-8');

echo "=== DIAGNÓSTICO UOL HOST WINDOWS ===\n\n";

// Informações básicas do sistema
echo "1. INFORMAÇÕES DO SISTEMA:\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Current User: " . get_current_user() . "\n";
echo "Current Directory: " . getcwd() . "\n";

// Teste de permissões
echo "\n2. TESTE DE PERMISSÕES:\n";
$testDir = '.';
$testFile = 'test_permissions.txt';

echo "Diretório atual legível: " . (is_readable($testDir) ? 'SIM' : 'NÃO') . "\n";
echo "Diretório atual gravável: " . (is_writable($testDir) ? 'SIM' : 'NÃO') . "\n";

// Teste de criação de arquivo
if (file_put_contents($testFile, 'Teste de permissão: ' . date('Y-m-d H:i:s'))) {
    echo "Criação de arquivo: SUCESSO\n";
    echo "Arquivo criado legível: " . (is_readable($testFile) ? 'SIM' : 'NÃO') . "\n";
    unlink($testFile);
    echo "Exclusão de arquivo: SUCESSO\n";
} else {
    echo "Criação de arquivo: FALHOU\n";
}

// Funções disponíveis
echo "\n3. FUNÇÕES CRÍTICAS:\n";
$functions = [
    'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 
    'exec', 'shell_exec', 'system', 'passthru',
    'json_decode', 'json_encode', 'curl_init'
];

foreach ($functions as $func) {
    echo "$func: " . (function_exists($func) ? 'DISPONÍVEL' : 'INDISPONÍVEL') . "\n";
}

// Configurações PHP importantes
echo "\n4. CONFIGURAÇÕES PHP:\n";
$configs = [
    'allow_url_fopen', 'allow_url_include', 'file_uploads',
    'enable_dl', 'safe_mode', 'open_basedir'
];

foreach ($configs as $config) {
    $value = ini_get($config);
    echo "$config: " . ($value ? $value : 'OFF/NULL') . "\n";
}

// Teste de headers HTTP
echo "\n5. HEADERS HTTP:\n";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        echo "$key: $value\n";
    }
}

// Informações do usuário IIS
echo "\n6. INFORMAÇÕES IIS:\n";
echo "AUTH_TYPE: " . ($_SERVER['AUTH_TYPE'] ?? 'N/A') . "\n";
echo "REMOTE_USER: " . ($_SERVER['REMOTE_USER'] ?? 'N/A') . "\n";
echo "LOGON_USER: " . ($_SERVER['LOGON_USER'] ?? 'N/A') . "\n";

// Teste de extensões
echo "\n7. EXTENSÕES PHP:\n";
$extensions = ['curl', 'openssl', 'json', 'mbstring', 'zip'];
foreach ($extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'CARREGADA' : 'NÃO CARREGADA') . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?>

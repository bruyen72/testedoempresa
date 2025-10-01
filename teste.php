<?php
// index_test.php - TESTE FORÇADO UOL HOST
echo "<!DOCTYPE html><html><head><title>TecPoint TESTE</title></head><body>";
echo "<h1>🎯 SITE TECPOINT FUNCIONANDO!</h1>";
echo "<p>PHP: " . phpversion() . "</p>";
echo "<p>Servidor: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
echo "<p>Data: " . date('d/m/Y H:i:s') . "</p>";
echo "<p>Arquivo: " . __FILE__ . "</p>";
echo "<p>URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<hr>";
echo "<h2>TESTES:</h2>";
echo "<p><a href='/teste.php'>Teste PHP</a></p>";
echo "<p><a href='/diagnostico-uolhost.php'>Diagnóstico</a></p>";
echo "</body></html>";
?>

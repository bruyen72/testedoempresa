<?php
echo "<h1>🔍 Teste SQLite - TecPoint</h1>";

// Verificar extensões
if (extension_loaded('pdo_sqlite')) {
    echo "✅ PDO SQLite: <strong>DISPONÍVEL</strong><br>";
} else {
    echo "❌ PDO SQLite: <strong>NÃO DISPONÍVEL</strong><br>";
}

// Testar banco existente
$db_paths = [
    __DIR__ . '/data/database.sqlite',
    __DIR__ . '/data/tecpoint.db',
    __DIR__ . '/database.sqlite',
    __DIR__ . '/local.db'
];

echo "<h2>Procurando banco de dados...</h2>";

foreach ($db_paths as $path) {
    echo "📁 Testando: <code>" . basename($path) . "</code> ";
    
    if (file_exists($path)) {
        echo "✅ <strong>ENCONTRADO!</strong><br>";
        
        try {
            $pdo = new PDO("sqlite:$path");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Verificar tabelas
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "📊 Tabelas encontradas: " . implode(', ', $tables) . "<br>";
            
            // Testar dados
            if (in_array('products', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM products");
                $count = $stmt->fetchColumn();
                echo "📦 Produtos: $count<br>";
            }
            
            if (in_array('admin', $tables)) {
                $stmt = $pdo->query("SELECT COUNT(*) FROM admin");
                $count = $stmt->fetchColumn();
                echo "👤 Admins: $count<br>";
            }
            
            echo "<h3>🎉 Banco funcionando perfeitamente!</h3>";
            break;
            
        } catch (PDOException $e) {
            echo "❌ ERRO: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "❌ Não encontrado<br>";
    }
}

echo "<hr><p><a href='index.php'>← Testar site principal</a></p>";
?>
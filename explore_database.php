<?php
require_once 'config/config.php';

try {
    $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "🔍 Explorando todas las tablas en la base de datos:\n\n";
    
    // Obtener todas las tablas
    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
    $tables = $stmt->fetchAll();
    
    foreach ($tables as $table) {
        $tableName = $table['table_name'];
        echo "📋 Tabla: $tableName\n";
        
        // Obtener columnas
        $stmt = $pdo->prepare("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = ? ORDER BY ordinal_position");
        $stmt->execute([$tableName]);
        $columns = $stmt->fetchAll();
        
        foreach ($columns as $col) {
            echo "  - {$col['column_name']} ({$col['data_type']})\n";
        }
        
        // Obtener conteo de registros
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM \"$tableName\"");
            $result = $stmt->fetch();
            echo "  📊 Registros: " . $result['count'] . "\n";
        } catch (Exception $e) {
            echo "  📊 Error al contar registros\n";
        }
        
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
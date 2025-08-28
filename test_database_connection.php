<?php
// Test de conexión a la base de datos real
require_once 'config/config.php';

echo "🔍 Verificando conexión a PostgreSQL...\n";

try {
    $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_DATABASE'];
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✅ Conexión a PostgreSQL exitosa\n";
    echo "📊 Base de datos: " . $_ENV['DB_DATABASE'] . "\n";
    echo "🖥️ Host: " . $_ENV['DB_HOST'] . ":" . $_ENV['DB_PORT'] . "\n";
    
    // Verificar tablas relacionadas con pacientes
    $tables = ['pacientes', 'personas', 'consultas'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "📋 Tabla '$table': " . $result['count'] . " registros\n";
        } catch (PDOException $e) {
            echo "⚠️ Tabla '$table': No existe o error (" . $e->getMessage() . ")\n";
        }
    }
    
    // Verificar estructura de la tabla de pacientes
    echo "\n🔍 Verificando estructura de tablas:\n";
    
    $queries = [
        "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'pacientes' ORDER BY ordinal_position",
        "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'personas' ORDER BY ordinal_position"
    ];
    
    foreach ($queries as $query) {
        try {
            $stmt = $pdo->query($query);
            $columns = $stmt->fetchAll();
            $tableName = strpos($query, 'pacientes') !== false ? 'pacientes' : 'personas';
            
            if (!empty($columns)) {
                echo "\n📋 Columnas de la tabla '$tableName':\n";
                foreach ($columns as $col) {
                    echo "  - {$col['column_name']} ({$col['data_type']})\n";
                }
            }
        } catch (PDOException $e) {
            // Tabla no existe, continuar
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
    echo "🔧 Verifique la configuración en .env:\n";
    echo "   DB_HOST=" . $_ENV['DB_HOST'] . "\n";
    echo "   DB_PORT=" . $_ENV['DB_PORT'] . "\n";
    echo "   DB_DATABASE=" . $_ENV['DB_DATABASE'] . "\n";
    echo "   DB_USERNAME=" . $_ENV['DB_USERNAME'] . "\n";
}

echo "\n🎯 El sistema ahora está configurado para usar datos reales de PostgreSQL\n";
?>
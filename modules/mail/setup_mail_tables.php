<?php
/**
 * Script para crear las tablas necesarias para el módulo de correo
 */

echo "=== Setup de Tablas para Módulo de Correo ===\n";

try {
    $root_path = dirname(dirname(dirname(__FILE__)));
    $config_file = $root_path . '/config/config.php';
    
    echo "Cargando configuración desde: $config_file\n";
    
    if (!file_exists($config_file)) {
        throw new Exception("Archivo config.php no encontrado en: $config_file");
    }
    
    require_once $config_file;

    // Verificar que las variables de entorno están disponibles
    if (!isset($_ENV['DB_HOST'])) {
        throw new Exception('Variables de entorno no están cargadas. Verifique el archivo .env');
    }

    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? 5432;
    $database = $_ENV['DB_DATABASE'] ?? 'clinica';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? 'admin';
    
    echo "Conectando a: $host:$port/$database con usuario: $username\n";
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a base de datos exitosa\n";
    
    // Crear tabla mail_config si no existe
    $sql_mail_config = "
        CREATE TABLE IF NOT EXISTS mail_config (
            id SERIAL PRIMARY KEY,
            smtp_host VARCHAR(255) NOT NULL,
            smtp_port INTEGER NOT NULL DEFAULT 587,
            smtp_secure VARCHAR(10) NULL,
            smtp_auth BOOLEAN NOT NULL DEFAULT TRUE,
            smtp_username VARCHAR(255) NOT NULL,
            smtp_password VARCHAR(255) NOT NULL,
            from_email VARCHAR(255) NOT NULL,
            from_name VARCHAR(255) NOT NULL,
            reply_to_email VARCHAR(255) NULL,
            reply_to_name VARCHAR(255) NULL,
            is_active BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    
    $pdo->exec($sql_mail_config);
    echo "✅ Tabla mail_config creada/verificada\n";
    
    // Crear tabla mail_logs si no existe
    $sql_mail_logs = "
        CREATE TABLE IF NOT EXISTS mail_logs (
            id SERIAL PRIMARY KEY,
            consulta_id INTEGER NULL,
            recipient_email VARCHAR(255) NOT NULL,
            recipient_name VARCHAR(255) NULL,
            subject VARCHAR(500) NULL,
            status VARCHAR(50) NOT NULL DEFAULT 'pending',
            error_message TEXT NULL,
            pdf_filename VARCHAR(255) NULL,
            user_id INTEGER NULL,
            attempts INTEGER NOT NULL DEFAULT 1,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";
    
    $pdo->exec($sql_mail_logs);
    echo "✅ Tabla mail_logs creada/verificada\n";
    
    // Crear índices para mejorar rendimiento
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_mail_config_active ON mail_config(is_active);",
        "CREATE INDEX IF NOT EXISTS idx_mail_logs_consulta ON mail_logs(consulta_id);",
        "CREATE INDEX IF NOT EXISTS idx_mail_logs_status ON mail_logs(status);",
        "CREATE INDEX IF NOT EXISTS idx_mail_logs_sent_at ON mail_logs(sent_at);"
    ];
    
    foreach ($indexes as $index_sql) {
        $pdo->exec($index_sql);
    }
    
    echo "✅ Índices creados/verificados\n";
    
    // Verificar si existe configuración por defecto
    $stmt = $pdo->query("SELECT COUNT(*) FROM mail_config");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "ℹ️  No hay configuraciones de correo. Accede a la página de configuración para crear una.\n";
    } else {
        echo "✅ Configuraciones de correo encontradas: {$count}\n";
    }
    
    echo "\n🎉 ¡Configuración de base de datos para correo completada!\n";
    echo "👉 Ahora puedes acceder a: http://tu-dominio/index.php?ruta=configuracion-correo\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Archivo: " . $e->getFile() . " línea: " . $e->getLine() . "\n";
    
    if (isset($dsn)) {
        echo "🔍 DSN utilizado: $dsn\n";
    }
    
    exit(1);
}
?>
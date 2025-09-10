<?php
/**
 * Script para verificar y crear las tablas necesarias para el sistema de correo
 */

require_once __DIR__ . '/config/config.php';

try {
    // Usar configuración de base de datos del .env
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? 5432;
    $database = $_ENV['DB_DATABASE'] ?? 'clinica';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? 'admin';
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    echo "Conectando a: $dsn con usuario: $username\n";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conexión exitosa a la base de datos\n\n";
    
    // Verificar tabla mail_config
    $checkMailConfig = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'mail_config'
    )";
    
    $result = $pdo->query($checkMailConfig)->fetchColumn();
    
    if (!$result) {
        echo "📧 Creando tabla mail_config...\n";
        
        $createMailConfig = "
        CREATE TABLE mail_config (
            id SERIAL PRIMARY KEY,
            smtp_host VARCHAR(255) NOT NULL,
            smtp_port INTEGER NOT NULL DEFAULT 587,
            smtp_secure VARCHAR(10) DEFAULT NULL,
            smtp_auth BOOLEAN DEFAULT TRUE,
            smtp_username VARCHAR(255) NOT NULL,
            smtp_password VARCHAR(255) NOT NULL,
            from_email VARCHAR(255) NOT NULL,
            from_name VARCHAR(255) NOT NULL,
            reply_to_email VARCHAR(255) DEFAULT NULL,
            reply_to_name VARCHAR(255) DEFAULT NULL,
            is_active BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createMailConfig);
        echo "✅ Tabla mail_config creada exitosamente\n";
    } else {
        echo "✅ Tabla mail_config ya existe\n";
    }
    
    // Verificar tabla mail_logs
    $checkMailLogs = "SELECT EXISTS (
        SELECT FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'mail_logs'
    )";
    
    $result = $pdo->query($checkMailLogs)->fetchColumn();
    
    if (!$result) {
        echo "📝 Creando tabla mail_logs...\n";
        
        $createMailLogs = "
        CREATE TABLE mail_logs (
            id SERIAL PRIMARY KEY,
            consulta_id INTEGER,
            recipient_email VARCHAR(255) NOT NULL,
            recipient_name VARCHAR(255),
            subject VARCHAR(255) NOT NULL,
            body TEXT,
            status VARCHAR(20) DEFAULT 'pending',
            error_message TEXT,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            attempts INTEGER DEFAULT 0
        )";
        
        $pdo->exec($createMailLogs);
        echo "✅ Tabla mail_logs creada exitosamente\n";
    } else {
        echo "✅ Tabla mail_logs ya existe\n";
    }
    
    // Insertar configuración de ejemplo si no existe
    $checkConfig = $pdo->query("SELECT COUNT(*) FROM mail_config")->fetchColumn();
    
    if ($checkConfig == 0) {
        echo "📧 Insertando configuración de ejemplo...\n";
        
        $insertExample = "
        INSERT INTO mail_config (
            smtp_host, smtp_port, smtp_secure, smtp_auth,
            smtp_username, smtp_password, from_email, from_name,
            reply_to_email, reply_to_name, is_active
        ) VALUES (
            'sandbox.smtp.mailtrap.io', 2525, NULL, TRUE,
            'usuario_mailtrap', 'contraseña_mailtrap', 'noreply@clinica.test', 'Sistema Clínica',
            'info@clinica.test', 'Soporte Clínica', FALSE
        )";
        
        $pdo->exec($insertExample);
        echo "✅ Configuración de ejemplo insertada\n";
    } else {
        echo "✅ Ya existe configuración en mail_config\n";
    }
    
    echo "\n🎉 Sistema de correo configurado correctamente\n";
    echo "Puedes acceder a: http://clinica.test/index.php?ruta=configuracion-correo\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Detalles de conexión:\n";
    echo "Host: $host\n";
    echo "Puerto: $port\n";
    echo "Base de datos: $database\n";
    echo "Usuario: $username\n";
}
?>
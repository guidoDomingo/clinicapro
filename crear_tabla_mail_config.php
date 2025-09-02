<?php
/**
 * Script para crear tabla de configuración de correo electrónico
 */

try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=clinica', 'postgres', 'admin');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== CREANDO TABLA DE CONFIGURACIÓN DE CORREO ===\n";
    
    // Crear tabla mail_config
    $sql = "CREATE TABLE IF NOT EXISTS mail_config (
        id SERIAL PRIMARY KEY,
        smtp_host VARCHAR(255) NOT NULL DEFAULT 'sandbox.smtp.mailtrap.io',
        smtp_port INTEGER NOT NULL DEFAULT 2525,
        smtp_secure VARCHAR(10) DEFAULT NULL,
        smtp_auth BOOLEAN NOT NULL DEFAULT TRUE,
        smtp_username VARCHAR(255) NOT NULL DEFAULT '',
        smtp_password VARCHAR(255) NOT NULL DEFAULT '',
        from_email VARCHAR(255) NOT NULL DEFAULT 'noreply@clinica.test',
        from_name VARCHAR(255) NOT NULL DEFAULT 'Sistema Clínica',
        reply_to_email VARCHAR(255) DEFAULT NULL,
        reply_to_name VARCHAR(255) DEFAULT NULL,
        is_active BOOLEAN NOT NULL DEFAULT FALSE,
        created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Tabla 'mail_config' creada exitosamente\n";
    
    // Verificar si ya existe configuración
    $check = $pdo->query("SELECT COUNT(*) FROM mail_config")->fetchColumn();
    
    if ($check == 0) {
        // Insertar configuración por defecto para Mailtrap
        $defaultConfig = "INSERT INTO mail_config (
            smtp_host, smtp_port, smtp_secure, smtp_auth, 
            smtp_username, smtp_password, from_email, from_name,
            is_active
        ) VALUES (
            'sandbox.smtp.mailtrap.io', 2525, NULL, TRUE,
            'tu_usuario_mailtrap', 'tu_password_mailtrap', 
            'noreply@clinica.test', 'Sistema Clínica',
            FALSE
        )";
        
        $pdo->exec($defaultConfig);
        echo "✅ Configuración por defecto creada (Mailtrap)\n";
        echo "⚠️ NOTA: Actualiza las credenciales de Mailtrap en la configuración\n";
    } else {
        echo "ℹ️ Ya existe configuración de correo\n";
    }
    
    // Crear tabla mail_logs para registro de envíos
    $logSql = "CREATE TABLE IF NOT EXISTS mail_logs (
        id SERIAL PRIMARY KEY,
        consulta_id INTEGER REFERENCES consultas(id_consulta),
        recipient_email VARCHAR(255) NOT NULL,
        recipient_name VARCHAR(255),
        subject VARCHAR(500) NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        error_message TEXT,
        sent_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
        pdf_filename VARCHAR(255),
        attempts INTEGER DEFAULT 0,
        user_id INTEGER DEFAULT NULL
    )";
    
    $pdo->exec($logSql);
    echo "✅ Tabla 'mail_logs' creada exitosamente\n";
    
    // Mostrar configuración actual
    echo "\n=== CONFIGURACIÓN ACTUAL ===\n";
    $config = $pdo->query("SELECT * FROM mail_config WHERE is_active = TRUE")->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "Host SMTP: {$config['smtp_host']}\n";
        echo "Puerto: {$config['smtp_port']}\n";
        echo "Usuario: {$config['smtp_username']}\n";
        echo "Email remitente: {$config['from_email']}\n";
        echo "Nombre remitente: {$config['from_name']}\n";
        echo "Estado: " . ($config['is_active'] ? 'ACTIVO' : 'INACTIVO') . "\n";
    } else {
        echo "❌ No hay configuración activa\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
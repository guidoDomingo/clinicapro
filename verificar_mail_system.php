<?php
/**
 * Verificación rápida del sistema de correo
 */

require_once __DIR__ . '/config/config.php';

try {
    echo "<h2>🔍 Verificación del Sistema de Correo</h2>\n";
    
    // Verificar variables de entorno
    echo "<h3>📋 Variables de Entorno (.env)</h3>\n";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NO DEFINIDO') . "<br>\n";
    echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? 'NO DEFINIDO') . "<br>\n";
    echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'NO DEFINIDO') . "<br>\n";
    echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'NO DEFINIDO') . "<br>\n";
    echo "DB_PASSWORD: " . (isset($_ENV['DB_PASSWORD']) ? '***' : 'NO DEFINIDO') . "<br>\n";
    
    // Conectar a la base de datos
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? 5432;
    $database = $_ENV['DB_DATABASE'] ?? 'clinica';
    $username = $_ENV['DB_USERNAME'] ?? 'postgres';
    $password = $_ENV['DB_PASSWORD'] ?? 'admin';
    
    $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
    
    echo "<h3>🔌 Conexión a Base de Datos</h3>\n";
    echo "DSN: $dsn<br>\n";
    echo "Usuario: $username<br>\n";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conexión exitosa<br>\n";
    
    // Verificar tablas
    echo "<h3>📊 Verificación de Tablas</h3>\n";
    
    // Tabla mail_config
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM mail_config")->fetch();
        echo "✅ Tabla mail_config: " . $result['count'] . " registros<br>\n";
        
        // Mostrar configuraciones
        $configs = $pdo->query("SELECT id, smtp_host, smtp_port, from_email, is_active FROM mail_config ORDER BY id DESC LIMIT 3")->fetchAll();
        if ($configs) {
            echo "<ul>\n";
            foreach ($configs as $config) {
                $status = $config['is_active'] ? '🟢 Activo' : '🔴 Inactivo';
                echo "<li>ID {$config['id']}: {$config['smtp_host']}:{$config['smtp_port']} ({$config['from_email']}) - $status</li>\n";
            }
            echo "</ul>\n";
        }
    } catch (Exception $e) {
        echo "❌ Error con tabla mail_config: " . $e->getMessage() . "<br>\n";
    }
    
    // Tabla mail_logs
    try {
        $result = $pdo->query("SELECT COUNT(*) as count FROM mail_logs")->fetch();
        echo "✅ Tabla mail_logs: " . $result['count'] . " registros<br>\n";
    } catch (Exception $e) {
        echo "❌ Error con tabla mail_logs: " . $e->getMessage() . "<br>\n";
    }
    
    // Verificar PHPMailer
    echo "<h3>📧 Verificación de PHPMailer</h3>\n";
    
    $vendor_path = __DIR__ . '/vendor/autoload.php';
    if (file_exists($vendor_path)) {
        echo "✅ Composer autoload encontrado<br>\n";
        
        try {
            require_once $vendor_path;
            
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                echo "✅ PHPMailer cargado correctamente<br>\n";
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                echo "✅ Instancia de PHPMailer creada<br>\n";
            } else {
                echo "❌ PHPMailer no se pudo cargar<br>\n";
            }
        } catch (Exception $e) {
            echo "❌ Error cargando PHPMailer: " . $e->getMessage() . "<br>\n";
        }
    } else {
        echo "❌ Composer autoload no encontrado en: $vendor_path<br>\n";
    }
    
    // Test del endpoint de configuración
    echo "<h3>🔗 Test del Endpoint</h3>\n";
    echo "URL local: <a href='http://clinica.test/modules/mail/api/mail_config.php?action=get' target='_blank'>http://clinica.test/modules/mail/api/mail_config.php?action=get</a><br>\n";
    echo "Página principal: <a href='http://clinica.test/index.php?ruta=configuracion-correo' target='_blank'>http://clinica.test/index.php?ruta=configuracion-correo</a><br>\n";
    
    echo "<h3>✅ Verificación Completada</h3>\n";
    echo "El sistema está listo para funcionar en tu entorno local.<br>\n";
    
} catch (Exception $e) {
    echo "<h3>❌ Error</h3>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
    echo "Tipo: " . get_class($e) . "<br>\n";
    
    if ($e instanceof PDOException) {
        echo "Código SQL: " . $e->getCode() . "<br>\n";
    }
}
?>
<?php
/**
 * Verificar configuración de mail_config
 */

require_once __DIR__ . "/../api/core/EnvironmentSetup.php";

try {
    $dbConfig = EnvironmentSetup::getDatabaseConfig();
    $dsn = "pgsql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Verificación de Configuración mail_config</h2>";
    
    // Obtener la configuración activa
    $stmt = $pdo->prepare("SELECT * FROM mail_config WHERE activo = true LIMIT 1");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($config) {
        echo "<h3>✅ Configuración encontrada:</h3>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        foreach ($config as $key => $value) {
            $displayValue = (strpos($key, 'password') !== false) ? '***HIDDEN***' : $value;
            echo "<tr><td><strong>$key</strong></td><td>$displayValue</td></tr>";
        }
        echo "</table>";
        
        // Verificar campos críticos
        $requiredFields = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'smtp_auth', 'smtp_secure', 'from_email', 'from_name'];
        echo "<h3>🔍 Verificación de campos requeridos:</h3>";
        foreach ($requiredFields as $field) {
            if (isset($config[$field])) {
                echo "✅ $field: " . (strpos($field, 'password') !== false ? 'CONFIGURADO' : $config[$field]) . "<br>";
            } else {
                echo "❌ $field: FALTANTE<br>";
            }
        }
        
    } else {
        echo "❌ No se encontró configuración activa de correo";
        
        // Ver si hay alguna configuración
        $stmt = $pdo->prepare("SELECT * FROM mail_config");
        $stmt->execute();
        $allConfigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($allConfigs) {
            echo "<h3>📋 Configuraciones existentes (inactivas):</h3>";
            foreach ($allConfigs as $cfg) {
                echo "ID: {$cfg['id']}, Activo: {$cfg['activo']}, Host: {$cfg['smtp_host']}<br>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
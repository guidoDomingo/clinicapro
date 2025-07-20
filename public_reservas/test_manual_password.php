<?php
/**
 * Test manual del cambio de contraseña con logs detallados
 */

session_start();

// Función para registrar logs
function logMessage($message) {
    $logFile = "../logs/password_changes.log";
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] MANUAL TEST: $message\n", FILE_APPEND);
    echo "<p><strong>[$timestamp]</strong> $message</p>";
}

echo "<h1>Test Manual - Cambio de Contraseña</h1>";

// Verificar sesión
$userId = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0) {
    $userId = $_SESSION['user_id'];
    logMessage("Usuario autenticado del sistema principal: $userId");
} elseif (isset($_SESSION['paciente_id']) && $_SESSION['paciente_id'] > 0) {
    $userId = $_SESSION['paciente_id'];
    logMessage("Usuario autenticado del sistema de reservas públicas: $userId");
} else {
    logMessage("ERROR: Usuario no autenticado");
    echo "<div style='color: red;'>ERROR: Usuario no autenticado</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "../controller/profile.controller.php";
    
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    logMessage("Iniciando test de cambio de contraseña para usuario ID: $userId");
    logMessage("Contraseña actual proporcionada: " . ($currentPassword ? 'SÍ' : 'NO'));
    logMessage("Nueva contraseña proporcionada: " . ($newPassword ? 'SÍ' : 'NO'));
    
    try {
        $result = ControllerProfile::ctrChangePassword($userId, $currentPassword, $newPassword);
        logMessage("Resultado del cambio de contraseña: $result");
        
        if ($result === "ok") {
            echo "<div style='color: green; font-size: 18px;'>✅ ÉXITO: Contraseña cambiada correctamente</div>";
        } else {
            echo "<div style='color: red; font-size: 18px;'>❌ ERROR: $result</div>";
        }
    } catch (Exception $e) {
        logMessage("EXCEPCIÓN: " . $e->getMessage());
        echo "<div style='color: red; font-size: 18px;'>❌ EXCEPCIÓN: " . $e->getMessage() . "</div>";
    }
}

// Mostrar los últimos logs
echo "<h2>Últimos Logs</h2>";
$logFile = "../logs/password_changes.log";
if (file_exists($logFile)) {
    $logs = file($logFile);
    $recentLogs = array_slice($logs, -20); // Últimas 20 líneas
    
    echo "<div style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; max-height: 300px; overflow-y: auto;'>";
    echo "<pre>";
    foreach ($recentLogs as $log) {
        echo htmlspecialchars($log);
    }
    echo "</pre>";
    echo "</div>";
} else {
    echo "<p>No existe el archivo de log</p>";
}
?>

<h2>Test Form</h2>
<form method="POST" style="max-width: 400px;">
    <div style="margin-bottom: 10px;">
        <label>Contraseña Actual:</label><br>
        <input type="password" name="current_password" required style="width: 100%; padding: 5px;">
    </div>
    <div style="margin-bottom: 10px;">
        <label>Nueva Contraseña:</label><br>
        <input type="password" name="new_password" required style="width: 100%; padding: 5px;">
    </div>
    <div style="margin-bottom: 10px;">
        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px;">
            Test Cambio de Contraseña
        </button>
    </div>
</form>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    p { margin: 5px 0; }
</style>

<?php
/**
 * Debug detallado del sistema de upload de archivos
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una sesión simulada o real
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    // Simular usuario autenticado para testing
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = 1;
    $_SESSION['usuario'] = 'test_user';
    $_SESSION['user_name'] = 'Usuario Test';
    $_SESSION['user_email'] = 'test@clinica.com';
    
    $_SESSION['user_data'] = [
        'user_id' => 1,
        'usuario' => 'test_user',
        'nombre' => 'Usuario',
        'apellido' => 'Test',
        'email' => 'test@clinica.com',
        'documento' => '12345678',
        'telefono' => '123456789'
    ];
    
    echo "<div style='background: #ffffcc; padding: 10px; margin: 10px 0; border: 1px solid #ffeb3b;'>";
    echo "⚠️ Sesión de usuario simulada para testing";
    echo "</div>";
}

echo "<h2>Debug del Sistema de Upload de Archivos</h2>";

// Información del usuario autenticado
echo "<h3>👤 Estado de Autenticación</h3>";
echo "<p>Usuario logueado: " . ($_SESSION['login'] ? '✅ SÍ' : '❌ NO') . "</p>";
if (isset($_SESSION['user_data'])) {
    echo "<p>Datos del usuario:</p>";
    echo "<pre>" . print_r($_SESSION['user_data'], true) . "</pre>";
}

echo "<h3>Datos POST:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Datos FILES:</h3>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<h3>Estado del directorio uploads:</h3>";
$uploadDir = __DIR__ . "/uploads/reservas/";
echo "<p>Directorio base: $uploadDir</p>";
echo "<p>Existe: " . (file_exists($uploadDir) ? "✅ SÍ" : "❌ NO") . "</p>";
echo "<p>Es escribible: " . (is_writable($uploadDir) ? "✅ SÍ" : "❌ NO") . "</p>";

// Crear directorio si no existe
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "<p>✅ Directorio creado exitosamente</p>";
    } else {
        echo "<p>❌ Error creando directorio</p>";
    }
}

// Si hay datos POST, procesar la reserva
if (isset($_POST['guardarReserva'])) {
    echo "<h3>🚀 Procesando Reserva...</h3>";
    
    try {
        require_once 'controller/ReservasPublicController.php';
        
        $controller = new ReservasPublicController();
        $resultado = $controller->ctrProcesarReserva();
        
        echo "<h4>Resultado del procesamiento:</h4>";
        echo "<pre>";
        print_r($resultado);
        echo "</pre>";
        
        // Mostrar logs recientes
        echo "<h4>📋 Logs recientes:</h4>";
        $logFile = "c:/laragon/www/clinica/logs/public_reservas.log";
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $recentLogs = substr($logs, -5000); // Últimos 5KB
            echo "<pre style='background: #f8f9fa; padding: 10px; max-height: 400px; overflow-y: auto;'>";
            echo htmlspecialchars($recentLogs);
            echo "</pre>";
        } else {
            echo "<p>❌ No se encuentra el archivo de logs</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h3>📁 Estado Actual</h3>";
    echo "<p>No hay datos POST. Use el formulario para enviar una reserva.</p>";
    
    // Mostrar últimos logs si existen
    $logFile = "c:/laragon/www/clinica/logs/public_reservas.log";
    if (file_exists($logFile)) {
        echo "<h4>📋 Últimos logs:</h4>";
        $logs = file_get_contents($logFile);
        $recentLogs = substr($logs, -2000); // Últimos 2KB
        echo "<pre style='background: #f8f9fa; padding: 10px; max-height: 200px; overflow-y: auto;'>";
        echo htmlspecialchars($recentLogs);
        echo "</pre>";
    }
}

echo "<hr>";
echo "<h3>🔗 Enlaces útiles</h3>";
echo "<p><a href='test_con_auth.php'>← Volver al formulario con autenticación</a></p>";
echo "<p><a href='crear_archivos_prueba.php'>📁 Crear archivos de prueba</a></p>";
echo "<p><a href='verificar_tabla_archivos.php'>🔍 Verificar tabla de archivos</a></p>";
?>

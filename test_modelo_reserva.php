<?php
/**
 * Script para probar directamente la función mdlObtenerReservaPorId
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";

echo "<h2>Test de mdlObtenerReservaPorId</h2>";

// ID de reserva a probar
$reservaId = 68;

echo "<h3>1. Probando conexión básica</h3>";

try {
    $pdo = Conexion::conectar();
    if ($pdo) {
        echo "<p style='color: green;'>✓ Conexión exitosa</p>";
        
        // Verificar si la reserva existe directamente
        $stmt = $pdo->prepare("SELECT * FROM servicios_reservas WHERE reserva_id = :id");
        $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
        $stmt->execute();
        $existe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existe) {
            echo "<p style='color: green;'>✓ La reserva $reservaId existe en la base de datos</p>";
            echo "<p><strong>Datos básicos:</strong></p>";
            echo "<pre>" . print_r($existe, true) . "</pre>";
        } else {
            echo "<p style='color: red;'>✗ La reserva $reservaId NO existe en la base de datos</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Error de conexión</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Probando función del modelo</h3>";

try {
    $resultado = ModelServicios::mdlObtenerReservaPorId($reservaId);
    
    if ($resultado) {
        echo "<p style='color: green;'>✓ Función del modelo devolvió datos</p>";
        echo "<p><strong>Resultado:</strong></p>";
        echo "<pre>" . print_r($resultado, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Función del modelo devolvió NULL</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en función del modelo: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Verificando logs</h3>";

$logFile = "c:/laragon/www/clinica/logs/reservas.log";
if (file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lineasRecientes = array_slice(explode("\n", $logs), -20, 20); // Últimas 20 líneas
    echo "<p><strong>Últimas entradas del log:</strong></p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    echo implode("\n", $lineasRecientes);
    echo "</pre>";
} else {
    echo "<p>No se encontró archivo de log en: $logFile</p>";
}

echo "<hr>";
echo "<p><a href='javascript:history.back()'>← Volver</a></p>";
?>

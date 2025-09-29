<?php
/**
 * Test directo del método mdlCancelarReserva para diagnosticar el problema
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";

echo "<h1>🧪 Test del Método mdlCancelarReserva</h1>";

try {
    $reservaId = 95; // Usamos la reserva 95 que sabemos que existe
    
    echo "<h2>1. Estado ANTES de la cancelación</h2>";
    
    // Verificar estado actual
    $stmt = Conexion::conectar()->prepare("
        SELECT reserva_id, activo, reserva_estado, updated_at, updated_by
        FROM servicios_reservas 
        WHERE reserva_id = :id
    ");
    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $estadoAntes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($estadoAntes) {
        echo "<table border='1'>";
        foreach ($estadoAntes as $campo => $valor) {
            echo "<tr><td><strong>$campo</strong></td><td>$valor</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró la reserva $reservaId</p>";
        exit;
    }
    
    echo "<h2>2. Ejecutando mdlCancelarReserva</h2>";
    
    // Simular datos de sesión como lo haría el sistema real
    $_SESSION['user_id'] = 999; // Simular usuario logueado
    
    try {
        // Llamar al método real
        $resultado = ModelServicios::mdlCancelarReserva($reservaId, "Test desde diagnóstico", 999);
        
        echo "<h3>Resultado del método:</h3>";
        echo "<table border='1'>";
        foreach ($resultado as $key => $value) {
            $color = ($key === 'error' && !$value) ? 'background-color: #d4edda;' : 
                    (($key === 'error' && $value) ? 'background-color: #f8d7da;' : '');
            echo "<tr style='$color'><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
        
        if (!$resultado['error']) {
            echo "<p style='color: green;'>✅ Método ejecutado exitosamente</p>";
        } else {
            echo "<p style='color: red;'>❌ Error en método: {$resultado['mensaje']}</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>💥 Excepción en método: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
    echo "<h2>3. Estado DESPUÉS de la cancelación</h2>";
    
    // Verificar estado después
    $stmt = Conexion::conectar()->prepare("
        SELECT reserva_id, activo, reserva_estado, updated_at, updated_by
        FROM servicios_reservas 
        WHERE reserva_id = :id
    ");
    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $estadoDespues = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($estadoDespues) {
        echo "<table border='1'>";
        foreach ($estadoDespues as $campo => $valor) {
            $color = '';
            if ($campo === 'activo' && ($valor === 'f' || $valor === false || $valor === 0)) {
                $color = 'background-color: #d4edda;'; // Verde para cancelada
            } elseif ($campo === 'reserva_estado' && $valor === 'CANCELADA') {
                $color = 'background-color: #d4edda;'; // Verde para cancelada
            }
            echo "<tr style='$color'><td><strong>$campo</strong></td><td>$valor</td></tr>";
        }
        echo "</table>";
        
        // Comparar cambios
        echo "<h3>Comparación de cambios:</h3>";
        echo "<ul>";
        
        if ($estadoDespues['activo'] != $estadoAntes['activo']) {
            echo "<li style='color: green;'>✅ Campo 'activo' cambió de '{$estadoAntes['activo']}' a '{$estadoDespues['activo']}'</li>";
        } else {
            echo "<li style='color: red;'>❌ Campo 'activo' NO cambió (sigue siendo '{$estadoDespues['activo']}')</li>";
        }
        
        if ($estadoDespues['reserva_estado'] != $estadoAntes['reserva_estado']) {
            echo "<li style='color: green;'>✅ Campo 'reserva_estado' cambió de '{$estadoAntes['reserva_estado']}' a '{$estadoDespues['reserva_estado']}'</li>";
        } else {
            echo "<li style='color: red;'>❌ Campo 'reserva_estado' NO cambió (sigue siendo '{$estadoDespues['reserva_estado']}')</li>";
        }
        
        if ($estadoDespues['updated_at'] != $estadoAntes['updated_at']) {
            echo "<li style='color: green;'>✅ Campo 'updated_at' se actualizó a '{$estadoDespues['updated_at']}'</li>";
        } else {
            echo "<li style='color: orange;'>⚠️ Campo 'updated_at' NO cambió</li>";
        }
        
        echo "</ul>";
    }
    
    echo "<h2>4. Test de consulta con filtro</h2>";
    
    // Test de la consulta que usa el sistema real
    echo "<h3>Consulta con filtro activo = true:</h3>";
    $stmt = Conexion::conectar()->prepare("
        SELECT reserva_id, activo, reserva_estado
        FROM servicios_reservas 
        WHERE reserva_id = :id AND activo = true
    ");
    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $consultaFiltrada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($consultaFiltrada) {
        echo "<p style='color: orange;'>⚠️ La reserva TODAVÍA aparece con filtro activo = true</p>";
        echo "<table border='1'>";
        foreach ($consultaFiltrada as $k => $v) {
            echo "<tr><td>$k</td><td>$v</td></tr>";
        }
        echo "</table>";
        echo "<p><strong>Esto significa que la cancelación NO funcionó correctamente</strong></p>";
    } else {
        echo "<p style='color: green;'>✅ La reserva YA NO aparece con filtro activo = true</p>";
        echo "<p><strong>Esto significa que la cancelación funcionó correctamente</strong></p>";
    }
    
    echo "<h2>5. Logs del sistema</h2>";
    
    // Intentar leer logs si existen
    $logFile = "/var/log/clinica/reservas.log";
    if (file_exists($logFile)) {
        echo "<h3>Últimas líneas del log:</h3>";
        $lines = file($logFile);
        $lastLines = array_slice($lines, -20); // Últimas 20 líneas
        echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;'>";
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "<p>📋 No se encontró archivo de log en $logFile</p>";
    }
    
    // Test del controller
    echo "<h2>6. Test del Controller</h2>";
    
    if (isset($_GET['test_controller']) && $_GET['test_controller'] === '1') {
        echo "<h3>🧪 Probando ControladorServicios::ctrCancelarReserva</h3>";
        
        require_once "controller/servicios.controller.php";
        
        try {
            // Simular otra reserva para probar el controller
            $testReservaId = 96;
            $resultado = ControladorServicios::ctrCancelarReserva($testReservaId, "Test controller");
            
            echo "<h4>Resultado del controller:</h4>";
            echo "<table border='1'>";
            foreach ($resultado as $k => $v) {
                echo "<tr><td><strong>$k</strong></td><td>$v</td></tr>";
            }
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>Error en controller: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p><a href='?test_controller=1' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🧪 Probar Controller</a></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error general: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { border-collapse: collapse; margin: 10px 0; width: 100%; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; margin-top: 30px; }
    h3 { color: #666; margin-top: 20px; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>
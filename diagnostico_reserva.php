<?php
/**
 * Diagnóstico completo del sistema de cancelación
 */

require_once "../model/conexion.php";

echo "<h1>🔍 Diagnóstico Sistema de Cancelación</h1>";

try {
    // 1. Verificar si existe el campo activo
    echo "<h2>1. Verificación del campo 'activo'</h2>";
    $stmt = Conexion::conectar()->prepare("
        SELECT column_name, data_type, column_default, is_nullable
        FROM information_schema.columns 
        WHERE table_name = 'servicios_reservas' 
        AND column_name = 'activo'
    ");
    $stmt->execute();
    $campoActivo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($campoActivo) {
        echo "<p style='color: green;'>✅ Campo 'activo' EXISTE</p>";
        echo "<table border='1'>";
        foreach ($campoActivo as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Campo 'activo' NO EXISTE - Ejecutar script SQL</p>";
        exit;
    }
    
    // 2. Verificar estructura completa de la tabla
    echo "<h2>2. Estructura completa de servicios_reservas</h2>";
    $stmt = Conexion::conectar()->prepare("
        SELECT column_name, data_type, column_default
        FROM information_schema.columns 
        WHERE table_name = 'servicios_reservas' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'><tr><th>Columna</th><th>Tipo</th><th>Default</th></tr>";
    foreach ($columnas as $col) {
        echo "<tr><td>{$col['column_name']}</td><td>{$col['data_type']}</td><td>{$col['column_default']}</td></tr>";
    }
    echo "</table>";
    
    // 3. Estado actual de la reserva 114
    echo "<h2>3. Estado actual de reserva 114</h2>";
    $stmt = Conexion::conectar()->prepare("
        SELECT reserva_id, activo, reserva_estado, created_at, updated_at, updated_by
        FROM servicios_reservas 
        WHERE reserva_id = 114
    ");
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reserva) {
        echo "<table border='1'>";
        foreach ($reserva as $key => $value) {
            $color = '';
            if ($key === 'activo') {
                $color = ($value === 't' || $value === true) ? 'color: orange;' : 'color: green;';
            }
            echo "<tr><td><strong>$key</strong></td><td style='$color'>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No se encontró reserva con ID 114</p>";
    }
    
    // 4. Probar manualmente la cancelación
    echo "<h2>4. Test manual de cancelación</h2>";
    
    if (isset($_GET['test_cancel']) && $_GET['test_cancel'] == '1') {
        echo "<p>🧪 Ejecutando test de cancelación...</p>";
        
        try {
            $conexion = Conexion::conectar();
            $conexion->beginTransaction();
            
            // Intentar actualizar manualmente
            $stmt = $conexion->prepare(
                "UPDATE servicios_reservas 
                 SET activo = false,
                     reserva_estado = 'CANCELADA',
                     updated_at = CURRENT_TIMESTAMP,
                     updated_by = 999
                 WHERE reserva_id = 114"
            );
            
            $resultado = $stmt->execute();
            $rowsAffected = $stmt->rowCount();
            
            if ($resultado) {
                echo "<p style='color: green;'>✅ UPDATE ejecutado exitosamente</p>";
                echo "<p>Filas afectadas: $rowsAffected</p>";
                
                // Verificar el cambio
                $stmtVerify = $conexion->prepare("SELECT activo, reserva_estado FROM servicios_reservas WHERE reserva_id = 114");
                $stmtVerify->execute();
                $verificacion = $stmtVerify->fetch(PDO::FETCH_ASSOC);
                
                echo "<p>Estado después del UPDATE:</p>";
                echo "<pre>" . print_r($verificacion, true) . "</pre>";
                
                $conexion->rollback(); // Rollback para no afectar datos reales
                echo "<p style='color: blue;'>ℹ️ Rollback ejecutado - no se guardaron cambios</p>";
            } else {
                $conexion->rollback();
                echo "<p style='color: red;'>❌ Error en UPDATE</p>";
            }
            
        } catch (Exception $e) {
            $conexion->rollback();
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p><a href='?test_cancel=1' style='background: #007bff; color: white; padding: 10px; text-decoration: none;'>🧪 Ejecutar Test Manual</a></p>";
    }
    
    // 5. Verificar método de cancelación
    echo "<h2>5. Test del método mdlCancelarReserva</h2>";
    
    if (isset($_GET['test_method']) && $_GET['test_method'] == '1') {
        echo "<p>🧪 Probando método mdlCancelarReserva...</p>";
        
        // Incluir el modelo
        require_once "../model/servicios.model.php";
        
        try {
            $resultado = ModelServicios::mdlCancelarReserva(114, "Test de diagnóstico", 999);
            
            echo "<p>Resultado del método:</p>";
            echo "<pre>" . print_r($resultado, true) . "</pre>";
            
            if (!$resultado['error']) {
                // Verificar el estado
                $stmt = Conexion::conectar()->prepare("SELECT activo, reserva_estado FROM servicios_reservas WHERE reserva_id = 114");
                $stmt->execute();
                $estado = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<p>Estado después del método:</p>";
                echo "<pre>" . print_r($estado, true) . "</pre>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error en método: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p><a href='?test_method=1' style='background: #28a745; color: white; padding: 10px; text-decoration: none;'>🧪 Probar Método</a></p>";
    }
    
    // 6. Verificar logs de error
    echo "<h2>6. Información adicional</h2>";
    echo "<p><strong>Conexión a BD:</strong> " . (Conexion::conectar() ? "✅ OK" : "❌ Error") . "</p>";
    echo "<p><strong>Versión PostgreSQL:</strong> ";
    $stmt = Conexion::conectar()->query("SELECT version()");
    echo $stmt->fetchColumn();
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error general: " . $e->getMessage() . "</p>";
    echo "<p>Trace: " . $e->getTraceAsString() . "</p>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; }
</style>
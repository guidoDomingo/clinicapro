<?php
/**
 * Diagnóstico profundo del problema de transacciones en cancelación
 */

require_once "model/conexion.php";

echo "<h1>🔧 Diagnóstico Profundo - Problema de Transacciones</h1>";

try {
    $reservaId = 114;
    
    echo "<h2>1. Test de Actualización Directa (sin transacciones)</h2>";
    
    $conexion = Conexion::conectar();
    
    // Estado antes
    echo "<h3>Estado ANTES:</h3>";
    $stmt = $conexion->prepare("SELECT reserva_id, activo, reserva_estado FROM servicios_reservas WHERE reserva_id = :id");
    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $antes = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    foreach ($antes as $k => $v) {
        echo "<tr><td>$k</td><td>$v</td></tr>";
    }
    echo "</table>";
    
    if (isset($_GET['test']) && $_GET['test'] === 'direct') {
        echo "<h3>⚡ Ejecutando UPDATE directo...</h3>";
        
        try {
            // UPDATE directo SIN transacciones
            $stmt = $conexion->prepare("
                UPDATE servicios_reservas 
                SET activo = false, 
                    reserva_estado = 'CANCELADA',
                    updated_at = CURRENT_TIMESTAMP,
                    updated_by = 999
                WHERE reserva_id = :reserva_id
            ");
            $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $resultado = $stmt->execute();
            $filasAfectadas = $stmt->rowCount();
            
            echo "<p><strong>Resultado UPDATE directo:</strong></p>";
            echo "<ul>";
            echo "<li>Ejecutado: " . ($resultado ? '✅ SÍ' : '❌ NO') . "</li>";
            echo "<li>Filas afectadas: <strong>$filasAfectadas</strong></li>";
            echo "</ul>";
            
            if ($resultado && $filasAfectadas > 0) {
                echo "<p style='color: green;'>✅ UPDATE directo funcionó</p>";
                
                // Verificar inmediatamente
                $stmt = $conexion->prepare("SELECT reserva_id, activo, reserva_estado, updated_at FROM servicios_reservas WHERE reserva_id = :id");
                $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                $stmt->execute();
                $despues = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<h3>Estado DESPUÉS del UPDATE directo:</h3>";
                echo "<table border='1'>";
                foreach ($despues as $k => $v) {
                    $color = '';
                    if ($k === 'activo' && ($v === 'f' || $v === false || $v === 0)) {
                        $color = 'background-color: #d4edda;';
                    } elseif ($k === 'reserva_estado' && $v === 'CANCELADA') {
                        $color = 'background-color: #d4edda;';
                    }
                    echo "<tr style='$color'><td>$k</td><td>$v</td></tr>";
                }
                echo "</table>";
                
                // Verificar en consulta filtrada
                $stmt = $conexion->prepare("SELECT COUNT(*) FROM servicios_reservas WHERE reserva_id = :id AND activo = true");
                $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                $stmt->execute();
                $visible = $stmt->fetchColumn();
                
                if ($visible === 0 || $visible === '0') {
                    echo "<p style='color: green; font-size: 1.2em;'>🎉 <strong>ÉXITO TOTAL - La reserva ya no aparece en búsquedas</strong></p>";
                    
                    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                    echo "<h3>✅ Problema Resuelto con UPDATE Directo</h3>";
                    echo "<p>La reserva 114 ha sido cancelada exitosamente usando UPDATE directo.</p>";
                    echo "<p><strong>El problema era que las transacciones en el método original causaban rollback.</strong></p>";
                    echo "<p>Ahora puedes volver a la interfaz principal y verificar que la reserva ya no aparece.</p>";
                    echo "</div>";
                    
                } else {
                    echo "<p style='color: red;'>❌ Aún aparece en búsquedas - hay otro problema</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ UPDATE directo falló</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>💥 Error en UPDATE directo: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p><a href='?test=direct' style='background: #dc3545; color: white; padding: 15px; text-decoration: none; border-radius: 5px;'>🔧 Ejecutar UPDATE Directo</a></p>";
        echo "<p style='color: #666;'><em>Esto ejecutará un UPDATE simple sin transacciones complejas para aislar el problema.</em></p>";
    }
    
    echo "<h2>2. Análisis del Problema en mdlCancelarReserva</h2>";
    
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        echo "<h3>🔍 Debugging del método original...</h3>";
        
        // Incluir el modelo pero con debugging
        require_once "model/servicios.model.php";
        
        // Crear una versión simplificada del método para debugging
        try {
            $conexion = Conexion::conectar();
            
            echo "<p><strong>Paso 1:</strong> Verificar que la reserva existe</p>";
            $stmtCheck = $conexion->prepare("
                SELECT reserva_id, reserva_estado, activo, agenda_id 
                FROM servicios_reservas 
                WHERE reserva_id = :reserva_id
            ");
            $stmtCheck->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
            $stmtCheck->execute();
            $reserva = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($reserva) {
                echo "<p style='color: green;'>✅ Reserva encontrada</p>";
                echo "<pre>" . print_r($reserva, true) . "</pre>";
                
                echo "<p><strong>Paso 2:</strong> Iniciar transacción manual</p>";
                $conexion->beginTransaction();
                echo "<p style='color: blue;'>🔄 Transacción iniciada</p>";
                
                echo "<p><strong>Paso 3:</strong> Ejecutar UPDATE</p>";
                $stmt = $conexion->prepare("
                    UPDATE servicios_reservas 
                    SET activo = false,
                        reserva_estado = 'CANCELADA',
                        updated_at = CURRENT_TIMESTAMP,
                        updated_by = :usuario_id
                    WHERE reserva_id = :reserva_id
                ");
                $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
                $stmt->bindParam(":usuario_id", 999, PDO::PARAM_INT);
                $resultado = $stmt->execute();
                $filasAfectadas = $stmt->rowCount();
                
                echo "<p>Resultado UPDATE: " . ($resultado ? '✅' : '❌') . "</p>";
                echo "<p>Filas afectadas: <strong>$filasAfectadas</strong></p>";
                
                if ($resultado && $filasAfectadas > 0) {
                    echo "<p style='color: green;'>✅ UPDATE exitoso dentro de transacción</p>";
                    
                    // Verificar dentro de la transacción
                    echo "<p><strong>Paso 4:</strong> Verificar cambios dentro de transacción</p>";
                    $stmt = $conexion->prepare("SELECT activo, reserva_estado FROM servicios_reservas WHERE reserva_id = :id");
                    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                    $stmt->execute();
                    $dentroTransaccion = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<p>Estado dentro de transacción:</p>";
                    echo "<pre>" . print_r($dentroTransaccion, true) . "</pre>";
                    
                    echo "<p><strong>Paso 5:</strong> COMMIT</p>";
                    $conexion->commit();
                    echo "<p style='color: green;'>✅ COMMIT ejecutado</p>";
                    
                    // Verificar después del commit
                    echo "<p><strong>Paso 6:</strong> Verificar después del COMMIT</p>";
                    $stmt = $conexion->prepare("SELECT activo, reserva_estado, updated_at FROM servicios_reservas WHERE reserva_id = :id");
                    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                    $stmt->execute();
                    $despuesCommit = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<p>Estado después del COMMIT:</p>";
                    echo "<pre>" . print_r($despuesCommit, true) . "</pre>";
                    
                    if ($despuesCommit['activo'] === 'f' || $despuesCommit['activo'] === false || $despuesCommit['activo'] === 0) {
                        echo "<p style='color: green; font-size: 1.2em;'>🎉 <strong>TRANSACCIÓN MANUAL EXITOSA</strong></p>";
                    } else {
                        echo "<p style='color: red;'>❌ Transacción manual también falló</p>";
                    }
                    
                } else {
                    $conexion->rollback();
                    echo "<p style='color: red;'>❌ UPDATE falló - rollback ejecutado</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Reserva no encontrada</p>";
            }
            
        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollback();
            }
            echo "<p style='color: red;'>💥 Error en debugging: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
    } else {
        echo "<p><a href='?debug=1' style='background: #007bff; color: white; padding: 15px; text-decoration: none; border-radius: 5px;'>🔍 Debug Método Original</a></p>";
    }
    
    echo "<h2>3. Información del Sistema</h2>";
    
    // Información de la conexión
    echo "<h3>Configuración de la Base de Datos:</h3>";
    try {
        $stmt = $conexion->query("SELECT version()");
        $version = $stmt->fetchColumn();
        echo "<p><strong>Versión PostgreSQL:</strong> $version</p>";
        
        $stmt = $conexion->query("SHOW autocommit");
        $autocommit = $stmt->fetchColumn();
        echo "<p><strong>Autocommit:</strong> $autocommit</p>";
        
        $stmt = $conexion->query("SELECT current_setting('transaction_isolation')");
        $isolation = $stmt->fetchColumn();
        echo "<p><strong>Isolation Level:</strong> $isolation</p>";
        
    } catch (Exception $e) {
        echo "<p>Error obteniendo info de BD: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error general: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; margin-top: 30px; }
    h3 { color: #666; margin-top: 20px; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; }
</style>
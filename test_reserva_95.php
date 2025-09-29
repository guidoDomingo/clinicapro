<?php
/**
 * Verificación y prueba específica para reserva ID 95
 */

require_once "model/conexion.php";

echo "<h1>🔍 Verificación Específica - Reserva ID 95</h1>";

try {
    $conexion = Conexion::conectar();
    $reservaId = 95;
    
    // 1. Verificar si existe el campo activo en la tabla
    echo "<h2>1. Verificación del campo 'activo'</h2>";
    $stmt = $conexion->prepare("
        SELECT column_name, data_type, column_default, is_nullable
        FROM information_schema.columns 
        WHERE table_name = 'servicios_reservas' 
        AND column_name = 'activo'
    ");
    $stmt->execute();
    $campoActivo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($campoActivo) {
        echo "<p style='color: green;'>✅ Campo 'activo' EXISTE en la tabla</p>";
        echo "<table border='1' style='margin: 10px 0;'>";
        echo "<tr><th>Propiedad</th><th>Valor</th></tr>";
        foreach ($campoActivo as $key => $value) {
            echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Campo 'activo' NO EXISTE</p>";
        echo "<p>Ejecutando creación del campo...</p>";
        
        try {
            $conexion->exec("ALTER TABLE servicios_reservas ADD COLUMN activo BOOLEAN NOT NULL DEFAULT TRUE");
            $conexion->exec("CREATE INDEX IF NOT EXISTS idx_reservas_activo ON servicios_reservas (activo)");
            $conexion->exec("UPDATE servicios_reservas SET activo = TRUE WHERE activo IS NULL");
            echo "<p style='color: green;'>✅ Campo 'activo' creado exitosamente</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error al crear campo: " . $e->getMessage() . "</p>";
            exit;
        }
    }
    
    // 2. Verificar si existe la reserva ID 95
    echo "<h2>2. Verificación de Reserva ID $reservaId</h2>";
    $stmt = $conexion->prepare("
        SELECT reserva_id, activo, reserva_estado, fecha_reserva, hora_inicio, hora_fin, 
               created_at, updated_at, updated_by
        FROM servicios_reservas 
        WHERE reserva_id = :reserva_id
    ");
    $stmt->bindParam(":reserva_id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reserva) {
        echo "<p style='color: green;'>✅ Reserva ID $reservaId EXISTE</p>";
        echo "<table border='1' style='margin: 10px 0; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
        
        foreach ($reserva as $campo => $valor) {
            $estado = '';
            $color = '';
            
            if ($campo === 'activo') {
                if ($valor === 't' || $valor === true || $valor === 1) {
                    $estado = '⚠️ ACTIVA';
                    $color = 'background-color: #fff3cd;';
                } else {
                    $estado = '✅ CANCELADA';
                    $color = 'background-color: #d4edda;';
                }
            } elseif ($campo === 'reserva_estado') {
                $estado = ($valor === 'CANCELADA') ? '✅ CANCELADA' : '⚠️ ' . strtoupper($valor);
            }
            
            echo "<tr style='$color'>";
            echo "<td><strong>$campo</strong></td>";
            echo "<td>$valor</td>";
            echo "<td>$estado</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ No existe reserva con ID $reservaId</p>";
        
        // Buscar reservas cercanas
        echo "<h3>Buscando reservas cercanas...</h3>";
        $stmt = $conexion->prepare("
            SELECT reserva_id, activo, reserva_estado, fecha_reserva 
            FROM servicios_reservas 
            WHERE reserva_id BETWEEN :id_min AND :id_max
            ORDER BY reserva_id
        ");
        $stmt->bindParam(":id_min", $reservaId - 5, PDO::PARAM_INT);
        $stmt->bindParam(":id_max", $reservaId + 5, PDO::PARAM_INT);
        $stmt->execute();
        $cercanas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($cercanas) {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Activo</th><th>Estado</th><th>Fecha</th></tr>";
            foreach ($cercanas as $r) {
                echo "<tr>";
                echo "<td>{$r['reserva_id']}</td>";
                echo "<td>{$r['activo']}</td>";
                echo "<td>{$r['reserva_estado']}</td>";
                echo "<td>{$r['fecha_reserva']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        exit;
    }
    
    // 3. Pruebas de actualización
    echo "<h2>3. Pruebas de Actualización</h2>";
    
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        if ($action === 'test_cancel') {
            echo "<h3>🧪 Ejecutando Prueba de Cancelación</h3>";
            
            try {
                $conexion->beginTransaction();
                
                // Estado antes
                echo "<p><strong>Estado ANTES de la actualización:</strong></p>";
                $stmt = $conexion->prepare("SELECT activo, reserva_estado FROM servicios_reservas WHERE reserva_id = :id");
                $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                $stmt->execute();
                $estadoAntes = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Activo: <strong>{$estadoAntes['activo']}</strong>, Estado: <strong>{$estadoAntes['reserva_estado']}</strong></p>";
                
                // Ejecutar actualización
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
                
                echo "<p><strong>Resultado del UPDATE:</strong></p>";
                echo "<ul>";
                echo "<li>Ejecutado: " . ($resultado ? '✅ SÍ' : '❌ NO') . "</li>";
                echo "<li>Filas afectadas: <strong>$filasAfectadas</strong></li>";
                echo "</ul>";
                
                // Estado después
                echo "<p><strong>Estado DESPUÉS de la actualización:</strong></p>";
                $stmt = $conexion->prepare("SELECT activo, reserva_estado, updated_at FROM servicios_reservas WHERE reserva_id = :id");
                $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                $stmt->execute();
                $estadoDespues = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "<p>Activo: <strong>{$estadoDespues['activo']}</strong>, Estado: <strong>{$estadoDespues['reserva_estado']}</strong></p>";
                echo "<p>Actualizado: <strong>{$estadoDespues['updated_at']}</strong></p>";
                
                // Verificar cambios
                if ($estadoDespues['activo'] != $estadoAntes['activo']) {
                    echo "<p style='color: green;'>✅ El campo 'activo' se actualizó correctamente</p>";
                } else {
                    echo "<p style='color: red;'>❌ El campo 'activo' NO se actualizó</p>";
                }
                
                if ($estadoDespues['reserva_estado'] != $estadoAntes['reserva_estado']) {
                    echo "<p style='color: green;'>✅ El campo 'reserva_estado' se actualizó correctamente</p>";
                } else {
                    echo "<p style='color: red;'>❌ El campo 'reserva_estado' NO se actualizó</p>";
                }
                
                // IMPORTANTE: Hacer rollback para no afectar datos reales
                $conexion->rollback();
                echo "<p style='color: blue;'>ℹ️ <strong>ROLLBACK ejecutado - Los cambios NO se guardaron permanentemente</strong></p>";
                
            } catch (Exception $e) {
                $conexion->rollback();
                echo "<p style='color: red;'>❌ Error en la prueba: " . $e->getMessage() . "</p>";
            }
            
        } elseif ($action === 'cancel_permanent') {
            echo "<h3>⚠️ Ejecutando Cancelación PERMANENTE</h3>";
            
            try {
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
                
                if ($resultado && $filasAfectadas > 0) {
                    echo "<p style='color: green;'>✅ Reserva $reservaId cancelada PERMANENTEMENTE</p>";
                    echo "<p>Filas afectadas: $filasAfectadas</p>";
                    
                    // Mostrar estado actual
                    $stmt = $conexion->prepare("SELECT activo, reserva_estado, updated_at FROM servicios_reservas WHERE reserva_id = :id");
                    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                    $stmt->execute();
                    $estadoFinal = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<p><strong>Estado final:</strong></p>";
                    echo "<ul>";
                    echo "<li>Activo: <strong>{$estadoFinal['activo']}</strong></li>";
                    echo "<li>Estado: <strong>{$estadoFinal['reserva_estado']}</strong></li>";
                    echo "<li>Actualizado: <strong>{$estadoFinal['updated_at']}</strong></li>";
                    echo "</ul>";
                    
                } else {
                    echo "<p style='color: red;'>❌ No se pudo cancelar la reserva</p>";
                }
                
            } catch (Exception $e) {
                echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
            }
        }
        
    } else {
        echo "<h3>Opciones de Prueba:</h3>";
        echo "<p><a href='?action=test_cancel' style='background: orange; color: white; padding: 10px; margin: 5px; text-decoration: none; border-radius: 4px;'>🧪 Prueba de Cancelación (con rollback)</a></p>";
        echo "<p><a href='?action=cancel_permanent' style='background: red; color: white; padding: 10px; margin: 5px; text-decoration: none; border-radius: 4px;'>⚠️ Cancelación PERMANENTE</a></p>";
        echo "<p style='color: #666; font-size: 0.9em;'><em>La prueba con rollback no afecta los datos. La cancelación permanente sí modifica la base de datos.</em></p>";
    }
    
    // 4. Test de consulta con filtro activo
    echo "<h2>4. Test de Consulta con Filtro</h2>";
    
    // Consulta SIN filtro activo
    echo "<h3>Sin filtro (todas las reservas):</h3>";
    $stmt = $conexion->prepare("
        SELECT reserva_id, activo, reserva_estado 
        FROM servicios_reservas 
        WHERE reserva_id IN (93, 94, 95, 96, 97)
        ORDER BY reserva_id
    ");
    $stmt->execute();
    $sinFiltro = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($sinFiltro) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Activo</th><th>Estado</th></tr>";
        foreach ($sinFiltro as $r) {
            $bgColor = ($r['reserva_id'] == $reservaId) ? 'background-color: #e3f2fd;' : '';
            echo "<tr style='$bgColor'>";
            echo "<td><strong>{$r['reserva_id']}</strong></td>";
            echo "<td>{$r['activo']}</td>";
            echo "<td>{$r['reserva_estado']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Consulta CON filtro activo = true
    echo "<h3>Con filtro activo = true (solo activas):</h3>";
    $stmt = $conexion->prepare("
        SELECT reserva_id, activo, reserva_estado 
        FROM servicios_reservas 
        WHERE reserva_id IN (93, 94, 95, 96, 97)
        AND activo = true
        ORDER BY reserva_id
    ");
    $stmt->execute();
    $conFiltro = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($conFiltro) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Activo</th><th>Estado</th><th>Visibilidad</th></tr>";
        foreach ($conFiltro as $r) {
            $bgColor = ($r['reserva_id'] == $reservaId) ? 'background-color: #e3f2fd;' : '';
            echo "<tr style='$bgColor'>";
            echo "<td><strong>{$r['reserva_id']}</strong></td>";
            echo "<td>{$r['activo']}</td>";
            echo "<td>{$r['reserva_estado']}</td>";
            echo "<td>✅ VISIBLE</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No hay reservas activas en ese rango (esto es normal si se cancelaron)</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error general: " . $e->getMessage() . "</p>";
    echo "<p><strong>Trace:</strong></p><pre>" . $e->getTraceAsString() . "</pre>";
}

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; font-weight: bold; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; margin-top: 30px; }
    h3 { color: #666; margin-top: 25px; }
    ul { margin: 10px 0; }
    li { margin: 5px 0; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style>
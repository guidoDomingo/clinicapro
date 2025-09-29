<?php
/**
 * Cancelación específica de la reserva 114
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";

echo "<h1>🎯 Cancelación Específica - Reserva 114</h1>";

try {
    $reservaId = 114;
    
    echo "<h2>1. Estado actual de la reserva 114</h2>";
    
    $stmt = Conexion::conectar()->prepare("
        SELECT reserva_id, activo, reserva_estado, fecha_reserva, hora_inicio, hora_fin,
               created_at, updated_at, updated_by
        FROM servicios_reservas 
        WHERE reserva_id = :id
    ");
    $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $estadoActual = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($estadoActual) {
        echo "<table border='1'>";
        foreach ($estadoActual as $campo => $valor) {
            $color = '';
            if ($campo === 'activo') {
                $color = ($valor == '1' || $valor == 't' || $valor == true) ? 'background-color: #fff3cd;' : 'background-color: #d4edda;';
            }
            echo "<tr style='$color'><td><strong>$campo</strong></td><td>$valor</td></tr>";
        }
        echo "</table>";
        
        if ($estadoActual['activo'] == '1' || $estadoActual['activo'] == 't' || $estadoActual['activo'] == true) {
            echo "<p style='color: orange;'>⚠️ La reserva 114 está ACTIVA - necesita ser cancelada</p>";
        } else {
            echo "<p style='color: green;'>✅ La reserva 114 ya está CANCELADA</p>";
            exit;
        }
    } else {
        echo "<p style='color: red;'>❌ No se encontró la reserva 114</p>";
        exit;
    }
    
    echo "<h2>2. Proceso de cancelación</h2>";
    
    if (isset($_GET['confirmar']) && $_GET['confirmar'] === '1') {
        echo "<h3>⚡ Ejecutando cancelación...</h3>";
        
        try {
            // Simular sesión de usuario
            $_SESSION['user_id'] = 999;
            
            // Llamar al método de cancelación
            $resultado = ModelServicios::mdlCancelarReserva($reservaId, "Cancelación manual desde diagnóstico", 999);
            
            echo "<h4>Resultado de la cancelación:</h4>";
            echo "<table border='1'>";
            foreach ($resultado as $key => $value) {
                $color = ($key === 'error' && !$value) ? 'background-color: #d4edda;' : 
                        (($key === 'error' && $value) ? 'background-color: #f8d7da;' : '');
                echo "<tr style='$color'><td><strong>$key</strong></td><td>$value</td></tr>";
            }
            echo "</table>";
            
            if (!$resultado['error']) {
                echo "<p style='color: green; font-size: 1.2em;'>🎉 <strong>RESERVA 114 CANCELADA EXITOSAMENTE</strong></p>";
                
                // Verificar el estado después de la cancelación
                echo "<h3>Estado después de la cancelación:</h3>";
                $stmt = Conexion::conectar()->prepare("
                    SELECT reserva_id, activo, reserva_estado, updated_at, updated_by
                    FROM servicios_reservas 
                    WHERE reserva_id = :id
                ");
                $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                $stmt->execute();
                $estadoFinal = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<table border='1'>";
                foreach ($estadoFinal as $campo => $valor) {
                    $color = '';
                    if ($campo === 'activo' && ($valor === 'f' || $valor === false || $valor === 0 || $valor === '0')) {
                        $color = 'background-color: #d4edda;';
                    } elseif ($campo === 'reserva_estado' && $valor === 'CANCELADA') {
                        $color = 'background-color: #d4edda;';
                    }
                    echo "<tr style='$color'><td><strong>$campo</strong></td><td>$valor</td></tr>";
                }
                echo "</table>";
                
                // Test de la consulta filtrada
                echo "<h3>Verificación - ¿Aparece en búsquedas?</h3>";
                $stmt = Conexion::conectar()->prepare("
                    SELECT COUNT(*) as total
                    FROM servicios_reservas 
                    WHERE reserva_id = :id AND activo = true
                ");
                $stmt->bindParam(":id", $reservaId, PDO::PARAM_INT);
                $stmt->execute();
                $visible = $stmt->fetchColumn();
                
                if ($visible > 0) {
                    echo "<p style='color: red;'>❌ La reserva TODAVÍA aparece en búsquedas (problema)</p>";
                } else {
                    echo "<p style='color: green;'>✅ La reserva YA NO aparece en búsquedas (correcto)</p>";
                }
                
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
                echo "<h3>🎯 Problema Resuelto</h3>";
                echo "<p>La reserva 114 ha sido cancelada exitosamente. Ahora:</p>";
                echo "<ul>";
                echo "<li>✅ Ya no aparecerá en las búsquedas de reservas</li>";
                echo "<li>✅ El horario queda libre para nuevas reservas</li>";
                echo "<li>✅ Se mantiene el registro para auditoría</li>";
                echo "</ul>";
                echo "<p><strong>Puedes volver a la interfaz principal y verificar que ya no aparece en la lista.</strong></p>";
                echo "</div>";
                
            } else {
                echo "<p style='color: red; font-size: 1.2em;'>❌ <strong>ERROR:</strong> {$resultado['mensaje']}</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>💥 Excepción: " . $e->getMessage() . "</p>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        }
        
    } else {
        echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Confirmación Requerida</h3>";
        echo "<p>¿Estás seguro de que quieres cancelar la reserva 114?</p>";
        echo "<p><strong>Datos de la reserva:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Fecha:</strong> {$estadoActual['fecha_reserva']}</li>";
        echo "<li><strong>Hora:</strong> {$estadoActual['hora_inicio']} - {$estadoActual['hora_fin']}</li>";
        echo "<li><strong>Estado actual:</strong> {$estadoActual['reserva_estado']}</li>";
        echo "</ul>";
        echo "<p style='color: red;'><em>Esta acción marcará la reserva como cancelada y liberará el horario.</em></p>";
        echo "<p>";
        echo "<a href='?confirmar=1' style='background: #dc3545; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>🗑️ SÍ, CANCELAR RESERVA 114</a>";
        echo "&nbsp;&nbsp;";
        echo "<a href='test_reserva_95.php' style='background: #6c757d; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px;'>❌ No, volver</a>";
        echo "</p>";
        echo "</div>";
    }
    
    echo "<h2>3. Información adicional</h2>";
    
    // Mostrar reservas del mismo día
    echo "<h3>Otras reservas del 2025-09-22:</h3>";
    $stmt = Conexion::conectar()->prepare("
        SELECT reserva_id, activo, reserva_estado, hora_inicio, hora_fin,
               CASE WHEN activo = true THEN 'VISIBLE' ELSE 'OCULTA' END as visibilidad
        FROM servicios_reservas 
        WHERE fecha_reserva = '2025-09-22'
        ORDER BY hora_inicio
    ");
    $stmt->execute();
    $reservasDelDia = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($reservasDelDia) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Activo</th><th>Estado</th><th>Hora</th><th>Visibilidad</th></tr>";
        foreach ($reservasDelDia as $r) {
            $bgColor = ($r['reserva_id'] == $reservaId) ? 'background-color: #e3f2fd;' : 
                      (($r['visibilidad'] == 'OCULTA') ? 'background-color: #ffebee;' : '');
            echo "<tr style='$bgColor'>";
            echo "<td><strong>{$r['reserva_id']}</strong></td>";
            echo "<td>{$r['activo']}</td>";
            echo "<td>{$r['reserva_estado']}</td>";
            echo "<td>{$r['hora_inicio']} - {$r['hora_fin']}</td>";
            echo "<td><strong>{$r['visibilidad']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error: " . $e->getMessage() . "</p>";
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
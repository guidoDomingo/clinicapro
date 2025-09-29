<?php
/**
 * Diagnóstico rápido y corrección del campo activo
 */

require_once "model/conexion.php";

echo "<h1>🔧 Diagnóstico y Corrección del Campo Activo</h1>";

try {
    $conexion = Conexion::conectar();
    
    // 1. Verificar si existe el campo activo
    echo "<h2>1. Verificando campo 'activo'</h2>";
    $stmt = $conexion->prepare("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'servicios_reservas' 
        AND column_name = 'activo'
    ");
    $stmt->execute();
    $existeCampo = $stmt->fetchColumn();
    
    if ($existeCampo) {
        echo "<p style='color: green;'>✅ El campo 'activo' YA EXISTE</p>";
        
        // Verificar datos actuales
        $stmt = $conexion->prepare("SELECT COUNT(*) as total, COUNT(CASE WHEN activo = true THEN 1 END) as activas FROM servicios_reservas");
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>📊 Estadísticas: {$stats['total']} reservas totales, {$stats['activas']} activas</p>";
        
    } else {
        echo "<p style='color: red;'>❌ El campo 'activo' NO EXISTE - Creándolo...</p>";
        
        try {
            // Crear el campo
            $conexion->exec("ALTER TABLE servicios_reservas ADD COLUMN activo BOOLEAN NOT NULL DEFAULT TRUE");
            echo "<p style='color: green;'>✅ Campo 'activo' creado exitosamente</p>";
            
            // Crear índice
            $conexion->exec("CREATE INDEX IF NOT EXISTS idx_reservas_activo ON servicios_reservas (activo)");
            echo "<p style='color: green;'>✅ Índice creado exitosamente</p>";
            
            // Actualizar registros existentes
            $stmt = $conexion->prepare("UPDATE servicios_reservas SET activo = TRUE WHERE activo IS NULL");
            $stmt->execute();
            $affected = $stmt->rowCount();
            echo "<p style='color: green;'>✅ $affected registros actualizados a activo = TRUE</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error al crear campo: " . $e->getMessage() . "</p>";
        }
    }
    
    // 2. Test de cancelación
    echo "<h2>2. Test de Cancelación</h2>";
    
    if (isset($_GET['action']) && $_GET['action'] == 'test_cancel') {
        echo "<p>🧪 Ejecutando test de cancelación en reserva 114...</p>";
        
        try {
            // Verificar estado antes
            $stmt = $conexion->prepare("SELECT reserva_id, activo, reserva_estado FROM servicios_reservas WHERE reserva_id = 114");
            $stmt->execute();
            $antes = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($antes) {
                echo "<p><strong>Estado ANTES:</strong></p>";
                echo "<table border='1'>";
                foreach ($antes as $k => $v) {
                    echo "<tr><td>$k</td><td>$v</td></tr>";
                }
                echo "</table>";
                
                // Ejecutar cancelación manual
                $stmt = $conexion->prepare("
                    UPDATE servicios_reservas 
                    SET activo = false, 
                        reserva_estado = 'CANCELADA',
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE reserva_id = 114
                ");
                $resultado = $stmt->execute();
                $filas = $stmt->rowCount();
                
                if ($resultado && $filas > 0) {
                    echo "<p style='color: green;'>✅ UPDATE ejecutado - $filas filas afectadas</p>";
                    
                    // Verificar estado después
                    $stmt = $conexion->prepare("SELECT reserva_id, activo, reserva_estado FROM servicios_reservas WHERE reserva_id = 114");
                    $stmt->execute();
                    $despues = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    echo "<p><strong>Estado DESPUÉS:</strong></p>";
                    echo "<table border='1'>";
                    foreach ($despues as $k => $v) {
                        $color = ($k == 'activo' && ($v == 'f' || $v == false)) ? 'style="background-color: lightgreen;"' : '';
                        echo "<tr $color><td>$k</td><td>$v</td></tr>";
                    }
                    echo "</table>";
                    
                } else {
                    echo "<p style='color: red;'>❌ Error en UPDATE o no se afectaron filas</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ No se encontró reserva con ID 114</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error en test: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p><a href='?action=test_cancel' style='background: orange; color: white; padding: 10px; text-decoration: none; border-radius: 4px;'>🧪 Ejecutar Test de Cancelación</a></p>";
    }
    
    // 3. Estado actual de la reserva 114
    echo "<h2>3. Estado Actual de Reserva 114</h2>";
    $stmt = $conexion->prepare("SELECT * FROM servicios_reservas WHERE reserva_id = 114");
    $stmt->execute();
    $reserva114 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($reserva114) {
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Valor</th><th>Estado</th></tr>";
        foreach ($reserva114 as $campo => $valor) {
            $estado = '';
            if ($campo == 'activo') {
                $estado = ($valor == 't' || $valor == true) ? '⚠️ ACTIVA' : '✅ CANCELADA';
            } elseif ($campo == 'reserva_estado') {
                $estado = ($valor == 'CANCELADA') ? '✅ CANCELADA' : '⚠️ ' . $valor;
            }
            echo "<tr><td><strong>$campo</strong></td><td>$valor</td><td>$estado</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ No existe reserva con ID 114</p>";
    }
    
    // 4. Test de consulta con filtro
    echo "<h2>4. Test de Consulta con Filtro</h2>";
    
    $stmt = $conexion->prepare("
        SELECT reserva_id, activo, reserva_estado, 
               CASE WHEN activo = true THEN 'VISIBLE' ELSE 'OCULTA' END as visibilidad
        FROM servicios_reservas 
        WHERE fecha_reserva = '2025-09-22'
        ORDER BY reserva_id
    ");
    $stmt->execute();
    $todasReservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Todas las reservas del 2025-09-22:</strong></p>";
    if ($todasReservas) {
        echo "<table border='1'><tr><th>ID</th><th>Activo</th><th>Estado</th><th>Visibilidad</th></tr>";
        foreach ($todasReservas as $r) {
            $bgColor = ($r['visibilidad'] == 'OCULTA') ? 'background-color: #ffcccc;' : '';
            echo "<tr style='$bgColor'>";
            echo "<td>{$r['reserva_id']}</td>";
            echo "<td>{$r['activo']}</td>";
            echo "<td>{$r['reserva_estado']}</td>";
            echo "<td><strong>{$r['visibilidad']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No hay reservas para esa fecha</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>💥 Error: " . $e->getMessage() . "</p>";
}

?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; margin: 10px 0; width: 100%; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 5px; margin-top: 30px; }
    .error { color: red; }
    .success { color: green; }
    .warning { color: orange; }
</style>
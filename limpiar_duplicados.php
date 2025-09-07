<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "model/conexion.php";

echo "<h3>Limpieza de Duplicados en rs_servicios_doctors</h3>";

try {
    $pdo = Conexion::conectar();
    
    // Verificar estado inicial
    echo "<h4>Estado inicial:</h4>";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rs_servicios_doctors");
    $stmt->execute();
    $totalInicial = $stmt->fetchColumn();
    echo "Total de registros: {$totalInicial}<br>";
    
    // Buscar duplicados por agenda_detalle_id
    $stmt = $pdo->prepare("
        SELECT agenda_detalle_id, COUNT(*) as duplicados
        FROM rs_servicios_doctors 
        GROUP BY agenda_detalle_id 
        HAVING COUNT(*) > 1
        ORDER BY duplicados DESC
    ");
    $stmt->execute();
    $duplicados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Duplicados encontrados:</h4>";
    if (empty($duplicados)) {
        echo "✅ No se encontraron duplicados<br>";
    } else {
        echo "❌ Se encontraron " . count($duplicados) . " grupos de duplicados:<br>";
        foreach ($duplicados as $dup) {
            echo "- agenda_detalle_id {$dup['agenda_detalle_id']}: {$dup['duplicados']} registros<br>";
        }
        
        echo "<h4>Limpiando duplicados:</h4>";
        
        // Para cada grupo de duplicados, mantener solo el más reciente
        foreach ($duplicados as $dup) {
            $detalle_id = $dup['agenda_detalle_id'];
            
            // Obtener todos los registros de este detalle_id ordenados por ID (el más alto es el más reciente)
            $stmt = $pdo->prepare("
                SELECT id, servicio_id, doctor_id 
                FROM rs_servicios_doctors 
                WHERE agenda_detalle_id = ? 
                ORDER BY id DESC
            ");
            $stmt->execute([$detalle_id]);
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($registros) > 1) {
                // Mantener solo el primero (más reciente), eliminar el resto
                $mantener = array_shift($registros); // Quitar el primero del array
                
                echo "Manteniendo registro ID {$mantener['id']} para detalle_id {$detalle_id}<br>";
                
                foreach ($registros as $eliminar) {
                    $stmt = $pdo->prepare("DELETE FROM rs_servicios_doctors WHERE id = ?");
                    $stmt->execute([$eliminar['id']]);
                    echo "- Eliminado registro ID {$eliminar['id']}<br>";
                }
            }
        }
    }
    
    // Verificar estado final
    echo "<h4>Estado final:</h4>";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM rs_servicios_doctors");
    $stmt->execute();
    $totalFinal = $stmt->fetchColumn();
    echo "Total de registros: {$totalFinal}<br>";
    
    $eliminados = $totalInicial - $totalFinal;
    if ($eliminados > 0) {
        echo "✅ Se eliminaron {$eliminados} registros duplicados<br>";
    }
    
    // Verificar que no hay duplicados
    $stmt = $pdo->prepare("
        SELECT agenda_detalle_id, COUNT(*) as cantidad
        FROM rs_servicios_doctors 
        GROUP BY agenda_detalle_id 
        HAVING COUNT(*) > 1
    ");
    $stmt->execute();
    $duplicadosRestantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicadosRestantes)) {
        echo "✅ Base de datos limpia: No hay duplicados<br>";
    } else {
        echo "❌ Aún hay duplicados:<br>";
        foreach ($duplicadosRestantes as $dup) {
            echo "- agenda_detalle_id {$dup['agenda_detalle_id']}: {$dup['cantidad']} registros<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>
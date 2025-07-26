<?php
require_once 'model/conexion.php';

try {
    $conn = Conexion::conectar();
    
    // Mapeo de IDs a nombres según la tabla tipos_formularios
    $mapeoIdANombre = [
        '1' => 'General',
        '2' => 'Anteojos',
        '3' => 'Dermatología', 
        '4' => 'Informe + Imagen',
        '5' => 'Estudios Médicos',
        '6' => 'Ginecología + 1',
        '7' => 'pediatria',
        '18' => 'General' // Asumiendo que 18 no existe en tipos_formularios, lo mapeamos a General
    ];
    
    echo "<h2>Migración de tipo_formulario de IDs a nombres</h2>";
    
    // Obtener todos los preformatos que tienen tipo_formulario numérico
    $stmt = $conn->prepare('SELECT id_preformato, nombre, tipo_formulario FROM preformatos WHERE tipo_formulario ~ \'^[0-9]+$\'');
    $stmt->execute();
    $preformatosConId = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Preformatos a migrar:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>ID Preformato</th><th>Nombre</th><th>Tipo Formulario Actual</th><th>Nuevo Tipo Formulario</th><th>Acción</th></tr>";
    
    $conn->beginTransaction();
    $migracionesRealizadas = 0;
    
    foreach ($preformatosConId as $preformato) {
        $idActual = $preformato['tipo_formulario'];
        $nuevoNombre = isset($mapeoIdANombre[$idActual]) ? $mapeoIdANombre[$idActual] : 'General';
        
        echo "<tr>";
        echo "<td>" . $preformato['id_preformato'] . "</td>";
        echo "<td>" . $preformato['nombre'] . "</td>";
        echo "<td>" . $idActual . "</td>";
        echo "<td>" . $nuevoNombre . "</td>";
        
        // Actualizar el registro
        $updateStmt = $conn->prepare('UPDATE preformatos SET tipo_formulario = ? WHERE id_preformato = ?');
        $resultado = $updateStmt->execute([$nuevoNombre, $preformato['id_preformato']]);
        
        if ($resultado) {
            echo "<td style='color: green;'>✅ Actualizado</td>";
            $migracionesRealizadas++;
        } else {
            echo "<td style='color: red;'>❌ Error</td>";
        }
        echo "</tr>";
    }
    
    echo "</table>";
    
    $conn->commit();
    
    echo "<h3>Resumen:</h3>";
    echo "<p><strong>Total de registros migrados:</strong> $migracionesRealizadas</p>";
    
    // Verificar el estado después de la migración
    echo "<h3>Estado después de la migración:</h3>";
    $stmt = $conn->prepare('SELECT tipo_formulario, COUNT(*) as cantidad FROM preformatos WHERE activo = true GROUP BY tipo_formulario ORDER BY tipo_formulario');
    $stmt->execute();
    $gruposDespues = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Tipo Formulario</th><th>Cantidad de Preformatos</th></tr>";
    foreach ($gruposDespues as $grupo) {
        echo "<tr>";
        echo "<td>" . $grupo['tipo_formulario'] . "</td>";
        echo "<td>" . $grupo['cantidad'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo "<h3 style='color: red;'>Error durante la migración:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

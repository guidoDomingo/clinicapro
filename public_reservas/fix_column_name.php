<?php
/**
 * Script para corregir el nombre de columna en reserva_archivos
 * Cambiar tamaño_archivo por tamano_archivo
 */

require_once '../model/conexion.php';

echo "<h2>🔧 Corrección de Tabla reserva_archivos</h2>";

try {
    $pdo = Conexion::conectar();
    
    // Verificar estructura actual
    echo "<h3>📋 1. Estructura actual de la tabla:</h3>";
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'reserva_archivos'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['column_name'] . "</td>";
        echo "<td>" . $column['data_type'] . "</td>";
        echo "<td>" . $column['is_nullable'] . "</td>";
        echo "<td>" . ($column['column_default'] ?: 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar si existe la columna con ñ
    $hasOldColumn = false;
    foreach ($columns as $column) {
        if ($column['column_name'] === 'tamaño_archivo') {
            $hasOldColumn = true;
            break;
        }
    }
    
    echo "<h3>🔍 2. Análisis:</h3>";
    if ($hasOldColumn) {
        echo "<p>✅ Se encontró la columna 'tamaño_archivo' que necesita ser renombrada</p>";
        
        echo "<h3>⚙️ 3. Ejecutando ALTER TABLE...</h3>";
        
        // Realizar el cambio
        $alterSQL = "ALTER TABLE reserva_archivos RENAME COLUMN tamaño_archivo TO tamano_archivo";
        
        if ($pdo->exec($alterSQL)) {
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>¡Éxito!</strong> La columna 'tamaño_archivo' ha sido renombrada a 'tamano_archivo'";
            echo "</div>";
            
            // Verificar el cambio
            echo "<h3>✅ 4. Verificación del cambio:</h3>";
            $stmt = $pdo->prepare("
                SELECT column_name, data_type
                FROM information_schema.columns 
                WHERE table_name = 'reserva_archivos' AND column_name IN ('tamaño_archivo', 'tamano_archivo')
                ORDER BY column_name
            ");
            $stmt->execute();
            $updatedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($updatedColumns)) {
                echo "<p>❌ No se encontró ninguna de las dos columnas</p>";
            } else {
                echo "<table border='1' style='border-collapse: collapse;'>";
                echo "<tr style='background: #f0f0f0;'><th>Columna</th><th>Tipo</th></tr>";
                foreach ($updatedColumns as $col) {
                    echo "<tr>";
                    echo "<td>" . $col['column_name'] . "</td>";
                    echo "<td>" . $col['data_type'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ <strong>Error:</strong> No se pudo ejecutar el ALTER TABLE";
            echo "</div>";
        }
        
    } else {
        echo "<p>⚠️ No se encontró la columna 'tamaño_archivo'. Verificando si ya existe 'tamano_archivo'...</p>";
        
        $hasNewColumn = false;
        foreach ($columns as $column) {
            if ($column['column_name'] === 'tamano_archivo') {
                $hasNewColumn = true;
                break;
            }
        }
        
        if ($hasNewColumn) {
            echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>¡Perfecto!</strong> La columna 'tamano_archivo' ya existe. No es necesario hacer cambios.";
            echo "</div>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ <strong>Problema:</strong> No se encontró ni 'tamaño_archivo' ni 'tamano_archivo'";
            echo "</div>";
        }
    }
    
    // Test de inserción después del cambio
    echo "<h3>🧪 5. Test de inserción:</h3>";
    try {
        $testData = [
            'reserva_id' => 999,
            'codigo_seguimiento' => 'TEST_ALTER_' . time(),
            'nombre_original' => 'test_alter.pdf',
            'nombre_archivo' => 'test_alter_' . time() . '.pdf',
            'ruta_archivo' => '/test/path/test_alter.pdf',
            'tipo_archivo' => 'pdf',
            'tamano_archivo' => 2048,
            'subido_por' => 1,
            'estado' => 'ACTIVO'
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO reserva_archivos 
            (reserva_id, codigo_seguimiento, nombre_original, nombre_archivo, 
             ruta_archivo, tipo_archivo, tamano_archivo, subido_por, estado)
            VALUES 
            (:reserva_id, :codigo_seguimiento, :nombre_original, :nombre_archivo,
             :ruta_archivo, :tipo_archivo, :tamano_archivo, :subido_por, :estado)
            RETURNING archivo_id
        ");
        
        $stmt->bindParam(":reserva_id", $testData['reserva_id'], PDO::PARAM_INT);
        $stmt->bindParam(":codigo_seguimiento", $testData['codigo_seguimiento'], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_original", $testData['nombre_original'], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_archivo", $testData['nombre_archivo'], PDO::PARAM_STR);
        $stmt->bindParam(":ruta_archivo", $testData['ruta_archivo'], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_archivo", $testData['tipo_archivo'], PDO::PARAM_STR);
        $stmt->bindParam(":tamano_archivo", $testData['tamano_archivo'], PDO::PARAM_INT);
        $stmt->bindParam(":subido_por", $testData['subido_por'], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $testData['estado'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "✅ <strong>Test exitoso!</strong> Se insertó registro con ID: " . $result['archivo_id'];
            echo "</div>";
            
            // Eliminar el registro de prueba
            $deleteStmt = $pdo->prepare("DELETE FROM reserva_archivos WHERE archivo_id = :id");
            $deleteStmt->bindParam(":id", $result['archivo_id'], PDO::PARAM_INT);
            $deleteStmt->execute();
            echo "<p>🗑️ Registro de prueba eliminado</p>";
            
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "❌ <strong>Error en test:</strong> No se pudo insertar el registro";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ <strong>Error en test:</strong> " . $e->getMessage();
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "❌ <strong>Error de conexión:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔗 Enlaces útiles</h3>";
echo "<ul>";
echo "<li><a href='test_upload_real.php'>🧪 Probar upload después del cambio</a></li>";
echo "<li><a href='verificar_tabla_archivos.php'>🔍 Verificar tabla de archivos</a></li>";
echo "</ul>";
?>

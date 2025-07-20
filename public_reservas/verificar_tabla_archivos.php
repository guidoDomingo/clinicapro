<?php
/**
 * Verificar estado de la tabla reserva_archivos
 */

require_once '../model/conexion.php';

echo "<h2>Verificación de tabla reserva_archivos</h2>";

try {
    $pdo = Conexion::conectar();
    
    // Verificar si la tabla existe
    echo "<h3>1. Verificar si existe la tabla:</h3>";
    $stmt = $pdo->prepare("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name = 'reserva_archivos'
    ");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "✅ La tabla 'reserva_archivos' existe<br>";
        
        // Verificar estructura de la tabla
        echo "<h3>2. Estructura de la tabla:</h3>";
        $stmt = $pdo->prepare("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns 
            WHERE table_name = 'reserva_archivos'
            ORDER BY ordinal_position
        ");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1'>";
        echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['column_name'] . "</td>";
            echo "<td>" . $column['data_type'] . "</td>";
            echo "<td>" . $column['is_nullable'] . "</td>";
            echo "<td>" . ($column['column_default'] ?: 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si hay datos
        echo "<h3>3. Datos en la tabla:</h3>";
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM reserva_archivos");
        $stmt->execute();
        $count = $stmt->fetch();
        echo "Total de registros: " . $count['total'] . "<br>";
        
        if ($count['total'] > 0) {
            echo "<h4>Últimos 5 registros:</h4>";
            $stmt = $pdo->prepare("
                SELECT archivo_id, reserva_id, nombre_original, fecha_subida, estado 
                FROM reserva_archivos 
                ORDER BY fecha_subida DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Reserva ID</th><th>Archivo</th><th>Fecha</th><th>Estado</th></tr>";
            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>" . $record['archivo_id'] . "</td>";
                echo "<td>" . $record['reserva_id'] . "</td>";
                echo "<td>" . $record['nombre_original'] . "</td>";
                echo "<td>" . $record['fecha_subida'] . "</td>";
                echo "<td>" . $record['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "❌ La tabla 'reserva_archivos' NO existe<br>";
        echo "<h3>Creando tabla...</h3>";
        
        $sql = "
        CREATE TABLE reserva_archivos (
            archivo_id SERIAL PRIMARY KEY,
            reserva_id INT NOT NULL,
            codigo_seguimiento VARCHAR(50),
            nombre_original VARCHAR(255) NOT NULL,
            nombre_archivo VARCHAR(255) NOT NULL,
            ruta_archivo TEXT NOT NULL,
            tipo_archivo VARCHAR(50),
            tamaño_archivo INT,
            fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            subido_por INT,
            estado VARCHAR(20) DEFAULT 'ACTIVO',
            
            FOREIGN KEY (reserva_id) REFERENCES servicios_reservas(reserva_id) ON DELETE CASCADE,
            FOREIGN KEY (subido_por) REFERENCES sys_users(user_id) ON DELETE SET NULL
        );
        
        CREATE INDEX idx_reserva_archivos_reserva_id ON reserva_archivos(reserva_id);
        CREATE INDEX idx_reserva_archivos_codigo ON reserva_archivos(codigo_seguimiento);
        CREATE INDEX idx_reserva_archivos_estado ON reserva_archivos(estado);
        ";
        
        if ($pdo->exec($sql)) {
            echo "✅ Tabla 'reserva_archivos' creada exitosamente<br>";
        } else {
            echo "❌ Error creando la tabla<br>";
        }
    }
    
    // Test de inserción
    echo "<h3>4. Test de inserción:</h3>";
    try {
        $testData = [
            'reserva_id' => 1,
            'codigo_seguimiento' => 'TEST123',
            'nombre_original' => 'test.pdf',
            'nombre_archivo' => 'test_123.pdf',
            'ruta_archivo' => '/test/path/test_123.pdf',
            'tipo_archivo' => 'pdf',
            'tamaño_archivo' => 1024,
            'subido_por' => 1,
            'estado' => 'ACTIVO'
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO reserva_archivos 
            (reserva_id, codigo_seguimiento, nombre_original, nombre_archivo, 
             ruta_archivo, tipo_archivo, tamaño_archivo, subido_por, estado)
            VALUES 
            (:reserva_id, :codigo_seguimiento, :nombre_original, :nombre_archivo,
             :ruta_archivo, :tipo_archivo, :tamaño_archivo, :subido_por, :estado)
            RETURNING archivo_id
        ");
        
        $stmt->bindParam(":reserva_id", $testData['reserva_id'], PDO::PARAM_INT);
        $stmt->bindParam(":codigo_seguimiento", $testData['codigo_seguimiento'], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_original", $testData['nombre_original'], PDO::PARAM_STR);
        $stmt->bindParam(":nombre_archivo", $testData['nombre_archivo'], PDO::PARAM_STR);
        $stmt->bindParam(":ruta_archivo", $testData['ruta_archivo'], PDO::PARAM_STR);
        $stmt->bindParam(":tipo_archivo", $testData['tipo_archivo'], PDO::PARAM_STR);
        $stmt->bindParam(":tamaño_archivo", $testData['tamaño_archivo'], PDO::PARAM_INT);
        $stmt->bindParam(":subido_por", $testData['subido_por'], PDO::PARAM_INT);
        $stmt->bindParam(":estado", $testData['estado'], PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Test de inserción exitoso. ID generado: " . $result['archivo_id'] . "<br>";
            
            // Eliminar el registro de prueba
            $deleteStmt = $pdo->prepare("DELETE FROM reserva_archivos WHERE archivo_id = :id");
            $deleteStmt->bindParam(":id", $result['archivo_id'], PDO::PARAM_INT);
            $deleteStmt->execute();
            echo "✅ Registro de prueba eliminado<br>";
        } else {
            echo "❌ Error en test de inserción<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error en test de inserción: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "<br>";
}
?>

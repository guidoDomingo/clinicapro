<?php
// Verificar estructura de tablas para servicios por doctor

$baseDir = dirname(__FILE__);
require_once $baseDir . "/model/conexion.php";

echo "<h2>Estructura de Tablas - Servicios por Doctor</h2>";

$tablas = ['rs_servicios', 'rs_servicios_doctors', 'rh_doctors', 'persons'];

try {
    $conexion = Conexion::conectar();
    
    foreach ($tablas as $tabla) {
        echo "<h3>Tabla: {$tabla}</h3>";
        
        // Verificar si la tabla existe
        $stmt = $conexion->prepare("
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_schema = 'public' 
                AND table_name = :tabla
            )
        ");
        $stmt->bindParam(':tabla', $tabla);
        $stmt->execute();
        $existe = $stmt->fetchColumn();
        
        if (!$existe) {
            echo "<p style='color: red;'>❌ La tabla '{$tabla}' no existe</p>";
            continue;
        }
        
        // Mostrar estructura
        $stmt = $conexion->prepare("
            SELECT column_name, data_type, is_nullable, column_default
            FROM information_schema.columns 
            WHERE table_name = :tabla 
            ORDER BY ordinal_position
        ");
        $stmt->bindParam(':tabla', $tabla);
        $stmt->execute();
        $columnas = $stmt->fetchAll();
        
        if (count($columnas) > 0) {
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Por Defecto</th></tr>";
            foreach ($columnas as $columna) {
                echo "<tr>";
                echo "<td>" . $columna['column_name'] . "</td>";
                echo "<td>" . $columna['data_type'] . "</td>";
                echo "<td>" . $columna['is_nullable'] . "</td>";
                echo "<td>" . ($columna['column_default'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Mostrar algunos datos de ejemplo
            $stmt = $conexion->prepare("SELECT * FROM {$tabla} LIMIT 5");
            $stmt->execute();
            $datos = $stmt->fetchAll();
            
            if (count($datos) > 0) {
                echo "<p><strong>Datos de ejemplo:</strong></p>";
                echo "<pre>" . print_r($datos, true) . "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠️ No hay datos en la tabla</p>";
            }
        }
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
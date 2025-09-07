<?php
// Buscar tabla de personas

$baseDir = dirname(__FILE__);
require_once $baseDir . "/model/conexion.php";

echo "<h2>Buscar Tabla de Personas</h2>";

try {
    $conexion = Conexion::conectar();
    
    // Buscar tablas que contengan "person" o "persona"
    $stmt = $conexion->prepare("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND (table_name ILIKE '%person%' OR table_name ILIKE '%persona%')
        ORDER BY table_name
    ");
    $stmt->execute();
    $tablas = $stmt->fetchAll();
    
    echo "<h3>Tablas relacionadas con personas:</h3>";
    foreach ($tablas as $tabla) {
        echo "<p>- " . $tabla['table_name'] . "</p>";
    }
    
    // Probar algunas consultas para encontrar los datos de persona
    $tablasCandidatas = ['persons', 'rh_person', 'person', 'personas'];
    
    foreach ($tablasCandidatas as $tabla) {
        echo "<h3>Probando tabla: {$tabla}</h3>";
        
        try {
            $stmt = $conexion->prepare("SELECT * FROM {$tabla} LIMIT 2");
            $stmt->execute();
            $datos = $stmt->fetchAll();
            
            if (count($datos) > 0) {
                echo "<p style='color: green;'>✅ Tabla encontrada con datos</p>";
                echo "<pre>" . print_r($datos[0], true) . "</pre>";
                
                // Mostrar estructura
                $stmt = $conexion->prepare("
                    SELECT column_name, data_type
                    FROM information_schema.columns 
                    WHERE table_name = :tabla 
                    ORDER BY ordinal_position
                ");
                $stmt->bindParam(':tabla', $tabla);
                $stmt->execute();
                $columnas = $stmt->fetchAll();
                
                echo "<p><strong>Columnas:</strong></p>";
                foreach ($columnas as $columna) {
                    echo "<p>- {$columna['column_name']} ({$columna['data_type']})</p>";
                }
                break;
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error o tabla no existe: " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
<?php
// Probar consulta directa con datos conocidos

$baseDir = dirname(__FILE__);
require_once $baseDir . "/model/conexion.php";

echo "<h2>Probar Consulta con Datos Conocidos</h2>";

try {
    $conexion = Conexion::conectar();
    
    // Sabemos que el doctor_id 18 tiene person_id 58
    // Probemos varias tablas para encontrar el nombre
    
    $tablasCandidatas = ['rh_person', 'person', 'personas', 'rh_persons'];
    
    foreach ($tablasCandidatas as $tabla) {
        echo "<h3>Probando tabla: {$tabla}</h3>";
        
        try {
            // Buscar person_id 58
            $stmt = $conexion->prepare("SELECT * FROM {$tabla} WHERE person_id = 58 OR id = 58");
            $stmt->execute();
            $datos = $stmt->fetchAll();
            
            if (count($datos) > 0) {
                echo "<p style='color: green;'>✅ Datos encontrados:</p>";
                echo "<pre>" . print_r($datos, true) . "</pre>";
            } else {
                echo "<p style='color: orange;'>⚠️ No se encontraron datos</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
    
    // También probemos una consulta que ya sabemos que funciona
    echo "<h3>Consulta que funciona (getMedicos):</h3>";
    try {
        $stmt = $conexion->prepare("
            SELECT d.doctor_id, d.person_id, 
                   CONCAT(p.nombre, ' ', p.apellido) as nombre_completo
            FROM rh_doctors d
            INNER JOIN persons p ON d.person_id = p.id
            WHERE d.doctor_estado = 'ACTIVO'
            ORDER BY p.nombre, p.apellido
        ");
        $stmt->execute();
        $doctores = $stmt->fetchAll();
        
        echo "<p style='color: green;'>✅ Consulta exitosa:</p>";
        echo "<pre>" . print_r($doctores, true) . "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en consulta conocida: " . $e->getMessage() . "</p>";
        
        // Intentemos sin el JOIN
        try {
            $stmt = $conexion->prepare("
                SELECT doctor_id, person_id
                FROM rh_doctors 
                WHERE doctor_estado = 'ACTIVO'
                ORDER BY doctor_id
            ");
            $stmt->execute();
            $doctores = $stmt->fetchAll();
            
            echo "<p style='color: blue;'>📋 Doctores sin nombres:</p>";
            echo "<pre>" . print_r($doctores, true) . "</pre>";
        } catch (Exception $e2) {
            echo "<p style='color: red;'>❌ Error incluso sin JOIN: " . $e2->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
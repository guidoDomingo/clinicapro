<?php
// Test simple de búsqueda de pacientes
require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    
    // Búsqueda similar a la del API
    $searchSQL = "SELECT person_id, first_name, last_name, document_number, phone_number 
                  FROM rh_person 
                  WHERE first_name ILIKE :search_term 
                  ORDER BY first_name ASC 
                  LIMIT 10";
    
    $stmt = $conexion->prepare($searchSQL);
    $searchTerm = '%alejandro%';  // El nombre que probábamos
    $stmt->bindParam(':search_term', $searchTerm, PDO::PARAM_STR);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Búsqueda de pacientes con 'alejandro':</h2>";
    
    if (count($results) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>CI</th><th>Teléfono</th></tr>";
        
        foreach ($results as $row) {
            echo "<tr>";
            echo "<td>{$row['person_id']}</td>";
            echo "<td>{$row['first_name']}</td>";
            echo "<td>{$row['last_name']}</td>";
            echo "<td>{$row['document_number']}</td>";
            echo "<td>{$row['phone_number']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Como JSON:</h3>";
        echo "<pre>" . json_encode($results, JSON_PRETTY_PRINT) . "</pre>";
        
    } else {
        echo "<p>No se encontraron resultados</p>";
        
        // Probar con otro nombre que sabemos que existe
        $searchTerm2 = '%andres%';
        $stmt->bindParam(':search_term', $searchTerm2, PDO::PARAM_STR);
        $stmt->execute();
        $results2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Búsqueda con 'andres':</h3>";
        echo "<pre>" . json_encode($results2, JSON_PRETTY_PRINT) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
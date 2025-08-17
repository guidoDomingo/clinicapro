<?php
// Diagnóstico de pacientes en la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Diagnóstico de Pacientes en la Base de Datos</h2>";

try {
    // Incluir archivos de conexión
    require_once "controller/conexion.controller.php";
    
    $pdo = Conexion::conectar();
    
    echo "<h3>✅ Conexión establecida correctamente</h3>";
    
    // Contar total de personas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rh_person");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total de personas en la base de datos:</strong> " . $total['total'] . "</p>";
    
    // Buscar personas con 'vis' en el nombre
    echo "<h3>Búsqueda de personas con 'vis':</h3>";
    $stmt = $pdo->prepare("SELECT person_id, first_name, last_name, document_number, record_number FROM rh_person WHERE LOWER(first_name) LIKE LOWER(?) OR LOWER(last_name) LIKE LOWER(?) LIMIT 10");
    $stmt->execute(['%vis%', '%vis%']);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($resultados) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>Documento</th><th>Ficha</th></tr>";
        foreach ($resultados as $persona) {
            echo "<tr>";
            echo "<td>" . $persona['person_id'] . "</td>";
            echo "<td>" . $persona['first_name'] . "</td>";
            echo "<td>" . $persona['last_name'] . "</td>";
            echo "<td>" . ($persona['document_number'] ?: 'Sin documento') . "</td>";
            echo "<td>" . ($persona['record_number'] ?: 'Sin ficha') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron personas con 'vis' en el nombre.</p>";
    }
    
    // Mostrar algunas personas de ejemplo
    echo "<h3>Primeras 10 personas en la base de datos:</h3>";
    $stmt = $pdo->query("SELECT person_id, first_name, last_name, document_number, record_number FROM rh_person LIMIT 10");
    $ejemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($ejemplos) {
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ID</th><th>Nombres</th><th>Apellidos</th><th>Documento</th><th>Ficha</th></tr>";
        foreach ($ejemplos as $persona) {
            echo "<tr>";
            echo "<td>" . $persona['person_id'] . "</td>";
            echo "<td>" . $persona['first_name'] . "</td>";
            echo "<td>" . $persona['last_name'] . "</td>";
            echo "<td>" . ($persona['document_number'] ?: 'Sin documento') . "</td>";
            echo "<td>" . ($persona['record_number'] ?: 'Sin ficha') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Trace:</strong> " . $e->getTraceAsString() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; margin: 10px 0; }
th { background-color: #f0f0f0; }
h2, h3 { color: #333; }
p { margin: 10px 0; }
</style>

<?php
// Script temporal para depurar los datos del paciente 45
require_once "model/conexion.php";

try {
    $conn = Conexion::conectar();
    
    $stmt = $conn->prepare("
        SELECT 
            person_id,
            document_number,
            record_number,
            first_name,
            last_name,
            birth_date,
            phone_number,
            email
        FROM 
            public.rh_person 
        WHERE 
            person_id = :id_persona
    ");
    
    $stmt->bindParam(":id_persona", $paciente_id = 45, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Datos del paciente ID 45:</h2>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    if ($result) {
        echo "<h3>Análisis:</h3>";
        echo "<ul>";
        echo "<li>Nombre: '" . $result['first_name'] . "'</li>";
        echo "<li>Apellido: '" . $result['last_name'] . "'</li>";
        echo "<li>Apellido es NULL: " . (is_null($result['last_name']) ? 'SÍ' : 'NO') . "</li>";
        echo "<li>Apellido está vacío: " . (empty($result['last_name']) ? 'SÍ' : 'NO') . "</li>";
        echo "</ul>";
        
        echo "<h3>Nombre completo construido:</h3>";
        $nombre_completo = trim($result['first_name'] . ' ' . $result['last_name']);
        echo "<p><strong>'" . $nombre_completo . "'</strong></p>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

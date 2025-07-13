<?php
// Script para actualizar el apellido del paciente 45
require_once "model/conexion.php";

try {
    $conn = Conexion::conectar();
    
    // Primero verificar los datos actuales
    echo "<h2>Datos ANTES de la actualización:</h2>";
    $stmt = $conn->prepare("SELECT person_id, first_name, last_name FROM public.rh_person WHERE person_id = 45");
    $stmt->execute();
    $antes = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($antes);
    echo "</pre>";
    
    // Actualizar el apellido a "visconte"
    $updateStmt = $conn->prepare("
        UPDATE public.rh_person 
        SET last_name = :apellido 
        WHERE person_id = :id_persona
    ");
    
    $apellido = 'visconte';
    $idPersona = 45;
    
    $updateStmt->bindParam(":apellido", $apellido, PDO::PARAM_STR);
    $updateStmt->bindParam(":id_persona", $idPersona, PDO::PARAM_INT);
    
    if ($updateStmt->execute()) {
        echo "<h2>✅ ACTUALIZACIÓN EXITOSA</h2>";
        echo "<p>Se actualizó el apellido del paciente 45 a: <strong>$apellido</strong></p>";
        
        // Verificar los datos después de la actualización
        echo "<h2>Datos DESPUÉS de la actualización:</h2>";
        $stmt = $conn->prepare("SELECT person_id, first_name, last_name FROM public.rh_person WHERE person_id = 45");
        $stmt->execute();
        $despues = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($despues);
        echo "</pre>";
        
        echo "<p><strong>Nombre completo ahora:</strong> " . $despues['first_name'] . " " . $despues['last_name'] . "</p>";
    } else {
        echo "<h2>❌ ERROR EN LA ACTUALIZACIÓN</h2>";
        echo "<p>No se pudo actualizar el apellido.</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ ERROR:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<br><br>
<a href="index.php?ruta=consultas&form_type=anteojos&paciente_id=45">🔗 Ir al formulario de anteojos con paciente 45</a>

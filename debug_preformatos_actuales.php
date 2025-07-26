<?php
require_once 'model/conexion.php';

try {
    $conn = Conexion::conectar();
    
    echo "<h2>Preformatos actuales y sus tipos de formulario:</h2>";
    $stmt = $conn->prepare('SELECT id_preformato, nombre, tipo, tipo_formulario FROM preformatos WHERE activo = true ORDER BY tipo_formulario, nombre');
    $stmt->execute();
    $preformatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tipo Formulario</th></tr>";
    foreach ($preformatos as $preformato) {
        echo "<tr>";
        echo "<td>" . $preformato['id_preformato'] . "</td>";
        echo "<td>" . $preformato['nombre'] . "</td>";
        echo "<td>" . $preformato['tipo'] . "</td>";
        echo "<td>" . $preformato['tipo_formulario'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>Grupos por tipo_formulario:</h2>";
    $stmt = $conn->prepare('SELECT tipo_formulario, COUNT(*) as cantidad FROM preformatos WHERE activo = true GROUP BY tipo_formulario ORDER BY tipo_formulario');
    $stmt->execute();
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Tipo Formulario</th><th>Cantidad de Preformatos</th></tr>";
    foreach ($grupos as $grupo) {
        echo "<tr>";
        echo "<td>" . $grupo['tipo_formulario'] . "</td>";
        echo "<td>" . $grupo['cantidad'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

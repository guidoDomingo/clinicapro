<?php
require_once 'model/conexion.php';

echo "<h2>Insertar datos de ejemplo en tipo_formularios</h2>";

try {
    $db = Conexion::conectar();
    
    // Verificar si ya existen datos
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM tipo_formularios WHERE activo = true");
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Registros activos existentes: " . $total['total'] . "</h3>";
    
    if ($total['total'] == 0) {
        echo "<h3>Insertando datos de ejemplo...</h3>";
        
        $tipos = [
            ['General', 'Formulario general', 'GEN'],
            ['Consultas', 'Formulario para consultas médicas', 'CON'],
            ['Recetas', 'Formulario para recetas médicas', 'REC'],
            ['Estudios', 'Formulario para órdenes de estudios', 'EST'],
            ['Anteojos', 'Formulario para recetas de anteojos', 'ANT'],
            ['Cirugías', 'Formulario para órdenes de cirugías', 'CIR']
        ];
        
        $stmt = $db->prepare("INSERT INTO tipo_formularios (nombre, descripcion, codigo, activo) VALUES (?, ?, ?, true)");
        
        foreach ($tipos as $tipo) {
            $stmt->execute($tipo);
            echo "✅ Insertado: " . $tipo[0] . "<br>";
        }
        
        echo "<h3>✅ Datos insertados exitosamente</h3>";
    } else {
        echo "<h3>Ya existen datos, mostrando registros:</h3>";
        
        $stmt = $db->prepare("SELECT * FROM tipo_formularios WHERE activo = true ORDER BY nombre");
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Código</th></tr>";
        foreach ($registros as $reg) {
            echo "<tr>";
            echo "<td>" . $reg['id'] . "</td>";
            echo "<td>" . $reg['nombre'] . "</td>";
            echo "<td>" . $reg['descripcion'] . "</td>";
            echo "<td>" . $reg['codigo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>

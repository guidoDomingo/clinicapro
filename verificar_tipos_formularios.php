<?php
require_once 'model/conexion.php';

echo "<h2>Verificación de Tabla tipo_formularios</h2>";

try {
    $db = Conexion::conectar();
    
    // Verificar si la tabla existe
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_name = 'tipo_formularios'");
    $stmt->execute();
    $tabla_existe = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Tabla existe: " . ($tabla_existe['total'] > 0 ? 'SÍ' : 'NO') . "</h3>";
    
    if ($tabla_existe['total'] > 0) {
        // Verificar datos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tipo_formularios");
        $stmt->execute();
        $total_registros = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Total de registros: " . $total_registros['total'] . "</h3>";
        
        // Mostrar algunos registros
        $stmt = $db->prepare("SELECT * FROM tipo_formularios LIMIT 10");
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Registros:</h3>";
        echo "<pre>";
        print_r($registros);
        echo "</pre>";
        
        // Verificar registros activos
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tipo_formularios WHERE activo = true");
        $stmt->execute();
        $activos = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Registros activos: " . $activos['total'] . "</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
?>

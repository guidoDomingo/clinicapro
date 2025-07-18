<?php
// Verificar estructura de tablas de permisos

require_once 'model/conexion.php';

try {
    $conexion = Conexion::conectar();
    echo "<h2>Estructura de tablas de permisos</h2>";
    
    // Verificar tabla permisos
    echo "<h3>Tabla 'permisos':</h3>";
    $stmt = $conexion->prepare("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'permisos' 
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($columnas) > 0) {
        echo "<table border='1'><tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Predeterminado</th></tr>";
        foreach($columnas as $col) {
            echo "<tr><td>{$col['column_name']}</td><td>{$col['data_type']}</td><td>{$col['is_nullable']}</td><td>" . ($col['column_default'] ?? 'NULL') . "</td></tr>";
        }
        echo "</table>";
        
        // Mostrar algunos permisos existentes
        echo "<h4>Permisos existentes:</h4>";
        $permisos = $conexion->prepare("SELECT * FROM permisos ORDER BY permiso_id LIMIT 10");
        $permisos->execute();
        $lista = $permisos->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1'>";
        if(count($lista) > 0) {
            $headers = array_keys($lista[0]);
            echo "<tr>";
            foreach($headers as $header) {
                echo "<th>$header</th>";
            }
            echo "</tr>";
            
            foreach($lista as $permiso) {
                echo "<tr>";
                foreach($permiso as $valor) {
                    echo "<td>$valor</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        
    } else {
        echo "<p>La tabla 'permisos' no existe</p>";
    }
    
    // Verificar si existe el permiso administrar_turnos
    echo "<h3>Verificar permiso 'administrar_turnos':</h3>";
    
    // Probar con diferentes nombres de columna
    $queries = [
        "SELECT * FROM permisos WHERE nombre = 'administrar_turnos'",
        "SELECT * FROM permisos WHERE permiso_nombre = 'administrar_turnos'"
    ];
    
    foreach($queries as $query) {
        try {
            $stmt = $conexion->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($result) {
                echo "<p style='color: green;'>✅ Encontrado con query: $query</p>";
                var_dump($result);
                break;
            } else {
                echo "<p>❌ No encontrado con query: $query</p>";
            }
        } catch(Exception $e) {
            echo "<p style='color: red;'>Error con query '$query': " . $e->getMessage() . "</p>";
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

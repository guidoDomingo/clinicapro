<?php
/**
 * Script para explorar la estructura de rs_servicios
 */

// Configuración de la base de datos
$host = 'localhost';
$port = '5432';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Exploración de la tabla rs_servicios</h2>";
    
    echo "<h3>1. Estructura de la tabla</h3>";
    
    $stmt = $pdo->query("
        SELECT 
            column_name, 
            data_type, 
            is_nullable,
            column_default
        FROM information_schema.columns 
        WHERE table_name = 'rs_servicios' 
        ORDER BY ordinal_position
    ");
    
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th><th>Valor por defecto</th></tr>";
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td style='padding: 5px; font-weight: bold;'>{$columna['column_name']}</td>";
        echo "<td style='padding: 5px;'>{$columna['data_type']}</td>";
        echo "<td style='padding: 5px;'>{$columna['is_nullable']}</td>";
        echo "<td style='padding: 5px;'>{$columna['column_default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>2. Muestra de datos (primeros 3 registros)</h3>";
    
    $stmt = $pdo->query("SELECT * FROM rs_servicios LIMIT 3");
    $muestras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($muestras as $index => $muestra) {
        echo "<h4>Registro " . ($index + 1) . ":</h4>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        foreach ($muestra as $campo => $valor) {
            echo "<tr>";
            echo "<td style='padding: 5px; font-weight: bold;'>$campo</td>";
            echo "<td style='padding: 5px;'>$valor</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>3. Campos relacionados con duración</h3>";
    
    $camposDuracion = [];
    foreach ($columnas as $columna) {
        $nombre = strtolower($columna['column_name']);
        if (strpos($nombre, 'duracion') !== false || 
            strpos($nombre, 'tiempo') !== false || 
            strpos($nombre, 'minutos') !== false ||
            strpos($nombre, 'duration') !== false) {
            $camposDuracion[] = $columna['column_name'];
        }
    }
    
    if (!empty($camposDuracion)) {
        echo "<p><strong>Campos encontrados:</strong> " . implode(', ', $camposDuracion) . "</p>";
        
        // Mostrar valores de estos campos
        $camposStr = implode(', ', $camposDuracion);
        $stmt = $pdo->query("SELECT serv_id, serv_descripcion, $camposStr FROM rs_servicios LIMIT 5");
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Descripción</th>";
        foreach ($camposDuracion as $campo) {
            echo "<th>$campo</th>";
        }
        echo "</tr>";
        
        foreach ($datos as $dato) {
            echo "<tr>";
            echo "<td>{$dato['serv_id']}</td>";
            echo "<td>{$dato['serv_descripcion']}</td>";
            foreach ($camposDuracion as $campo) {
                echo "<td>{$dato[$campo]}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron campos relacionados con duración.</p>";
    }
    
    echo "<h3>4. Campos relacionados con categoría</h3>";
    
    $camposCategoria = [];
    foreach ($columnas as $columna) {
        $nombre = strtolower($columna['column_name']);
        if (strpos($nombre, 'categoria') !== false || 
            strpos($nombre, 'tipo') !== false || 
            strpos($nombre, 'category') !== false ||
            strpos($nombre, 'group') !== false) {
            $camposCategoria[] = $columna['column_name'];
        }
    }
    
    if (!empty($camposCategoria)) {
        echo "<p><strong>Campos encontrados:</strong> " . implode(', ', $camposCategoria) . "</p>";
    } else {
        echo "<p>No se encontraron campos relacionados con categoría.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='javascript:history.back()'>← Volver</a></p>";
?>

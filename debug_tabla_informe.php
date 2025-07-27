<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $db = new PDO("pgsql:host=localhost;dbname=clinica", "postgres", "root");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Debug Tabla consulta_informe_imagen</h1>";
    
    // Verificar estructura de la tabla
    echo "<h2>Estructura de la tabla</h2>";
    $stmt = $db->query("
        SELECT column_name, data_type, is_nullable, column_default 
        FROM information_schema.columns 
        WHERE table_name = 'consulta_informe_imagen'
        ORDER BY ordinal_position
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nullable</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['column_name']) . "</td>";
        echo "<td>" . htmlspecialchars($col['data_type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['is_nullable']) . "</td>";
        echo "<td>" . htmlspecialchars($col['column_default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar datos existentes
    echo "<h2>Últimos 5 registros</h2>";
    $stmt = $db->query("
        SELECT * FROM consulta_informe_imagen 
        ORDER BY id DESC 
        LIMIT 5
    ");
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($records)) {
        echo "<p><strong>No hay registros en la tabla</strong></p>";
    } else {
        echo "<table border='1'>";
        // Headers
        echo "<tr>";
        foreach (array_keys($records[0]) as $key) {
            echo "<th>" . htmlspecialchars($key) . "</th>";
        }
        echo "</tr>";
        
        // Data
        foreach ($records as $record) {
            echo "<tr>";
            foreach ($record as $value) {
                if (is_null($value)) {
                    echo "<td><em>NULL</em></td>";
                } elseif (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0)) {
                    // Es JSON, formatearlo
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        echo "<td><pre>" . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre></td>";
                    } else {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                } else {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar también tabla consultas
    echo "<h2>Últimos 3 registros de tabla consultas con tipo_formulario = 'informe_imagen'</h2>";
    $stmt = $db->query("
        SELECT id, consulta_textarea, tipo_formulario, datos_especificos, fecha_creacion
        FROM consultas 
        WHERE tipo_formulario = 'informe_imagen'
        ORDER BY id DESC 
        LIMIT 3
    ");
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($consultas)) {
        echo "<p><strong>No hay consultas con tipo 'informe_imagen'</strong></p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Consulta Textarea</th><th>Tipo Formulario</th><th>Datos Específicos</th><th>Fecha</th></tr>";
        foreach ($consultas as $consulta) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($consulta['id']) . "</td>";
            echo "<td>" . (empty($consulta['consulta_textarea']) ? '<em>VACÍO</em>' : htmlspecialchars($consulta['consulta_textarea'])) . "</td>";
            echo "<td>" . htmlspecialchars($consulta['tipo_formulario']) . "</td>";
            
            if (!empty($consulta['datos_especificos'])) {
                $decoded = json_decode($consulta['datos_especificos'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "<td><pre>" . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre></td>";
                } else {
                    echo "<td>" . htmlspecialchars($consulta['datos_especificos']) . "</td>";
                }
            } else {
                echo "<td><em>VACÍO</em></td>";
            }
            
            echo "<td>" . htmlspecialchars($consulta['fecha_creacion']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<p><a href='debug_problemas_formulario.html'>Volver al debug del formulario</a></p>";
    
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<?php
require_once "model/conexion.php";

echo "<h1>Análisis de Estructura de Base de Datos PostgreSQL</h1>";

try {
    $db = Conexion::conectar();
    if (!$db) {
        die("Error de conexión a la base de datos");
    }
    
    echo "<h2>1. Tablas relacionadas con consultas:</h2>";
    
    // Buscar tablas que contengan 'consulta' en PostgreSQL
    $stmt = $db->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        AND table_name ILIKE '%consulta%'
        ORDER BY table_name
    ");
    
    if ($stmt) {
        echo "<ul>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li><strong>" . htmlspecialchars($row['table_name']) . "</strong></li>";
        }
        echo "</ul>";
    }
    
    echo "<h2>2. Estructura de la tabla principal 'consultas':</h2>";
    
    // Obtener estructura de la tabla consultas
    $stmt = $db->query("
        SELECT column_name, data_type, is_nullable, column_default
        FROM information_schema.columns 
        WHERE table_name = 'consultas' 
        AND table_schema = 'public'
        ORDER BY ordinal_position
    ");
    
    if ($stmt) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Default</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['column_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['data_type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['is_nullable']) . "</td>";
            echo "<td>" . htmlspecialchars($row['column_default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>3. Buscar tablas específicas por tipo de formulario:</h2>";
    
    // Buscar tablas para diferentes tipos de formularios
    $tiposFormulario = ['anteojos', 'estudios', 'informe', 'imagen'];
    
    foreach ($tiposFormulario as $tipo) {
        echo "<h3>Tablas para '$tipo':</h3>";
        
        $stmt = $db->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name ILIKE '%$tipo%'
            ORDER BY table_name
        ");
        
        if ($stmt) {
            $tablas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($tablas) > 0) {
                echo "<ul>";
                foreach ($tablas as $tabla) {
                    echo "<li>" . htmlspecialchars($tabla['table_name']) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No se encontraron tablas específicas para '$tipo'</p>";
            }
        }
    }
    
    echo "<h2>4. Todas las tablas del sistema:</h2>";
    
    $stmt = $db->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    
    if ($stmt) {
        echo "<div style='columns: 3; column-gap: 20px;'>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<div>" . htmlspecialchars($row['table_name']) . "</div>";
        }
        echo "</div>";
    }
    
    echo "<h2>5. Datos de ejemplo de consultas:</h2>";
    
    // Obtener algunos registros de ejemplo
    $stmt = $db->query("
        SELECT id_consulta, id_persona, tipo_formulario, fecha_consulta, 
               CASE 
                   WHEN LENGTH(diagnostico) > 50 THEN LEFT(diagnostico, 50) || '...'
                   ELSE diagnostico 
               END as diagnostico_preview
        FROM consultas 
        ORDER BY id_consulta DESC 
        LIMIT 5
    ");
    
    if ($stmt) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Persona</th><th>Tipo Formulario</th><th>Fecha</th><th>Diagnóstico (preview)</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id_consulta']) . "</td>";
            echo "<td>" . htmlspecialchars($row['id_persona']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tipo_formulario'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['fecha_consulta']) . "</td>";
            echo "<td>" . htmlspecialchars($row['diagnostico_preview'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2>6. Verificar tabla de tipos de formularios:</h2>";
    
    $stmt = $db->query("
        SELECT * FROM tipos_formularios 
        ORDER BY id 
        LIMIT 10
    ");
    
    if ($stmt) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Código</th><th>Descripción</th></tr>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['codigo'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['descripcion'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>La tabla tipos_formularios no existe o está vacía</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>

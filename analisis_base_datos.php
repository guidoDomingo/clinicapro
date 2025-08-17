<?php
require_once "model/conexion.php";

echo "<h1>Análisis de Estructura de Base de Datos</h1>";
echo "<style>
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    h2 { color: #2c3e50; margin-top: 30px; }
    .table-info { background-color: #e8f4f8; padding: 10px; margin: 10px 0; }
</style>";

try {
    $db = Conexion::conectar();
    
    // 1. Obtener todas las tablas relacionadas con consultas
    echo "<h2>1. Tablas relacionadas con consultas:</h2>";
    $stmt = $db->query("SHOW TABLES LIKE '%consulta%'");
    $tablasConsultas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tablasConsultas)) {
        echo "<p>Buscando con diferentes patrones...</p>";
        $stmt = $db->query("SHOW TABLES");
        $todasTablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $patronesConsulta = ['consulta', 'anteojos', 'estudios', 'informe', 'imagen'];
        $tablasConsultas = [];
        
        foreach ($todasTablas as $tabla) {
            foreach ($patronesConsulta as $patron) {
                if (stripos($tabla, $patron) !== false) {
                    $tablasConsultas[] = $tabla;
                    break;
                }
            }
        }
    }
    
    echo "<div class='table-info'><strong>Tablas encontradas:</strong> " . implode(", ", $tablasConsultas) . "</div>";
    
    // 2. Analizar estructura de cada tabla
    foreach ($tablasConsultas as $tabla) {
        echo "<h3>Estructura de tabla: $tabla</h3>";
        
        $stmt = $db->query("DESCRIBE $tabla");
        $estructura = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($estructura as $campo) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($campo['Field']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($campo['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Default']) . "</td>";
            echo "<td>" . htmlspecialchars($campo['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar algunos registros de ejemplo
        try {
            $stmt = $db->query("SELECT * FROM $tabla LIMIT 3");
            $ejemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($ejemplos)) {
                echo "<h4>Registros de ejemplo:</h4>";
                echo "<table>";
                $headers = array_keys($ejemplos[0]);
                echo "<tr>";
                foreach ($headers as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                
                foreach ($ejemplos as $ejemplo) {
                    echo "<tr>";
                    foreach ($ejemplo as $valor) {
                        $valorMostrar = is_null($valor) ? '<em>NULL</em>' : htmlspecialchars(substr($valor, 0, 50)) . (strlen($valor) > 50 ? '...' : '');
                        echo "<td>" . $valorMostrar . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<p>Error obteniendo ejemplos: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        
        echo "<hr>";
    }
    
    // 3. Buscar tabla principal de consultas
    echo "<h2>3. Análisis de tabla principal 'consultas':</h2>";
    
    try {
        $stmt = $db->query("SELECT DISTINCT tipo_formulario FROM consultas WHERE tipo_formulario IS NOT NULL ORDER BY tipo_formulario");
        $tiposFormulario = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='table-info'><strong>Tipos de formulario encontrados:</strong> " . implode(", ", $tiposFormulario) . "</div>";
        
        // Analizar cada tipo de formulario
        foreach ($tiposFormulario as $tipo) {
            echo "<h4>Consultas tipo: $tipo</h4>";
            
            $stmt = $db->prepare("SELECT * FROM consultas WHERE tipo_formulario = ? LIMIT 2");
            $stmt->execute([$tipo]);
            $ejemplos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($ejemplos)) {
                echo "<table>";
                $headers = array_keys($ejemplos[0]);
                echo "<tr>";
                foreach ($headers as $header) {
                    echo "<th>" . htmlspecialchars($header) . "</th>";
                }
                echo "</tr>";
                
                foreach ($ejemplos as $ejemplo) {
                    echo "<tr>";
                    foreach ($ejemplo as $valor) {
                        $valorMostrar = is_null($valor) ? '<em>NULL</em>' : htmlspecialchars(substr($valor, 0, 30)) . (strlen($valor) > 30 ? '...' : '');
                        echo "<td>" . $valorMostrar . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>Error analizando tipos de formulario: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    // 4. Buscar tablas relacionadas por JOIN
    echo "<h2>4. Análisis de relaciones:</h2>";
    
    try {
        echo "<h4>Relación consultas → anteojos:</h4>";
        $stmt = $db->query("
            SELECT c.id_consulta, c.tipo_formulario, a.* 
            FROM consultas c 
            LEFT JOIN consulta_anteojos a ON c.id_consulta = a.id_consulta 
            WHERE c.tipo_formulario = 'anteojos' 
            LIMIT 2
        ");
        $relacionAnteojos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($relacionAnteojos)) {
            echo "<table>";
            $headers = array_keys($relacionAnteojos[0]);
            echo "<tr>";
            foreach ($headers as $header) {
                echo "<th>" . htmlspecialchars($header) . "</th>";
            }
            echo "</tr>";
            
            foreach ($relacionAnteojos as $ejemplo) {
                echo "<tr>";
                foreach ($ejemplo as $valor) {
                    $valorMostrar = is_null($valor) ? '<em>NULL</em>' : htmlspecialchars(substr($valor, 0, 20)) . (strlen($valor) > 20 ? '...' : '');
                    echo "<td>" . $valorMostrar . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron relaciones con anteojos</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>Error analizando relaciones: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p><strong>Error de conexión:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

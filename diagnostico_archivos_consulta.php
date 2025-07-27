<?php
/**
 * Script para diagnosticar los archivos de una consulta específica
 */

require_once 'model/conexion.php';
require_once 'model/archivos.model.php';

echo "<h2>🔍 Diagnóstico de Archivos de Consulta</h2>";

// Obtener el ID de consulta de los parámetros GET o usar uno por defecto
$idConsulta = isset($_GET['id_consulta']) ? $_GET['id_consulta'] : null;

if (!$idConsulta) {
    // Buscar la última consulta de estudios que tenga archivos
    try {
        $pdo = Conexion::conectar();
        $stmt = $pdo->query("
            SELECT DISTINCT c.id_consulta 
            FROM consultas c 
            INNER JOIN archivos_consulta ac ON c.id_consulta = ac.id_consulta 
            WHERE c.tipo_formulario = 'estudios' 
            ORDER BY c.fecha_registro DESC 
            LIMIT 1
        ");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $idConsulta = $result ? $result['id_consulta'] : null;
    } catch (Exception $e) {
        echo "<p>❌ Error al buscar consulta: " . $e->getMessage() . "</p>";
    }
}

if (!$idConsulta) {
    echo "<p>❌ No se encontró ninguna consulta con archivos para diagnosticar.</p>";
    echo "<p>💡 Pruebe subiendo un archivo primero o especifique un ID: ?id_consulta=123</p>";
    exit;
}

echo "<p>🔧 Diagnosticando consulta ID: <strong>$idConsulta</strong></p>";

// Verificar que la consulta existe
try {
    $pdo = Conexion::conectar();
    $stmt = $pdo->prepare("SELECT * FROM consultas WHERE id_consulta = :id");
    $stmt->bindParam(':id', $idConsulta);
    $stmt->execute();
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$consulta) {
        echo "<p>❌ La consulta con ID $idConsulta no existe.</p>";
        exit;
    }
    
    echo "<h3>📋 Datos de la consulta:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Valor</th></tr>";
    foreach ($consulta as $campo => $valor) {
        echo "<tr><td>$campo</td><td>" . htmlspecialchars($valor ?? 'NULL') . "</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p>❌ Error al obtener consulta: " . $e->getMessage() . "</p>";
    exit;
}

// Obtener archivos usando el modelo
echo "<h3>📁 Archivos usando el modelo:</h3>";
$archivos = ModelArchivos::mdlGetArchivosPorConsulta($idConsulta);

echo "<p>Cantidad de archivos encontrados: <strong>" . count($archivos) . "</strong></p>";

if (!empty($archivos)) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr>";
    echo "<th>Campo</th>";
    foreach ($archivos as $index => $archivo) {
        echo "<th>Archivo " . ($index + 1) . "</th>";
    }
    echo "</tr>";
    
    // Obtener todas las claves únicas de todos los archivos
    $todasLasClaves = [];
    foreach ($archivos as $archivo) {
        $todasLasClaves = array_merge($todasLasClaves, array_keys($archivo));
    }
    $todasLasClaves = array_unique($todasLasClaves);
    
    foreach ($todasLasClaves as $clave) {
        echo "<tr>";
        echo "<td><strong>$clave</strong></td>";
        foreach ($archivos as $archivo) {
            $valor = isset($archivo[$clave]) ? $archivo[$clave] : 'N/A';
            echo "<td>" . htmlspecialchars($valor) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>⚠️ No se encontraron archivos para esta consulta.</p>";
}

// Verificar la tabla archivos_consulta directamente
echo "<h3>🔗 Relaciones en archivos_consulta:</h3>";
try {
    $stmt = $pdo->prepare("
        SELECT ac.*, a.nombre_archivo, a.ruta_archivo 
        FROM archivos_consulta ac 
        LEFT JOIN archivos a ON ac.id_archivo = a.id_archivo 
        WHERE ac.id_consulta = :id
    ");
    $stmt->bindParam(':id', $idConsulta);
    $stmt->execute();
    $relaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($relaciones)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Relación</th><th>ID Consulta</th><th>ID Archivo</th><th>Nombre Archivo</th><th>Ruta</th></tr>";
        foreach ($relaciones as $relacion) {
            echo "<tr>";
            echo "<td>" . $relacion['id_archivo_consulta'] . "</td>";
            echo "<td>" . $relacion['id_consulta'] . "</td>";
            echo "<td>" . $relacion['id_archivo'] . "</td>";
            echo "<td>" . ($relacion['nombre_archivo'] ?? 'No encontrado') . "</td>";
            echo "<td>" . ($relacion['ruta_archivo'] ?? 'No encontrada') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>⚠️ No hay relaciones en archivos_consulta para esta consulta.</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error al verificar relaciones: " . $e->getMessage() . "</p>";
}

// Simular la respuesta AJAX
echo "<h3>🌐 Simulación de respuesta AJAX:</h3>";
$_POST['id_consulta'] = $idConsulta;
$_POST['operacion'] = 'archivosPorConsulta';

ob_start();
include 'ajax/archivos.ajax.php';
$ajaxResponse = ob_get_clean();

echo "<p><strong>Respuesta JSON:</strong></p>";
echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ddd;'>";
echo htmlspecialchars($ajaxResponse);
echo "</pre>";

// Decodificar y mostrar de forma legible
$responseData = json_decode($ajaxResponse, true);
if ($responseData) {
    echo "<h4>📊 Datos decodificados:</h4>";
    echo "<pre>";
    print_r($responseData);
    echo "</pre>";
} else {
    echo "<p>❌ Error al decodificar JSON: " . json_last_error_msg() . "</p>";
}

// Verificar la existencia de archivos físicos
if (!empty($archivos)) {
    echo "<h3>💾 Verificación de archivos físicos:</h3>";
    foreach ($archivos as $index => $archivo) {
        $rutaCompleta = $archivo['ruta_archivo'];
        echo "<p><strong>Archivo " . ($index + 1) . ":</strong> " . htmlspecialchars($archivo['nombre_archivo']) . "</p>";
        echo "<p>Ruta: " . htmlspecialchars($rutaCompleta) . "</p>";
        
        if (file_exists($rutaCompleta)) {
            $tamanoReal = filesize($rutaCompleta);
            echo "<p>✅ Existe físicamente - Tamaño: " . number_format($tamanoReal / 1024, 2) . " KB</p>";
        } else {
            echo "<p>❌ No existe físicamente en la ruta especificada</p>";
        }
        echo "<hr>";
    }
}

echo "<p>✅ Diagnóstico completado</p>";
?>

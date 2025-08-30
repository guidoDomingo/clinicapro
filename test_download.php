<?php
// test_download.php - Prueba directa de descarga de archivos

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de base de datos (ajustar según tu configuración)
$host = 'localhost';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🧪 Test de Descarga de Archivos</h2>";
    
    // Obtener algunos archivos de prueba (más recientes)
    $sql = "SELECT id_archivo, nombre_archivo, tipo_archivo, tamano_archivo, ruta_archivo FROM archivos ORDER BY id_archivo DESC LIMIT 10";
    $stmt = $pdo->query($sql);
    $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($archivos)) {
        echo "<p>❌ No se encontraron archivos para probar.</p>";
        exit;
    }
    
    echo "<h3>📁 Archivos Disponibles:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Tipo MIME</th><th>Tamaño</th><th>Existe</th><th>Acción</th></tr>";
    
    foreach ($archivos as $archivo) {
        $exists = file_exists($archivo['ruta_archivo']) ? '✅' : '❌';
        $size = $archivo['tamano_archivo'] ? formatFileSize($archivo['tamano_archivo']) : 'N/A';
        
        echo "<tr>";
        echo "<td>{$archivo['id_archivo']}</td>";
        echo "<td>{$archivo['nombre_archivo']}</td>";
        echo "<td>{$archivo['tipo_archivo']}</td>";
        echo "<td>$size</td>";
        echo "<td>$exists</td>";
        echo "<td>";
        
        if (file_exists($archivo['ruta_archivo'])) {
            // Enlaces de prueba
            echo "<a href='modules/consultas/api/livewire-system.php?action=download_archivo&id_archivo={$archivo['id_archivo']}' target='_blank' style='margin-right: 10px;'>📥 Ver/Descargar</a>";
            echo "<a href='test_direct_file.php?file=" . urlencode($archivo['ruta_archivo']) . "' target='_blank'>🔗 Acceso Directo</a>";
        } else {
            echo "❌ Archivo no existe";
        }
        
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test de tipos MIME
    echo "<h3>🔍 Análisis de Tipos MIME:</h3>";
    echo "<ul>";
    
    foreach ($archivos as $archivo) {
        if (!file_exists($archivo['ruta_archivo'])) continue;
        
        $detectedMime = mime_content_type($archivo['ruta_archivo']);
        $storedMime = $archivo['tipo_archivo'];
        $match = ($detectedMime === $storedMime) ? '✅' : '⚠️';
        
        echo "<li>";
        echo "<strong>{$archivo['nombre_archivo']}</strong><br>";
        echo "&nbsp;&nbsp;Almacenado: $storedMime<br>";
        echo "&nbsp;&nbsp;Detectado: $detectedMime $match<br>";
        echo "</li>";
    }
    
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 20px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
a { text-decoration: none; color: #007bff; }
a:hover { text-decoration: underline; }
</style>
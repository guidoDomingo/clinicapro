<?php
// debug_paths.php - Verificar rutas de archivos

$host = 'localhost';
$dbname = 'clinica';
$username = 'postgres';
$password = 'admin';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>🔍 Debug de Rutas de Archivos</h2>";
    
    $sql = "SELECT id_archivo, nombre_archivo, ruta_archivo FROM archivos ORDER BY id_archivo DESC LIMIT 5";
    $stmt = $pdo->query($sql);
    $archivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📁 Rutas en Base de Datos:</h3>";
    foreach ($archivos as $archivo) {
        echo "<div style='margin-bottom: 15px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>ID:</strong> {$archivo['id_archivo']}<br>";
        echo "<strong>Nombre:</strong> {$archivo['nombre_archivo']}<br>";
        echo "<strong>Ruta DB:</strong> {$archivo['ruta_archivo']}<br>";
        echo "<strong>Existe:</strong> " . (file_exists($archivo['ruta_archivo']) ? '✅ Sí' : '❌ No') . "<br>";
        echo "</div>";
    }
    
    echo "<h3>📂 Archivos Físicos en uploads/consultas:</h3>";
    $uploadDir = 'uploads/consultas/';
    if (is_dir($uploadDir)) {
        $files = array_diff(scandir($uploadDir), array('.', '..'));
        foreach ($files as $file) {
            $fullPath = $uploadDir . $file;
            $size = filesize($fullPath);
            echo "<div style='margin-bottom: 10px;'>";
            echo "<strong>Archivo:</strong> $file<br>";
            echo "<strong>Ruta completa:</strong> $fullPath<br>";
            echo "<strong>Tamaño:</strong> " . number_format($size / 1024, 2) . " KB<br>";
            echo "</div>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
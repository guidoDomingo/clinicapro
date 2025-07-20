<?php
/**
 * Debug detallado de subida de archivos
 */

echo "<h2>Debug de Upload de Archivos</h2>";
echo "<h3>1. Verificación de directorios</h3>";

$uploadDir = __DIR__ . "/uploads/reservas/";
echo "Directorio base de uploads: $uploadDir<br>";
echo "¿Existe el directorio? " . (file_exists($uploadDir) ? "✅ SÍ" : "❌ NO") . "<br>";
echo "¿Es escribible? " . (is_writable($uploadDir) ? "✅ SÍ" : "❌ NO") . "<br>";

// Crear directorio si no existe
if (!file_exists($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        echo "✅ Directorio creado<br>";
    } else {
        echo "❌ Error creando directorio<br>";
    }
}

// Test de escritura
$testFile = $uploadDir . "test_write.txt";
if (file_put_contents($testFile, "test")) {
    echo "✅ Test de escritura exitoso<br>";
    unlink($testFile); // Eliminar archivo de prueba
} else {
    echo "❌ Error en test de escritura<br>";
}

echo "<h3>2. Configuración PHP</h3>";
echo "file_uploads: " . (ini_get('file_uploads') ? '✅ Habilitado' : '❌ Deshabilitado') . "<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'Default del sistema') . "<br>";

echo "<h3>3. Variables del servidor</h3>";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "SCRIPT_FILENAME: " . $_SERVER['SCRIPT_FILENAME'] . "<br>";
echo "Working directory: " . getcwd() . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>4. Datos recibidos por POST</h3>";
    echo "<h4>4.1 $_POST:</h4>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    echo "<h4>4.2 $_FILES:</h4>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    if (isset($_FILES['archivos_reserva'])) {
        echo "<h4>4.3 Procesamiento de archivos:</h4>";
        $files = $_FILES['archivos_reserva'];
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<b>Archivo " . ($i + 1) . ":</b><br>";
                echo "Nombre: " . $files['name'][$i] . "<br>";
                echo "Tipo: " . $files['type'][$i] . "<br>";
                echo "Tamaño: " . $files['size'][$i] . " bytes<br>";
                echo "Archivo temporal: " . $files['tmp_name'][$i] . "<br>";
                echo "¿Existe archivo temporal? " . (file_exists($files['tmp_name'][$i]) ? "✅ SÍ" : "❌ NO") . "<br>";
                
                if (file_exists($files['tmp_name'][$i])) {
                    $testDir = $uploadDir . "TEST_" . date('YmdHis') . "/";
                    echo "Directorio de destino: $testDir<br>";
                    
                    if (mkdir($testDir, 0755, true)) {
                        echo "✅ Directorio creado<br>";
                        
                        $fileName = time() . "_test_" . $files['name'][$i];
                        $destPath = $testDir . $fileName;
                        echo "Ruta de destino: $destPath<br>";
                        
                        if (move_uploaded_file($files['tmp_name'][$i], $destPath)) {
                            echo "✅ Archivo movido exitosamente<br>";
                            echo "Tamaño final: " . filesize($destPath) . " bytes<br>";
                        } else {
                            echo "❌ Error moviendo archivo<br>";
                            echo "Error details: " . error_get_last()['message'] . "<br>";
                        }
                    } else {
                        echo "❌ Error creando directorio de destino<br>";
                    }
                } else {
                    echo "❌ Archivo temporal no existe<br>";
                }
                echo "</div>";
            } else {
                echo "<b>Error en archivo " . ($i + 1) . ":</b> Error " . $files['error'][$i] . "<br>";
            }
        }
    }
}
?>

<h3>5. Formulario de Prueba</h3>
<form method="POST" enctype="multipart/form-data">
    <label for="archivos_reserva">Seleccionar archivos:</label><br>
    <input type="file" name="archivos_reserva[]" multiple><br><br>
    <button type="submit">Probar Upload</button>
</form>

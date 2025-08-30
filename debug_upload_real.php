<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>DEBUG Upload - Estado del servidor</h2>";

echo "<h3>POST Data:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>FILES Data:</h3>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<h3>REQUEST METHOD:</h3>";
echo $_SERVER['REQUEST_METHOD'] . "<br>";

echo "<h3>CONTENT TYPE:</h3>";
echo $_SERVER['CONTENT_TYPE'] ?? 'No definido';

echo "<h3>Upload Settings:</h3>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";

// Si hay archivos, intentar procesarlos
if (!empty($_FILES)) {
    echo "<h3>Procesamiento de archivos:</h3>";
    foreach ($_FILES as $fieldName => $fieldData) {
        echo "<strong>Campo: $fieldName</strong><br>";
        if (is_array($fieldData['name'])) {
            for ($i = 0; $i < count($fieldData['name']); $i++) {
                echo "Archivo $i:<br>";
                echo "- Nombre: " . ($fieldData['name'][$i] ?? 'No definido') . "<br>";
                echo "- Tamaño: " . ($fieldData['size'][$i] ?? 'No definido') . " bytes<br>";
                echo "- Error: " . ($fieldData['error'][$i] ?? 'No definido') . "<br>";
                echo "- Tipo: " . ($fieldData['type'][$i] ?? 'No definido') . "<br>";
                echo "- Temp: " . ($fieldData['tmp_name'][$i] ?? 'No definido') . "<br>";
                if (isset($fieldData['tmp_name'][$i]) && file_exists($fieldData['tmp_name'][$i])) {
                    echo "- Archivo temporal existe: SÍ<br>";
                } else {
                    echo "- Archivo temporal existe: NO<br>";
                }
                echo "<br>";
            }
        } else {
            echo "- Nombre: " . ($fieldData['name'] ?? 'No definido') . "<br>";
            echo "- Tamaño: " . ($fieldData['size'] ?? 'No definido') . " bytes<br>";
            echo "- Error: " . ($fieldData['error'] ?? 'No definido') . "<br>";
            echo "- Tipo: " . ($fieldData['type'] ?? 'No definido') . "<br>";
            echo "- Temp: " . ($fieldData['tmp_name'] ?? 'No definido') . "<br>";
            if (isset($fieldData['tmp_name']) && file_exists($fieldData['tmp_name'])) {
                echo "- Archivo temporal existe: SÍ<br>";
            } else {
                echo "- Archivo temporal existe: NO<br>";
            }
        }
        echo "<br>";
    }
}
?>
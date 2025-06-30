<?php
/**
 * Script para verificar las rutas de autenticación
 * Útil para diagnosticar problemas de carga de archivos
 */

$rutaControlador = __DIR__ . "/controller/AuthController.php";
$rutaModelo = __DIR__ . "/model/ReservasPublicModel.php";

echo "<h1>Verificación de Rutas para Autenticación</h1>";

echo "<h3>Verificando rutas de controlador y modelo:</h3>";

// Verificar el controlador
if (file_exists($rutaControlador)) {
    echo "<p style='color:green'>✅ Controlador AuthController.php encontrado en: $rutaControlador</p>";
} else {
    echo "<p style='color:red'>❌ Controlador AuthController.php NO encontrado en: $rutaControlador</p>";
}

// Verificar el modelo
if (file_exists($rutaModelo)) {
    echo "<p style='color:green'>✅ Modelo ReservasPublicModel.php encontrado en: $rutaModelo</p>";
} else {
    echo "<p style='color:red'>❌ Modelo ReservasPublicModel.php NO encontrado en: $rutaModelo</p>";
}

// Verificar la estructura de directorios
$directorios = [
    __DIR__ . "/controller",
    __DIR__ . "/model",
    __DIR__ . "/view",
    __DIR__ . "/view/modules"
];

echo "<h3>Verificando estructura de directorios:</h3>";

foreach ($directorios as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color:green'>✅ Directorio '$dir' existe</p>";
    } else {
        echo "<p style='color:red'>❌ Directorio '$dir' NO existe</p>";
    }
}

// Mostrar rutas incluidas en index.php
echo "<h3>Archivos incluidos en index.php:</h3>";
$indexContent = file_get_contents(__DIR__ . "/index.php");
preg_match_all('/require_once\s+["\']([^"\']+)["\']/i', $indexContent, $matches);

if (!empty($matches[1])) {
    echo "<ul>";
    foreach ($matches[1] as $includedFile) {
        $fullPath = realpath(__DIR__ . "/" . $includedFile);
        if ($fullPath && file_exists($fullPath)) {
            echo "<li style='color:green'>✅ $includedFile ($fullPath)</li>";
        } else {
            echo "<li style='color:red'>❌ $includedFile (No encontrado)</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p>No se encontraron inclusiones en index.php</p>";
}

// Mostrar información del entorno
echo "<h3>Información del entorno:</h3>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Ruta de este script:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Directorio raíz:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";

// Imprimir include_path para depuración
echo "<p><strong>Include Path:</strong> " . get_include_path() . "</p>";

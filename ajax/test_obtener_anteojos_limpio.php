<?php
/**
 * Script de prueba limpio para ejecutar obtener-datos-anteojos.php
 */

// Redirigir la salida de errores a un archivo
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'c:/laragon/www/clinica/logs/test_anteojos_error.log');

// Función para verificar la conexión a la base de datos
function verificar_conexion() {
    $conexion_path = 'c:/laragon/www/clinica/model/conexion.php';
    if (!file_exists($conexion_path)) {
        file_put_contents('c:/laragon/www/clinica/logs/test_anteojos_error.log', 
            date('[Y-m-d H:i:s] ') . "Archivo de conexión no encontrado: $conexion_path\n", 
            FILE_APPEND);
        return false;
    }
    
    require_once $conexion_path;
    
    try {
        $db = Conexion::conectar();
        file_put_contents('c:/laragon/www/clinica/logs/test_anteojos_error.log', 
            date('[Y-m-d H:i:s] ') . "Conexión a la base de datos exitosa\n", 
            FILE_APPEND);
        return true;
    } catch (Exception $e) {
        file_put_contents('c:/laragon/www/clinica/logs/test_anteojos_error.log', 
            date('[Y-m-d H:i:s] ') . "Error de conexión a la base de datos: " . $e->getMessage() . "\n", 
            FILE_APPEND);
        return false;
    }
}

// Crear directorio de logs si no existe
if (!is_dir('c:/laragon/www/clinica/logs/')) {
    mkdir('c:/laragon/www/clinica/logs/', 0777, true);
}

// Verificar conexión
$conexion_ok = verificar_conexion();

// Actualizar el archivo obtener-datos-anteojos.php con rutas absolutas
$file_content = file_get_contents('obtener-datos-anteojos.php');
$updated_content = str_replace(
    'require_once "../model/conexion.php";', 
    'require_once "c:/laragon/www/clinica/model/conexion.php";', 
    $file_content
);
file_put_contents('obtener-datos-anteojos.php.temp', $updated_content);

// Simular una petición directa para ver la respuesta
$_GET['id_consulta'] = 52;

// Capturar la salida en un buffer
ob_start();
include 'obtener-datos-anteojos.php.temp';
$output = ob_get_clean();

// Eliminar archivo temporal
@unlink('obtener-datos-anteojos.php.temp');

// Guardar la salida para análisis
file_put_contents('c:/laragon/www/clinica/logs/test_anteojos_output.log', $output);

// Devolver la salida para análisis en el navegador
header('Content-Type: text/plain');
echo $output;
?>

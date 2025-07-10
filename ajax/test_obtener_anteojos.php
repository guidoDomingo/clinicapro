<?php
/**
 * Script de prueba para diagnosticar problemas con la obtención de datos de anteojos
 */

// Habilitar mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función auxiliar de depuración
function debug_print($message, $data = null) {
    echo "[DEBUG] $message\n";
    if ($data !== null) {
        echo "DATA: " . print_r($data, true) . "\n";
    }
}

// Capturar la salida en un buffer
ob_start();

// Simular una solicitud GET
$_GET['id_consulta'] = 52; // El ID que causó el problema

try {
    // Verificar si existe el archivo model/conexion.php
    debug_print("Verificando archivo de conexión");
    if (file_exists('../model/conexion.php')) {
        debug_print("Archivo de conexión encontrado");
    } else {
        debug_print("Archivo de conexión NO encontrado");
    }
    
    // Verificar la variable GET
    debug_print("Verificando variable GET", $_GET);
    
    // Incluir el archivo para probar
    debug_print("Incluyendo archivo obtener-datos-anteojos.php");
    include 'obtener-datos-anteojos.php';
    debug_print("Archivo incluido exitosamente");
} catch (Exception $e) {
    echo "EXCEPCIÓN CAPTURADA: " . $e->getMessage() . "\n";
    echo "En archivo: " . $e->getFile() . " línea: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "ERROR PHP CAPTURADO: " . $e->getMessage() . "\n";
    echo "En archivo: " . $e->getFile() . " línea: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

// Obtener la salida
$output = ob_get_clean();

// Mostrar información de depuración
echo "CONTENIDO DE LA SALIDA:\n";
echo "------------------------\n";
echo $output . "\n";
echo "------------------------\n";

// Verificar si es un JSON válido
echo "\nVALIDACIÓN DE JSON:\n";
echo "------------------------\n";
$json_decoded = json_decode($output);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "El JSON es válido.\n";
} else {
    echo "Error en JSON: " . json_last_error_msg() . "\n";
    
    // Mostrar los primeros 100 caracteres para diagnóstico
    echo "Primeros 100 caracteres: " . substr($output, 0, 100) . "\n";
    
    // Buscar caracteres no imprimibles
    echo "Caracteres no imprimibles:\n";
    $chars = str_split($output);
    $positions = [];
    foreach ($chars as $pos => $char) {
        $ord = ord($char);
        if (($ord < 32 && $ord !== 9 && $ord !== 10 && $ord !== 13) || $ord >= 127) {
            $positions[] = "Posición $pos: " . $ord . " (hex: " . dechex($ord) . ")";
        }
    }
    
    if ($positions) {
        echo implode("\n", array_slice($positions, 0, 10)) . "\n";
        if (count($positions) > 10) {
            echo "... y " . (count($positions) - 10) . " más\n";
        }
    } else {
        echo "No se encontraron caracteres no imprimibles.\n";
    }
}
?>

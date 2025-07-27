<?php
// Test simple para verificar includes
echo "=== TEST DE INCLUDES ===\n";

echo "1. Probando modelo...\n";
try {
    require_once "../model/consultas.model.php";
    echo "✅ Modelo cargado correctamente\n";
    echo "✅ Clase ModelConsulta: " . (class_exists('ModelConsulta') ? 'existe' : 'NO existe') . "\n";
} catch (Exception $e) {
    echo "❌ Error modelo: " . $e->getMessage() . "\n";
}

echo "\n2. Probando controlador...\n";
try {
    require_once "../controller/consultas.controller.php";
    echo "✅ Controlador cargado correctamente\n";
    echo "✅ Clase ControllerConsulta: " . (class_exists('ControllerConsulta') ? 'existe' : 'NO existe') . "\n";
} catch (Exception $e) {
    echo "❌ Error controlador: " . $e->getMessage() . "\n";
}

echo "\n3. Probando endpoint principal...\n";
if (file_exists("guardar-consulta-informe-imagen.php")) {
    echo "✅ Endpoint existe\n";
    $syntax = shell_exec('php -l guardar-consulta-informe-imagen.php 2>&1');
    if (strpos($syntax, 'No syntax errors') !== false) {
        echo "✅ Sintaxis correcta\n";
    } else {
        echo "❌ Error sintaxis: " . $syntax . "\n";
    }
} else {
    echo "❌ Endpoint no encontrado\n";
}

echo "\n=== FIN TEST ===\n";
?>

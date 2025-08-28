<?php
// Test paso a paso
session_start();
$_SESSION['user_id'] = 1;

echo "<h1>Test Paso a Paso</h1>";

echo "<h3>Paso 1: Verificar conexión</h3>";
require_once 'model/conexion.php';
$pdo = Conexion::conectar();
if ($pdo) {
    echo "✅ Conexión OK<br>";
} else {
    echo "❌ Error de conexión<br>";
    exit;
}

echo "<h3>Paso 2: Verificar archivos requeridos</h3>";
$files = [
    'controller/consultas.controller.php',
    'model/personas.model.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe<br>";
    } else {
        echo "❌ $file no existe<br>";
    }
}

echo "<h3>Paso 3: Incluir archivos</h3>";
try {
    require_once 'controller/consultas.controller.php';
    echo "✅ Controller incluido<br>";
    
    require_once 'model/personas.model.php';  
    echo "✅ Model incluido<br>";
    
} catch (Exception $e) {
    echo "❌ Error incluyendo: " . $e->getMessage() . "<br>";
}

echo "<h3>Paso 4: Test clase LivewireCRUDHandler</h3>";
try {
    // Incluir solo la definición de la clase
    $code = file_get_contents('modules/consultas/api/livewire-crud.php');
    
    // Buscar donde termina la clase y solo incluir hasta ahí
    $classStart = strpos($code, 'class LivewireCRUDHandler');
    if ($classStart !== false) {
        // Encontrar el final de la clase
        $braces = 0;
        $classEnd = $classStart;
        $inClass = false;
        
        for ($i = $classStart; $i < strlen($code); $i++) {
            if ($code[$i] === '{') {
                $braces++;
                $inClass = true;
            } elseif ($code[$i] === '}') {
                $braces--;
                if ($inClass && $braces === 0) {
                    $classEnd = $i + 1;
                    break;
                }
            }
        }
        
        // Extraer solo la clase
        $classCode = substr($code, 0, $classStart) . substr($code, $classStart, $classEnd - $classStart);
        
        // Ejecutar solo la definición de clase (sin el handler al final)
        $lines = explode("\n", $classCode);
        $filteredLines = [];
        
        foreach ($lines as $line) {
            // Saltar las líneas que ejecutan el handler
            if (strpos($line, '$handler = new LivewireCRUDHandler()') === false && 
                strpos($line, '$handler->handleRequest()') === false) {
                $filteredLines[] = $line;
            }
        }
        
        eval(implode("\n", $filteredLines));
        
        echo "✅ Clase definida<br>";
        
        // Probar constructor
        $handler = new LivewireCRUDHandler();
        echo "✅ Constructor OK<br>";
        
    } else {
        echo "❌ No se encontró la clase<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error con la clase: " . $e->getMessage() . "<br>";
    echo "Archivo: " . $e->getFile() . "<br>";
    echo "Línea: " . $e->getLine() . "<br>";
}

?>
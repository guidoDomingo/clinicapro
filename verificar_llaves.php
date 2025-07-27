<?php
// Script para verificar llaves en PHP
$file = 'c:\laragon\www\clinica\ajax\guardar-consulta-informe-imagen.php';

if (!file_exists($file)) {
    echo "Archivo no encontrado: $file\n";
    exit;
}

$content = file_get_contents($file);
$lines = explode("\n", $content);

$openBraces = 0;
$braceStack = [];

echo "Verificando balance de llaves...\n\n";

for ($i = 0; $i < count($lines); $i++) {
    $lineNum = $i + 1;
    $line = $lines[$i];
    
    // Contar llaves de apertura
    $openCount = substr_count($line, '{');
    $closeCount = substr_count($line, '}');
    
    if ($openCount > 0 || $closeCount > 0) {
        echo "Línea $lineNum: +$openCount -{$closeCount} = " . ($openCount - $closeCount) . " | " . trim($line) . "\n";
        
        // Tracking del balance
        for ($j = 0; $j < $openCount; $j++) {
            $braceStack[] = $lineNum;
        }
        
        for ($j = 0; $j < $closeCount; $j++) {
            if (count($braceStack) > 0) {
                array_pop($braceStack);
            } else {
                echo "ERROR: Llave de cierre sin apertura en línea $lineNum\n";
            }
        }
    }
}

echo "\n\nResultado final:\n";
echo "Llaves abiertas sin cerrar: " . count($braceStack) . "\n";

if (count($braceStack) > 0) {
    echo "Líneas con llaves sin cerrar:\n";
    foreach ($braceStack as $lineNum) {
        echo "- Línea $lineNum: " . trim($lines[$lineNum - 1]) . "\n";
    }
} else {
    echo "Todas las llaves están balanceadas.\n";
}
?>

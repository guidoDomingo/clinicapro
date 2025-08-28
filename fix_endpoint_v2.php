<?php
// Script directo para cambiar endpoint
$filePath = __DIR__ . '/view/modules/consultas-new.php';

echo "Archivo: $filePath\n";
echo "Existe: " . (file_exists($filePath) ? "SÍ" : "NO") . "\n";

if (file_exists($filePath)) {
    $content = file_get_contents($filePath);
    
    // Buscar la línea específica
    $search = "livwireCrud: '/clinica/modules/consultas/api/livwire-crud.php',";
    $replace = "livwireCrud: '/clinica/modules/consultas/api/livwire-crud-debug.php',";
    
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        file_put_contents($filePath, $content);
        echo "✅ Cambio realizado exitosamente\n";
    } else {
        echo "❌ No se encontró la línea exacta: $search\n";
        
        // Buscar variaciones
        if (strpos($content, 'livwire-crud.php') !== false) {
            echo "🔍 Se encontró 'livwire-crud.php' en el archivo\n";
            $content = str_replace('livwire-crud.php', 'livwire-crud-debug.php', $content);
            file_put_contents($filePath, $content);
            echo "✅ Cambio alternativo realizado\n";
        }
    }
}
?>
<?php
echo "🔧 Debug: Sistema de Archivos\n";
echo "==================================\n\n";

// Mostrar información de $_FILES y $_POST
echo "📁 FILES recibidos:\n";
var_dump($_FILES);

echo "\n📝 POST recibidos:\n";
var_dump($_POST);

echo "\n🔗 REQUEST METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "🔗 Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No definido') . "\n";

// Si hay archivos, mostrar información detallada
if (!empty($_FILES)) {
    echo "\n📊 Análisis detallado de archivos:\n";
    foreach ($_FILES as $fieldName => $fileData) {
        echo "Campo: $fieldName\n";
        if (is_array($fileData['name'])) {
            for ($i = 0; $i < count($fileData['name']); $i++) {
                echo "  Archivo $i: " . $fileData['name'][$i] . "\n";
                echo "  Tamaño: " . $fileData['size'][$i] . " bytes\n";
                echo "  Tipo: " . $fileData['type'][$i] . "\n";
                echo "  Error: " . $fileData['error'][$i] . "\n";
            }
        } else {
            echo "  Archivo: " . $fileData['name'] . "\n";
            echo "  Tamaño: " . $fileData['size'] . " bytes\n";
            echo "  Tipo: " . $fileData['type'] . "\n";
            echo "  Error: " . $fileData['error'] . "\n";
        }
        echo "\n";
    }
}

// Intentar procesar con el sistema real
if (!empty($_FILES) && !empty($_POST['action']) && $_POST['action'] === 'upload_archivo') {
    echo "🚀 Intentando upload real...\n";
    
    try {
        // Incluir el sistema real
        require_once __DIR__ . '/modules/consultas/api/livewire-system.php';
        
        $api = new LivewireSystem();
        $result = $api->handleRequest();
        
        echo "✅ Resultado del upload:\n";
        echo json_encode($result, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        echo "❌ Error en upload: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }
}
?>
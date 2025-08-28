<?php
// Simple test for API endpoint without cURL
echo "🔧 Testing API endpoint accessibility...\n\n";

// Check if the file exists
$apiFile = __DIR__ . '/modules/consultas/api/livwire-crud.php';
echo "API File Path: $apiFile\n";
echo "File exists: " . (file_exists($apiFile) ? "YES" : "NO") . "\n";

if (file_exists($apiFile)) {
    echo "File size: " . filesize($apiFile) . " bytes\n";
    echo "File is readable: " . (is_readable($apiFile) ? "YES" : "NO") . "\n";
}

// Check controller file
$controllerFile = __DIR__ . '/controller/ControllerConsulta.php';
echo "\nController File Path: $controllerFile\n";
echo "Controller exists: " . (file_exists($controllerFile) ? "YES" : "NO") . "\n";

if (file_exists($controllerFile)) {
    echo "Controller size: " . filesize($controllerFile) . " bytes\n";
    echo "Controller is readable: " . (is_readable($controllerFile) ? "YES" : "NO") . "\n";
}

// Test basic PHP execution
echo "\n🔧 Testing basic PHP execution...\n";

// Simulate POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];

// Capture output
ob_start();

// Set test input
$testInput = json_encode([
    'action' => 'validateField',
    'data' => [
        'property' => 'search_nombre',
        'value' => 'leo',
        'formType' => 'general'
    ]
]);

// Mock php://input
file_put_contents('php://memory', $testInput);

try {
    // Try to execute the API file
    include $apiFile;
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ API executed successfully\n";
    echo "Output: $output\n";
    
    // Try to decode JSON
    $decoded = json_decode($output, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ JSON Error: " . json_last_error_msg() . "\n";
        echo "Raw output (first 200 chars): " . substr($output, 0, 200) . "\n";
    } else {
        echo "✅ JSON decoded successfully\n";
        print_r($decoded);
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Exception: " . $e->getMessage() . "\n";
} catch (Error $e) {
    ob_end_clean();
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
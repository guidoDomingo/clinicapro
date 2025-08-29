<?php
// Test más simple y directo
echo "=== TEST SIMPLE ===\n";

// Simular data
$_POST = [
    'action' => 'search',
    'table' => 'rh_person',
    'nombres' => 'guido',
    'apellidos' => 'guido',
    'ci' => 'guido'
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

echo "POST data:\n";
print_r($_POST);

echo "\nCONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set') . "\n";

// Verificar que getInput funcionará
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    echo "Intentará parsear JSON\n";
} else {
    echo "Usará \$_REQUEST\n";
    echo "REQUEST data:\n";
    print_r($_REQUEST);
}
?>
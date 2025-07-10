<?php
$url = 'http://localhost/clinica/ajax/obtener-datos-anteojos.php?id_consulta=52';
$response = file_get_contents($url);

echo "URL: $url\n";
echo "Response:\n$response\n";

// Verificar si es JSON válido
$json = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\nJSON válido. Estructura:\n";
    print_r($json);
} else {
    echo "\nError en JSON: " . json_last_error_msg() . "\n";
}
?>

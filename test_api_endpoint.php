<?php
// Test the API endpoint directly
echo "🔧 Testing Livewire CRUD API endpoint...\n\n";

// Test URL
$url = 'http://localhost/clinica/modules/consultas/api/livwire-crud.php';

// Test data
$testData = [
    'action' => 'validateField',
    'data' => [
        'property' => 'search_nombre',
        'value' => 'leo',
        'formType' => 'general'
    ]
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen(json_encode($testData))
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}

// Separate headers and body
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "\n--- HEADERS ---\n";
echo $headers;

echo "\n--- BODY ---\n";
echo $body;

echo "\n--- BODY ANALYSIS ---\n";
echo "Body length: " . strlen($body) . "\n";
echo "Starts with: " . substr($body, 0, 50) . "\n";

// Try to decode JSON
$decoded = json_decode($body, true);
if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "JSON decoded successfully:\n";
    print_r($decoded);
}
?>
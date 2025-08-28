<?php
// Debug para monitorear el guardado con LivewireCRUD
session_start();

echo "<h2>🚀 Debug Save LivewireCRUD</h2>";

// Verificar si se está guardando
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>📤 POST Request Detectado</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>📋 Raw Input</h3>";
    $rawInput = file_get_contents('php://input');
    echo "<textarea rows='10' cols='100'>" . htmlspecialchars($rawInput) . "</textarea>";
}

// Monitorear estado de sesión
echo "<h3>🔐 Estado de Sesión</h3>";
echo "Session ID: " . session_id() . "<br>";
echo "Usuario logueado: " . ($_SESSION['iniciarSesion'] ?? 'NO') . "<br>";
echo "ID Usuario: " . ($_SESSION['id'] ?? 'N/A') . "<br>";

// Verificar endpoint de LivewireCRUD
$endpoints = [
    'http://localhost/clinica/modules/consultas/api/livewire-crud.php',
    'http://localhost/clinica/modules/consultas/api/livwire-crud-debug.php'
];

foreach ($endpoints as $endpoint) {
    echo "<h3>🌐 Testing: " . basename($endpoint) . "</h3>";
    
    $testData = json_encode([
        'action' => 'save',
        'formType' => 'anteojos',
        'consultaId' => 161,
        'state' => [
            'txtmotivo' => 'Test motivo',
            'od_esf' => '-19.25',
            'od_nota' => 'Test nota'
        ]
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($testData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode<br>";
    if ($error) {
        echo "❌ Error: $error<br>";
    }
    echo "Response:<br>";
    echo "<textarea rows='8' cols='100'>" . htmlspecialchars($response) . "</textarea><br><br>";
}

// JavaScript para monitorear clicks del botón guardar
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Debug save script loaded');
    
    // Monitorear todos los botones de guardar
    const saveButtons = document.querySelectorAll('button[onclick*="guardar"], .btn-guardar, [data-action="save"]');
    
    saveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('💾 Botón guardar clickeado:', this);
            console.log('💾 LivewireCRUD state:', window.Livewire ? window.Livewire.all() : 'No disponible');
        });
    });
    
    // Interceptar requests AJAX
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        console.log('📡 Fetch intercepted:', args);
        return originalFetch.apply(this, args).then(response => {
            console.log('📨 Fetch response:', response);
            return response;
        });
    };
});
</script>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #2c3e50; }
h3 { color: #34495e; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; }
textarea { font-family: monospace; background: #f8f9fa; border: 1px solid #dee2e6; }
pre { background: #f8f9fa; padding: 10px; border-left: 4px solid #007bff; }
</style>
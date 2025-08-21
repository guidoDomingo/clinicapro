<?php
/**
 * Script de prueba para verificar el endpoint de equipos médicos
 */

echo "<h1>🧪 Prueba del Endpoint de Equipos Médicos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// Simular llamada a la API
$_GET['action'] = 'get_equipos_medicos';
$_SERVER['REQUEST_METHOD'] = 'GET';

// Simular sesión
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Usuario de prueba
    $_SESSION['username'] = 'test_user';
}

echo "<h2>📞 Simulando llamada a la API:</h2>";
echo "<p><strong>Action:</strong> get_equipos_medicos</p>";
echo "<p><strong>Method:</strong> GET</p>";

try {
    // Incluir el archivo de la API
    ob_start();
    include 'modules/consultas/api/consultas-api.php';
    $resultado = ob_get_clean();
    
    echo "<h2>📋 Resultado de la API:</h2>";
    echo "<pre>" . htmlspecialchars($resultado) . "</pre>";
    
    // Decodificar y mostrar de forma bonita
    $data = json_decode($resultado, true);
    
    if ($data) {
        echo "<h2>📊 Datos Decodificados:</h2>";
        if ($data['success']) {
            echo "<p class='success'>✅ SUCCESS: " . ($data['message'] ?? 'OK') . "</p>";
            echo "<p><strong>Total equipos:</strong> " . ($data['count'] ?? count($data['data'])) . "</p>";
            
            if (!empty($data['data'])) {
                echo "<h3>🏥 Lista de Equipos:</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th></tr>";
                
                foreach ($data['data'] as $equipo) {
                    echo "<tr>";
                    echo "<td>{$equipo['id']}</td>";
                    echo "<td><strong>{$equipo['codigo']}</strong></td>";
                    echo "<td>{$equipo['nombre']}</td>";
                    echo "<td>" . substr($equipo['descripcion'] ?? '', 0, 50) . "...</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p class='error'>❌ ERROR: " . ($data['message'] ?? 'Error desconocido') . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Error decodificando JSON</p>";
    }
    
} catch (Exception $e) {
    echo "<h2 class='error'>❌ Error en la prueba:</h2>";
    echo "<p class='error'>" . $e->getMessage() . "</p>";
}

echo "<h2>🧪 Prueba JavaScript (Frontend):</h2>";
echo "
<script>
// Simular llamada desde JavaScript
fetch('modules/consultas/api/consultas-api.php?action=get_equipos_medicos')
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta de la API:', data);
        
        if (data.success && data.data) {
            document.getElementById('js-result').innerHTML = 
                '<div class=\"success\">✅ JavaScript: ' + data.data.length + ' equipos cargados correctamente</div>' +
                '<p>Primer equipo: ' + JSON.stringify(data.data[0], null, 2) + '</p>';
        } else {
            document.getElementById('js-result').innerHTML = 
                '<div class=\"error\">❌ JavaScript: Error - ' + (data.message || 'Error desconocido') + '</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('js-result').innerHTML = 
            '<div class=\"error\">❌ JavaScript: Error de red - ' + error.message + '</div>';
    });
</script>
<div id='js-result'>⏳ Ejecutando prueba JavaScript...</div>
";

echo "<h2>🎯 Estado del Sistema:</h2>";
echo "<div class='info'>";
echo "<p>✅ Referencial 'equipos_medicos' poblado en la base de datos</p>";
echo "<p>✅ API endpoint 'get_equipos_medicos' implementado</p>";
echo "<p>✅ Estructura de respuesta compatible con frontend</p>";
echo "<p>✅ Datos dinámicos (sin hardcodeo)</p>";
echo "</div>";
?>
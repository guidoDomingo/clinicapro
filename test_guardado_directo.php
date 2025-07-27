<?php
require_once 'config.php';
require_once 'model/consultas.model.php';

// Simulación de datos POST para probar
$datos_simulados = [
    'motivoscomunes' => 'Control de rutina',
    'txtmotivo' => 'Paciente viene por control',
    'visionod' => '20/20',
    'visionoi' => '20/25',
    'tensionod' => '14',
    'tensionoi' => '15',
    'consulta-textarea' => 'Esta es una prueba de texto en el área de consulta para verificar que se guarda correctamente.',
    'receta-textarea' => 'Receta de prueba',
    'txtnota' => 'Nota de prueba',
    'proximaconsulta' => '2024-02-15',
    'whatsapptxt' => 'Mensaje de WhatsApp',
    'email' => 'test@test.com',
    'id_user' => 1,
    'id_reserva' => 123,
    'idPersona' => 456,
    'form_type' => 'informe_imagen'
];

echo "<h1>Prueba de Guardado Directo - Informe Imagen</h1>";

echo "<h2>Datos que se van a guardar:</h2>";
echo "<pre>";
print_r($datos_simulados);
echo "</pre>";

// Intentar guardar usando la función correcta del modelo
$resultado = ModelConsulta::mdlSetConsulta($datos_simulados);

echo "<h2>Resultado del guardado:</h2>";
echo "<p><strong>Resultado:</strong> " . htmlspecialchars($resultado) . "</p>";

if (is_numeric($resultado)) {
    echo "<p style='color: green;'>✅ Consulta guardada exitosamente con ID: $resultado</p>";
    
    // Verificar qué se guardó realmente
    try {
        $db = new PDO("pgsql:host=localhost;dbname=clinica", "postgres", "root");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->prepare("SELECT * FROM consultas WHERE id = ?");
        $stmt->execute([$resultado]);
        $consulta_guardada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Datos realmente guardados en la base de datos:</h3>";
        echo "<table border='1'>";
        foreach ($consulta_guardada as $campo => $valor) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($campo) . "</strong></td>";
            echo "<td>";
            if (is_null($valor)) {
                echo "<em>NULL</em>";
            } elseif ($campo === 'consulta_textarea') {
                echo "<span style='color: " . (empty($valor) ? 'red' : 'green') . ";'>" . 
                     (empty($valor) ? 'VACÍO ❌' : 'CON CONTENIDO ✅') . "</span><br>";
                echo htmlspecialchars($valor);
            } elseif (is_string($valor) && (strpos($valor, '{') === 0 || strpos($valor, '[') === 0)) {
                $decoded = json_decode($valor, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "<pre>" . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
                } else {
                    echo htmlspecialchars($valor);
                }
            } else {
                echo htmlspecialchars($valor);
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error al verificar datos guardados: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Error al guardar: $resultado</p>";
}

echo "<hr>";
echo "<p><a href='debug_tabla_informe.php'>Ver tabla informe_imagen</a></p>";
echo "<p><a href='debug_problemas_formulario.html'>Volver al debug del formulario</a></p>";
?>

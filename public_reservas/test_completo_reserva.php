<?php
/**
 * Test completo del sistema de reservas con archivos
 */

// Simular datos de sesión de usuario autenticado
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['nombre'] = 'Usuario';
$_SESSION['apellido'] = 'Prueba';
$_SESSION['email'] = 'test@test.com';
$_SESSION['documento'] = '12345678';
$_SESSION['telefono'] = '123456789';

require_once 'controller/ReservasPublicController.php';

echo "<h2>Test Completo del Sistema de Reservas con Archivos</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_reserva'])) {
    echo "<h3>Simulando creación de reserva con archivos...</h3>";
    
    // Simular datos POST de una reserva
    $_POST = [
        'guardarReserva' => '1',
        'fecha_reserva' => date('Y-m-d', strtotime('+1 day')),
        'servicio_id' => '1',
        'doctor_id' => '1',
        'horario' => '09:00 - 09:30',
        'seguro_id' => '1',
        'observaciones' => 'Test de reserva con archivos adjuntos',
        'nombre_paciente' => 'Usuario',
        'apellido_paciente' => 'Prueba',
        'documento_paciente' => '12345678',
        'email_paciente' => 'test@test.com',
        'telefono_paciente' => '123456789'
    ];
    
    // Simular archivos subidos si existen
    if (isset($_FILES['archivos_reserva']) && !empty($_FILES['archivos_reserva']['name'][0])) {
        echo "<p>✅ Archivos detectados para procesar: " . count($_FILES['archivos_reserva']['name']) . "</p>";
        
        // Mostrar detalles de los archivos
        for ($i = 0; $i < count($_FILES['archivos_reserva']['name']); $i++) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
            echo "<b>Archivo " . ($i + 1) . ":</b><br>";
            echo "Nombre: " . $_FILES['archivos_reserva']['name'][$i] . "<br>";
            echo "Tipo: " . $_FILES['archivos_reserva']['type'][$i] . "<br>";
            echo "Tamaño: " . $_FILES['archivos_reserva']['size'][$i] . " bytes<br>";
            echo "Error: " . $_FILES['archivos_reserva']['error'][$i] . "<br>";
            echo "</div>";
        }
    } else {
        echo "<p>⚠️ No se detectaron archivos para procesar</p>";
    }
    
    // Procesar la reserva
    echo "<h4>Procesando reserva...</h4>";
    $controlador = new ReservasPublicController();
    $resultado = $controlador->ctrProcesarReserva();
    
    if ($resultado) {
        echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; margin: 10px 0;'>";
        echo "<h4>✅ Resultado del procesamiento:</h4>";
        echo "<pre>" . print_r($resultado, true) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; margin: 10px 0;'>";
        echo "<h4>❌ No se obtuvo resultado del procesamiento</h4>";
        echo "</div>";
    }
    
    echo "<h4>Revisando logs...</h4>";
    $logFile = 'c:/laragon/www/clinica/logs/public_reservas.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        $recentLines = array_slice($lines, -20); // Últimas 20 líneas
        echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; max-height: 300px; overflow-y: auto;'>";
        echo htmlspecialchars(implode('', $recentLines));
        echo "</pre>";
    } else {
        echo "<p>No se encontró el archivo de log</p>";
    }
}
?>

<h3>Formulario de Test</h3>
<form method="POST" enctype="multipart/form-data">
    <div style="margin-bottom: 15px;">
        <label><b>Archivos a subir:</b></label><br>
        <input type="file" name="archivos_reserva[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        <small>Selecciona uno o más archivos para probar el upload</small>
    </div>
    
    <button type="submit" name="test_reserva" value="1" style="background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer;">
        🚀 Ejecutar Test Completo
    </button>
</form>

<h3>Links de utilidad</h3>
<ul>
    <li><a href="crear_archivos_prueba.php">Crear archivos de prueba</a></li>
    <li><a href="debug_upload_detallado.php">Debug de upload detallado</a></li>
    <li><a href="verificar_tabla_archivos.php">Verificar tabla de archivos</a></li>
    <li><a href="index.php">Sistema de reservas completo</a></li>
</ul>

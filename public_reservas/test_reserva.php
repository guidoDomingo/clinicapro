<?php
// Test de funcionalidad de reservas públicas
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Reservas Públicas</h2>";

// Simular datos de una reserva
$datosReserva = [
    'codigo_seguimiento' => 'TEST_' . date('YmdHis'),
    'email' => 'test@example.com',
    'nombre' => 'Test Usuario',
    'apellido' => 'Prueba'
];

echo "<h3>1. Testeo de Conexión a Base de Datos</h3>";
require_once "../model/conexion.php";
$conexion = Conexion::conectar();
if ($conexion) {
    echo "✅ Conexión a base de datos: OK<br>";
} else {
    echo "❌ Conexión a base de datos: FALLO<br>";
    exit;
}

echo "<h3>2. Testeo de Guardado de Archivos</h3>";

// Simular archivo subido
$_FILES['archivos'] = [
    'name' => ['test_file.txt'],
    'type' => ['text/plain'],
    'tmp_name' => [tempnam(sys_get_temp_dir(), 'test')],
    'error' => [0],
    'size' => [100]
];

// Crear archivo temporal para prueba
file_put_contents($_FILES['archivos']['tmp_name'][0], "Contenido de prueba");

require_once "controller/ReservasPublicController.php";
$controller = new ReservasPublicController();

// Simular archivos $_FILES
$archivosSimulados = [
    'name' => ['test_archivo.pdf'],
    'type' => ['application/pdf'],
    'tmp_name' => [tempnam(sys_get_temp_dir(), 'test_')],
    'error' => [UPLOAD_ERR_OK],
    'size' => [1024]
];

// Crear un archivo temporal para la prueba
file_put_contents($archivosSimulados['tmp_name'][0], 'Contenido de prueba PDF');

// Simular procesamiento de archivos
echo "Procesando archivos...<br>";
try {
    // Llamar método privado usando reflexión
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('procesarArchivosReserva');
    $method->setAccessible(true);
    
    $reservaId = 12345; // ID de prueba
    $resultado = $method->invoke($controller, $archivosSimulados, $reservaId, $datosReserva['codigo_seguimiento']);
    
    if (!empty($resultado)) {
        echo "✅ Procesamiento de archivos: OK - " . count($resultado) . " archivos procesados<br>";
        foreach ($resultado as $archivo) {
            echo "&nbsp;&nbsp;- " . $archivo['nombre_original'] . " → " . $archivo['nombre_archivo'] . "<br>";
        }
    } else {
        echo "❌ Procesamiento de archivos: FALLO - No se procesaron archivos<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en procesamiento de archivos: " . $e->getMessage() . "<br>";
    echo "Stack trace: " . $e->getTraceAsString() . "<br>";
}

echo "<h3>3. Testeo de Envío de Email</h3>";
try {
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('enviarEmailConfirmacion');
    $method->setAccessible(true);
    
    $resultado = $method->invoke($controller, $datosReserva);
    if ($resultado) {
        echo "✅ Envío de email: OK<br>";
    } else {
        echo "❌ Envío de email: FALLO<br>";
    }
} catch (Exception $e) {
    echo "❌ Error en envío de email: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Revisión de Logs</h3>";
$logFile = "c:/laragon/www/clinica/logs/public_reservas.log";
if (file_exists($logFile)) {
    echo "<strong>Últimas 10 líneas del log:</strong><br>";
    $lines = file($logFile);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
} else {
    echo "❌ Archivo de log no encontrado<br>";
}

// Limpiar archivo temporal
if (file_exists($_FILES['archivos']['tmp_name'][0])) {
    unlink($_FILES['archivos']['tmp_name'][0]);
}

echo "<h3>Test Completado</h3>";
?>

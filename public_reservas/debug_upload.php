<?php
echo "<?php
/**
 * Debug detallado del sistema de upload de archivos
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si hay una sesión simulada o real
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    // Simular usuario autenticado para testing
    $_SESSION['login'] = true;
    $_SESSION['user_id'] = 1;
    $_SESSION['usuario'] = 'test_user';
    $_SESSION['user_name'] = 'Usuario Test';
    $_SESSION['user_email'] = 'test@clinica.com';
    
    $_SESSION['user_data'] = [
        'user_id' => 1,
        'usuario' => 'test_user',
        'nombre' => 'Usuario',
        'apellido' => 'Test',
        'email' => 'test@clinica.com',
        'documento' => '12345678',
        'telefono' => '123456789'
    ];
    
    echo "<div style='background: #ffffcc; padding: 10px; margin: 10px 0; border: 1px solid #ffeb3b;'>";
    echo "⚠️ Sesión de usuario simulada para testing";
    echo "</div>";
}

echo "<h2>Debug del Sistema de Upload de Archivos</h2>";

// Información del usuario autenticado
echo "<h3>👤 Estado de Autenticación</h3>";
echo "<p>Usuario logueado: " . ($_SESSION['login'] ? '✅ SÍ' : '❌ NO') . "</p>";
if (isset($_SESSION['user_data'])) {
    echo "<p>Datos del usuario:</p>";
    echo "<pre>" . print_r($_SESSION['user_data'], true) . "</pre>";
}";

echo "<h3>Datos POST:</h3>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h3>Datos FILES:</h3>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<h3>Información del servidor:</h3>";
echo "<pre>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "</pre>";

// Si hay archivos, mostrar información detallada
if (!empty($_FILES['archivos_reserva']['name'][0])) {
    echo "<h3>Archivos detectados:</h3>";
    for ($i = 0; $i < count($_FILES['archivos_reserva']['name']); $i++) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px;'>";
        echo "<strong>Archivo " . ($i + 1) . ":</strong><br>";
        echo "Nombre: " . $_FILES['archivos_reserva']['name'][$i] . "<br>";
        echo "Tipo: " . $_FILES['archivos_reserva']['type'][$i] . "<br>";
        echo "Tamaño: " . $_FILES['archivos_reserva']['size'][$i] . " bytes<br>";
        echo "Error: " . $_FILES['archivos_reserva']['error'][$i] . "<br>";
        echo "Temp: " . $_FILES['archivos_reserva']['tmp_name'][$i] . "<br>";
        echo "</div>";
    }
} else {
    echo "<p style='color: orange;'>No se detectaron archivos subidos.</p>";
}

// Verificar directorios de upload
$uploadDir = __DIR__ . "/uploads/reservas/";
echo "<h3>Directorio de uploads:</h3>";
echo "Ruta: " . $uploadDir . "<br>";
echo "Existe: " . (is_dir($uploadDir) ? "SÍ" : "NO") . "<br>";
echo "Escribible: " . (is_writable($uploadDir) ? "SÍ" : "NO") . "<br>";

if (isset($_POST['guardarReserva'])) {
    echo "<h3 style='color: red;'>SIMULACIÓN DE PROCESAMIENTO</h3>";
    
    // Simular el procesamiento como lo haría el controlador
    if (isset($_FILES['archivos_reserva']) && !empty($_FILES['archivos_reserva']['name'][0])) {
        echo "<p style='color: green;'>✅ Se detectaron archivos para procesar</p>";
        
        // Simular el procesamiento
        $codigoSeguimiento = 'TEST' . date('YmdHis') . rand(100, 999);
        $directorioReserva = $uploadDir . $codigoSeguimiento . "/";
        
        echo "Directorio de destino: " . $directorioReserva . "<br>";
        
        if (!file_exists($directorioReserva)) {
            if (mkdir($directorioReserva, 0755, true)) {
                echo "✅ Directorio creado correctamente<br>";
            } else {
                echo "❌ Error creando directorio<br>";
            }
        }
        
        // Intentar mover los archivos
        for ($i = 0; $i < count($_FILES['archivos_reserva']['name']); $i++) {
            if ($_FILES['archivos_reserva']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['archivos_reserva']['name'][$i];
                $tmpName = $_FILES['archivos_reserva']['tmp_name'][$i];
                $nombreSeguro = time() . "_" . $i . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '', $fileName);
                $rutaDestino = $directorioReserva . $nombreSeguro;
                
                echo "Intentando mover: " . $tmpName . " → " . $rutaDestino . "<br>";
                
                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    echo "✅ Archivo movido correctamente: " . $fileName . "<br>";
                } else {
                    echo "❌ Error moviendo archivo: " . $fileName . "<br>";
                }
            } else {
                echo "❌ Error en archivo: " . $_FILES['archivos_reserva']['name'][$i] . " (Error: " . $_FILES['archivos_reserva']['error'][$i] . ")<br>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ No se detectaron archivos para procesar</p>";
    }
}
?>

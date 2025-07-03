<?php
// Este script mostrará el estado de los archivos de log relacionados con el cambio de contraseña
// y verificará que los permisos estén configurados correctamente.

// Definir los archivos de log a verificar
$logFiles = [
    'password_changes.log' => dirname(__DIR__) . '/logs/password_changes.log',
    'application.log' => dirname(__DIR__) . '/logs/application.log',
    'test_password.log' => dirname(__DIR__) . '/logs/test_password.log',
    'diagnostico_password_root.log' => dirname(__DIR__) . '/logs/diagnostico_password_root.log',
    'diagnostico_password.log' => dirname(__DIR__) . '/logs/diagnostico_password.log',
];

// Función para verificar permisos de un archivo
function verificarPermisos($archivo) {
    if (!file_exists($archivo)) {
        return "No existe - intentando crear...";
    }
    
    $permisos = fileperms($archivo);
    $octal = substr(sprintf('%o', $permisos), -4);
    
    // Verificar si se puede escribir
    $writable = is_writable($archivo) ? "SÍ" : "NO";
    
    return "Existe (Permisos: $octal, Escribible: $writable)";
}

// Función para asegurar que un archivo de log existe y es escribible
function asegurarArchivo($archivo) {
    if (!file_exists($archivo)) {
        touch($archivo);
        @chmod($archivo, 0666); // Intentar dar permisos de escritura
    } else if (!is_writable($archivo)) {
        @chmod($archivo, 0666); // Intentar dar permisos de escritura
    }
    return verificarPermisos($archivo);
}

// Crear una tabla con el resultado
echo "<html><head><title>Estado de archivos de log - Cambio de Contraseña</title>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    h1, h2 { color: #333; }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    pre { background-color: #f5f5f5; padding: 10px; overflow: auto; max-height: 200px; }
</style>";
echo "</head><body>";

echo "<h1>Diagnóstico de archivos de log para el cambio de contraseña</h1>";

// Información del sistema
echo "<h2>Información del sistema</h2>";
echo "<table>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Web Server</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
echo "<tr><td>Script User</td><td>" . get_current_user() . "</td></tr>";
echo "<tr><td>Script Owner</td><td>" . (function_exists('posix_getpwuid') ? posix_getpwuid(fileowner(__FILE__))['name'] : 'No disponible') . "</td></tr>";
echo "</table>";

// Tabla de estado de archivos
echo "<h2>Estado de archivos de log</h2>";
echo "<table>";
echo "<tr><th>Archivo</th><th>Ruta</th><th>Estado</th><th>Después de corrección</th></tr>";

foreach ($logFiles as $nombre => $ruta) {
    echo "<tr>";
    echo "<td>$nombre</td>";
    echo "<td>$ruta</td>";
    echo "<td>" . verificarPermisos($ruta) . "</td>";
    echo "<td>" . asegurarArchivo($ruta) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Mostrar últimas 10 líneas de cada archivo de log
echo "<h2>Contenido reciente de archivos de log</h2>";

foreach ($logFiles as $nombre => $ruta) {
    echo "<h3>$nombre</h3>";
    
    if (file_exists($ruta) && is_readable($ruta)) {
        $contenido = file_get_contents($ruta);
        if (empty($contenido)) {
            echo "<p class='warning'>El archivo está vacío.</p>";
            // Intentar escribir algo en el archivo para verificar que se pueda
            $resultado = @file_put_contents($ruta, date('Y-m-d H:i:s') . " - Test de escritura desde diagnóstico\n", FILE_APPEND);
            echo $resultado !== false ? 
                "<p class='success'>Se ha añadido una línea de prueba correctamente.</p>" : 
                "<p class='error'>No se pudo escribir en el archivo. Revise los permisos.</p>";
        } else {
            // Mostrar las últimas 10 líneas
            $lineas = explode("\n", $contenido);
            $lineas = array_filter($lineas); // Eliminar líneas vacías
            $ultimasLineas = array_slice($lineas, -10);
            
            echo "<pre>";
            echo htmlspecialchars(implode("\n", $ultimasLineas));
            echo "</pre>";
        }
    } else {
        echo "<p class='error'>No se puede leer el archivo.</p>";
    }
}

// Verificar la configuración de permisos del controlador principal
$controllerFile = dirname(__DIR__) . '/controller/profile.controller.php';
echo "<h2>Verificación de archivo controller/profile.controller.php</h2>";
echo "<p>Archivo: " . $controllerFile . "</p>";
echo "<p>Estado: " . (file_exists($controllerFile) ? "Existe" : "No existe") . "</p>";
if (file_exists($controllerFile)) {
    echo "<p>Permisos: " . substr(sprintf('%o', fileperms($controllerFile)), -4) . "</p>";
    echo "<p>Tamaño: " . filesize($controllerFile) . " bytes</p>";
    echo "<p>Última modificación: " . date("Y-m-d H:i:s", filemtime($controllerFile)) . "</p>";
    
    // Verificar que el controlador tiene el método adecuado
    $contenidoController = file_get_contents($controllerFile);
    if (strpos($contenidoController, 'ctrChangePassword') !== false) {
        echo "<p class='success'>El método ctrChangePassword está presente en el controlador.</p>";
    } else {
        echo "<p class='error'>¡El método ctrChangePassword NO se encuentra en el controlador!</p>";
    }
}

// Información de la sesión actual
echo "<h2>Información de sesión</h2>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>Usuario en sesión: ID " . $_SESSION['user_id'] . "</p>";
    
    // Verificar si el usuario existe en la base de datos
    require_once dirname(__DIR__) . "/model/conexion.php";
    try {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT user_id, user_name, user_is_active, user_pass FROM sys_users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userData) {
            echo "<p class='success'>Usuario encontrado en la base de datos:</p>";
            echo "<table>";
            foreach ($userData as $key => $value) {
                // Ocultar la contraseña real
                if ($key === 'user_pass') {
                    $hashType = (strlen($value) === 32 && ctype_xdigit($value)) ? "MD5" : "BCRYPT/PASSWORD_HASH";
                    echo "<tr><td>$key</td><td>[HASH OCULTO - Tipo: $hashType]</td></tr>";
                } else {
                    echo "<tr><td>$key</td><td>$value</td></tr>";
                }
            }
            echo "</table>";
        } else {
            echo "<p class='error'>¡Usuario no encontrado en la base de datos!</p>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Error al consultar la base de datos: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p class='error'>No hay usuario en sesión</p>";
}

echo "<h2>Acciones de diagnóstico</h2>";
echo "<p>Se han intentado arreglar los permisos de los archivos de log. Para probar el cambio de contraseña, use alguno de estos enlaces:</p>";

echo "<ul>";
echo "<li><a href='test_cambio_password.html' target='_blank'>Página de prueba de cambio de contraseña</a></li>";
echo "<li><a href='test_password.php?currentPass=antigua&newPass=nueva123' target='_blank'>Ejecutar prueba automática de cambio</a></li>";
echo "</ul>";

echo "</body></html>";
?>

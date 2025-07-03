<?php
/**
 * Diagnóstico del sistema para el módulo de reservas
 * 
 * Este script realiza una verificación completa del entorno para
 * identificar problemas potenciales con el sistema de reservas.
 */

// Función para formatear una sección
function seccion($titulo) {
    echo "\n";
    echo "====================================================================\n";
    echo " $titulo \n";
    echo "====================================================================\n";
}

// Función para mostrar un ítem de verificación
function item($titulo, $valor, $ok = true) {
    $estado = $ok ? "[OK]" : "[ERROR]";
    echo sprintf("%-35s: %s %s\n", $titulo, $valor, $estado);
}

// Información básica
seccion("INFORMACIÓN DEL SISTEMA");
item("Fecha y hora", date('Y-m-d H:i:s'));
item("Versión de PHP", phpversion());
item("Sistema operativo", PHP_OS);
item("Modo de ejecución", php_sapi_name());
item("Directorio actual", getcwd());
item("Usuario", get_current_user());
item("Archivo de configuración PHP", php_ini_loaded_file());

// Verificar extensiones críticas
seccion("EXTENSIONES DE PHP");
$extensionesRequeridas = [
    'PDO',
    'pdo_pgsql',
    'json',
    'curl',
    'mbstring',
    'xml'
];

foreach ($extensionesRequeridas as $ext) {
    $cargada = extension_loaded($ext);
    item("Extensión $ext", $cargada ? "Cargada" : "No cargada", $cargada);
}

echo "\nTodas las extensiones cargadas:\n";
echo implode(", ", get_loaded_extensions());
echo "\n";

// Verificar directorios
seccion("DIRECTORIOS DEL SISTEMA");
$directorios = [
    'logs',
    'model',
    'controller',
    'view',
    'public_reservas',
    'ajax'
];

foreach ($directorios as $dir) {
    $path = __DIR__ . "/$dir";
    $existe = is_dir($path);
    item("Directorio $dir", $path, $existe);
    
    if ($existe && is_readable($path)) {
        item("  - Permisos", substr(sprintf('%o', fileperms($path)), -4));
    }
}

// Verificar archivos críticos
seccion("ARCHIVOS CRÍTICOS");
$archivos = [
    'model/conexion.php',
    'model/reservas.model.php',
    'controller/template.controller.php',
    'ajax/reservas.ajax.php',
    'config/config.php'
];

foreach ($archivos as $archivo) {
    $path = __DIR__ . "/$archivo";
    $existe = file_exists($path);
    item("Archivo $archivo", $existe ? "Existe" : "No existe", $existe);
    
    if ($existe) {
        item("  - Tamaño", filesize($path) . " bytes");
        item("  - Permisos", substr(sprintf('%o', fileperms($path)), -4));
        item("  - Última modificación", date("Y-m-d H:i:s", filemtime($path)));
    }
}

// Intentar conexión a la base de datos
seccion("PRUEBA DE BASE DE DATOS");
try {
    require_once __DIR__ . "/model/conexion.php";
    item("Archivo de conexión", "Cargado correctamente");
    
    $startTime = microtime(true);
    $pdo = Conexion::conectar();
    $endTime = microtime(true);
    $connectionTime = round($endTime - $startTime, 3);
    
    if ($pdo === null) {
        item("Conexión a la base de datos", "Falló - Devolvió NULL", false);
        
        // Verificar log de la base de datos
        $dbLogPath = __DIR__ . "/logs/database.log";
        if (file_exists($dbLogPath)) {
            item("Log de la base de datos", "Existe");
            echo "\nÚltimas líneas del log de la base de datos:\n";
            
            $logContent = file_get_contents($dbLogPath);
            $lines = explode("\n", $logContent);
            $lastLines = array_slice($lines, -10); // Últimas 10 líneas
            
            foreach ($lastLines as $line) {
                if (!empty(trim($line))) {
                    echo " > $line\n";
                }
            }
        }
    } else {
        item("Conexión a la base de datos", "Exitosa ($connectionTime s)");
        
        // Verificar información de la base de datos
        try {
            $dbInfo = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
            item("Versión de PostgreSQL", $dbInfo);
            
            // Verificar tabla de reservas
            $stmt = $pdo->query("SELECT COUNT(*) FROM servicios_reservas");
            $count = $stmt->fetchColumn();
            item("Tabla servicios_reservas", "$count reservas encontradas");
            
            // Verificar estados de reservas
            $stmt = $pdo->query("SELECT reserva_estado, COUNT(*) as total FROM servicios_reservas GROUP BY reserva_estado");
            echo "\nDistribución de estados de reservas:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo " - {$row['reserva_estado']}: {$row['total']}\n";
            }
        } catch (PDOException $e) {
            item("Consulta a la base de datos", "Falló: " . $e->getMessage(), false);
        }
    }
} catch (Exception $e) {
    item("Prueba de base de datos", "Error: " . $e->getMessage(), false);
}

// Verificar configuración de mantenimiento
seccion("CONFIGURACIÓN DE MANTENIMIENTO");
$configPath = __DIR__ . "/config/mantenimiento_reservas.php";
if (file_exists($configPath)) {
    item("Archivo de configuración", "Existe");
    
    try {
        $config = require $configPath;
        
        item("Mantenimiento habilitado", $config['enabled'] ? "Sí" : "No");
        item("Email de administrador", $config['admin_email'] ?? "No configurado");
        item("Ruta de logs", $config['log_path'] ?? "No configurada");
        
        if (isset($config['scheduled_tasks'])) {
            echo "\nTareas programadas:\n";
            foreach ($config['scheduled_tasks'] as $taskName => $taskConfig) {
                echo " - $taskName: " . ($taskConfig['enabled'] ? "Habilitada" : "Deshabilitada") . "\n";
            }
        }
    } catch (Exception $e) {
        item("Carga de configuración", "Error: " . $e->getMessage(), false);
    }
} else {
    item("Archivo de configuración", "No existe", false);
}

// Resumen y recomendaciones
seccion("RESUMEN Y RECOMENDACIONES");

$problemas = [];

// Verificar extensión PostgreSQL
if (!extension_loaded('pdo_pgsql')) {
    $problemas[] = "La extensión pdo_pgsql no está cargada. Esto impide la conexión a la base de datos.";
}

// Verificar permisos de directorios críticos
$logDir = __DIR__ . "/logs";
if (!is_dir($logDir) || !is_writable($logDir)) {
    $problemas[] = "El directorio de logs no existe o no tiene permisos de escritura.";
}

// Mostrar problemas encontrados
if (empty($problemas)) {
    echo "No se encontraron problemas críticos en el sistema.\n";
} else {
    echo "Se encontraron los siguientes problemas:\n";
    foreach ($problemas as $i => $problema) {
        echo ($i + 1) . ". $problema\n";
    }
    
    echo "\nRecomendaciones:\n";
    
    if (!extension_loaded('pdo_pgsql')) {
        echo "- Habilite la extensión pdo_pgsql en PHP:\n";
        echo "  1. Abra el archivo php.ini: " . php_ini_loaded_file() . "\n";
        echo "  2. Busque la línea ';extension=pdo_pgsql' y quite el punto y coma del inicio\n";
        echo "  3. Si la línea no existe, agréguela: 'extension=pdo_pgsql'\n";
        echo "  4. Reinicie el servidor web\n";
        echo "  5. Acceda a " . (!empty($_SERVER['HTTP_HOST']) ? "http://{$_SERVER['HTTP_HOST']}/clinica/check_and_enable_pgsql.php" : "check_and_enable_pgsql.php") . " para más detalles\n";
    }
    
    if (!is_dir($logDir) || !is_writable($logDir)) {
        echo "- Cree o corrija los permisos del directorio de logs:\n";
        echo "  1. Asegúrese de que existe el directorio: $logDir\n";
        echo "  2. Establezca permisos de escritura para el usuario del servidor web\n";
    }
}

// Fin
seccion("FIN DEL DIAGNÓSTICO");
echo "Diagnóstico completado: " . date('Y-m-d H:i:s') . "\n";
echo "\nPuede copiar esta información para compartirla con el equipo de soporte.\n";

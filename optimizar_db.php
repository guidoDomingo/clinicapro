<?php
/**
 * Script para optimizar la base de datos
 * Este script realiza tareas de mantenimiento y optimización en la base de datos
 * 
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 */

// Cargar configuración de la base de datos
if (file_exists("config/config.php")) {
    require_once "config/config.php";
} else {
    die("Error: Archivo de configuración no encontrado");
}

// Definir constantes de la base de datos si no existen
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'clinica');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

// Archivo de log
$logFile = "logs/db_optimize_" . date('Y-m-d') . ".log";

// Función para registrar mensajes
function log_message($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}

// Conectar a la base de datos
function conectarDB() {
    try {
        $link = new PDO(
            "mysql:host=".DB_HOST.";dbname=".DB_NAME, 
            DB_USER, 
            DB_PASS,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
        );
        $link->exec("set names utf8");
        return $link;
    } catch (PDOException $e) {
        log_message("ERROR: No se pudo conectar a la base de datos: " . $e->getMessage());
        die();
    }
}

// Iniciar proceso
log_message("=== INICIANDO OPTIMIZACIÓN DE BASE DE DATOS ===");
$link = conectarDB();

// 1. Obtener lista de tablas
log_message("\nObteniendo lista de tablas...");
$tables = array();

try {
    $stmt = $link->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    log_message("Se encontraron " . count($tables) . " tablas");
} catch (PDOException $e) {
    log_message("ERROR: No se pudieron obtener las tablas: " . $e->getMessage());
    die();
}

// 2. Analizar y optimizar cada tabla
log_message("\n=== ANALIZANDO Y OPTIMIZANDO TABLAS ===");
$optimizedTables = 0;
$repairedTables = 0;
$errors = 0;

foreach ($tables as $table) {
    log_message("\nProcesando tabla: $table");
    
    // 2.1. Verificar estado de la tabla
    try {
        log_message("  Verificando estado...");
        $stmt = $link->query("CHECK TABLE `$table`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['Msg_text'] == 'OK') {
            log_message("  Estado: OK");
        } else {
            log_message("  Estado: " . $result['Msg_text']);
            
            // 2.2. Reparar tabla si es necesario
            log_message("  Reparando tabla...");
            $stmt = $link->query("REPAIR TABLE `$table`");
            $repairResult = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($repairResult['Msg_text'] == 'OK') {
                log_message("  Reparación exitosa");
                $repairedTables++;
            } else {
                log_message("  ERROR: No se pudo reparar la tabla: " . $repairResult['Msg_text']);
                $errors++;
            }
        }
        
        // 2.3. Optimizar tabla
        log_message("  Optimizando tabla...");
        $stmt = $link->query("OPTIMIZE TABLE `$table`");
        $optResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($optResult['Msg_text'] == 'OK' || $optResult['Msg_text'] == 'Table is already up to date') {
            log_message("  Optimización exitosa");
            $optimizedTables++;
        } else {
            log_message("  Mensaje: " . $optResult['Msg_text']);
        }
        
    } catch (PDOException $e) {
        log_message("  ERROR: Problema al procesar la tabla $table: " . $e->getMessage());
        $errors++;
    }
}

// 3. Actualizar estadísticas de índices
log_message("\n=== ACTUALIZANDO ESTADÍSTICAS DE ÍNDICES ===");

try {
    $stmt = $link->query("ANALYZE TABLE " . implode(', ', array_map(function($table) { return "`$table`"; }, $tables)));
    log_message("Estadísticas de índices actualizadas correctamente");
} catch (PDOException $e) {
    log_message("ERROR: No se pudieron actualizar las estadísticas de índices: " . $e->getMessage());
    $errors++;
}

// 4. Resumen
log_message("\n=== RESUMEN DE LA OPTIMIZACIÓN ===");
log_message("Total de tablas procesadas: " . count($tables));
log_message("Tablas optimizadas: $optimizedTables");
log_message("Tablas reparadas: $repairedTables");
log_message("Errores encontrados: $errors");
log_message("\nProceso completado. Revise el log para más detalles: $logFile");

// Cerrar conexión
$link = null;

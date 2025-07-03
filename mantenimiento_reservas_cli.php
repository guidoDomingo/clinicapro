<?php
/**
 * Script para mantenimiento de reservas - Versión simplificada para CLI
 * 
 * Uso:
 * php mantenimiento_reservas_cli.php [--dias=30]
 * 
 * Opciones:
 * --dias=N    Número de días para mantener las reservas canceladas (por defecto: 30)
 * --debug     Muestra información detallada durante la ejecución
 * --help      Muestra esta ayuda
 * 
 * Este script realiza tareas de mantenimiento en la tabla de reservas:
 * - Actualiza estados de reservas antiguas
 * - Marca como expiradas las reservas no confirmadas pasadas
 * - Archiva reservas antiguas
 */

// Verificar si se ejecuta desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos.\n");
}

// Procesar argumentos
$opciones = [
    'dias' => 30,
    'debug' => false,
    'help' => false
];

foreach ($argv as $arg) {
    if (strpos($arg, '--dias=') === 0) {
        $opciones['dias'] = (int) substr($arg, 7);
    } elseif ($arg === '--debug') {
        $opciones['debug'] = true;
    } elseif ($arg === '--help') {
        $opciones['help'] = true;
    }
}

// Mostrar ayuda si se solicita
if ($opciones['help']) {
    echo "Script para mantenimiento de reservas - Versión simplificada para CLI\n\n";
    echo "Uso:\n";
    echo "  php mantenimiento_reservas_cli.php [--dias=30] [--debug] [--help]\n\n";
    echo "Opciones:\n";
    echo "  --dias=N    Número de días para mantener las reservas canceladas (por defecto: 30)\n";
    echo "  --debug     Muestra información detallada durante la ejecución\n";
    echo "  --help      Muestra esta ayuda\n";
    exit(0);
}

// Función para registrar mensajes
function log_mensaje($mensaje, $nivel = 'INFO') {
    global $opciones;
    
    $fecha = date('Y-m-d H:i:s');
    $mensajeFormateado = "[$fecha][$nivel] $mensaje\n";
    
    echo $mensajeFormateado;
    
    // Guardar en archivo log si no es modo debug
    if (!$opciones['debug']) {
        $logFile = __DIR__ . '/logs/mantenimiento_cli_' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $mensajeFormateado, FILE_APPEND);
    }
}

// Verificar y crear directorio de logs si es necesario
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0755, true)) {
        echo "ERROR: No se pudo crear el directorio de logs.\n";
        exit(1);
    }
}

log_mensaje("Iniciando mantenimiento de reservas");
log_mensaje("Configuración: dias=" . $opciones['dias'] . ", debug=" . ($opciones['debug'] ? "sí" : "no"));

// Intentar cargar las dependencias
try {
    require_once __DIR__ . "/model/conexion.php";
    log_mensaje("Clase Conexion cargada correctamente");
} catch (Exception $e) {
    log_mensaje("Error al cargar dependencias: " . $e->getMessage(), 'ERROR');
    exit(1);
}

// Intentar establecer conexión a la base de datos
try {
    log_mensaje("Conectando a la base de datos...");
    $pdo = Conexion::conectar();
    
    if ($pdo === null) {
        log_mensaje("La conexión a la base de datos devolvió NULL. Verifique el archivo database.log para más detalles.", 'ERROR');
        exit(1);
    }
    
    log_mensaje("Conexión establecida correctamente", 'SUCCESS');
    
    // Verificar la conexión con una consulta simple
    $stmt = $pdo->query("SELECT 1");
    $stmt->fetch();
    log_mensaje("Consulta de prueba ejecutada correctamente", 'SUCCESS');
} catch (PDOException $e) {
    log_mensaje("Error al conectar a la base de datos: " . $e->getMessage(), 'ERROR');
    exit(1);
}

// Ejecutar tarea 1: Actualizar estados de reservas antiguas
try {
    log_mensaje("Actualizando estados de reservas antiguas...");
    
    // Marcar como NO ASISTIO las reservas pasadas que siguen en CONFIRMADA
    $stmt = $pdo->prepare(
        "UPDATE servicios_reservas 
         SET reserva_estado = 'NO ASISTIO', 
             updated_at = NOW(),
             observaciones = COALESCE(observaciones, '') || E'\n' || 'Actualización automática a NO ASISTIO por sistema de mantenimiento el ' || NOW()
         WHERE 
            CONCAT(fecha_reserva, ' ', hora_fin) < NOW() - INTERVAL '24 hours' 
            AND reserva_estado = 'CONFIRMADA'
         RETURNING reserva_id"
    );
    
    $stmt->execute();
    
    $reservasActualizadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totalActualizadas = count($reservasActualizadas);
    
    log_mensaje("Reservas actualizadas a NO ASISTIO: $totalActualizadas", 'SUCCESS');
    
    if ($totalActualizadas > 0 && $opciones['debug']) {
        log_mensaje("IDs de reservas actualizadas: " . implode(', ', $reservasActualizadas));
    }
} catch (PDOException $e) {
    log_mensaje("Error al actualizar estados de reservas: " . $e->getMessage(), 'ERROR');
}

// Ejecutar tarea 2: Marcar como expiradas las reservas no confirmadas pasadas
try {
    log_mensaje("Marcando como expiradas las reservas no confirmadas pasadas...");
    
    // Actualizar estado de reservas no confirmadas y pasadas
    $stmt = $pdo->prepare(
        "UPDATE servicios_reservas 
         SET reserva_estado = 'EXPIRADA',
             updated_at = NOW() 
         WHERE reserva_estado = 'PENDIENTE'
         AND fecha_reserva < CURRENT_DATE
         RETURNING reserva_id"
    );
    
    $stmt->execute();
    
    $reservasExpiradas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totalExpiradas = count($reservasExpiradas);
    
    log_mensaje("Reservas marcadas como EXPIRADA: $totalExpiradas", 'SUCCESS');
    
    if ($totalExpiradas > 0 && $opciones['debug']) {
        log_mensaje("IDs de reservas expiradas: " . implode(', ', $reservasExpiradas));
    }
} catch (PDOException $e) {
    log_mensaje("Error al marcar reservas expiradas: " . $e->getMessage(), 'ERROR');
}

// Ejecutar tarea 3: Eliminar o archivar reservas canceladas antiguas
try {
    log_mensaje("Gestionando reservas canceladas antiguas...");
    
    // Archivar las reservas canceladas antiguas
    $diasLimite = $opciones['dias'];
    $fechaLimite = date('Y-m-d', strtotime("-$diasLimite days"));
    
    // En PostgreSQL, el placeholder para parámetros en INTERVAL debe usar la sintaxis específica
    $stmt = $pdo->prepare(
        "UPDATE servicios_reservas 
         SET archivada = TRUE,
             updated_at = NOW() 
         WHERE 
            fecha_reserva < :fecha_limite
            AND reserva_estado = 'CANCELADA'
            AND (archivada IS NULL OR archivada = FALSE)
         RETURNING reserva_id"
    );
    
    $stmt->bindParam(':fecha_limite', $fechaLimite);
    $stmt->execute();
    
    $reservasArchivadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totalArchivadas = count($reservasArchivadas);
    
    log_mensaje("Reservas canceladas archivadas: $totalArchivadas", 'SUCCESS');
    
    if ($totalArchivadas > 0 && $opciones['debug']) {
        log_mensaje("IDs de reservas archivadas: " . implode(', ', $reservasArchivadas));
    }
} catch (PDOException $e) {
    log_mensaje("Error al gestionar reservas canceladas antiguas: " . $e->getMessage(), 'ERROR');
}

// Ejecutar tarea 4: Verificar integridad de datos
if ($opciones['debug']) {
    try {
        log_mensaje("Verificando integridad de datos...");
        
        // Verificar reservas con solapamientos de horarios
        $stmt = $pdo->query(
            "SELECT 
                r1.reserva_id as reserva1, 
                r2.reserva_id as reserva2,
                r1.fecha_reserva,
                r1.hora_inicio as hora_inicio1,
                r1.hora_fin as hora_fin1,
                r2.hora_inicio as hora_inicio2,
                r2.hora_fin as hora_fin2
             FROM 
                servicios_reservas r1
             JOIN 
                servicios_reservas r2 
             ON 
                r1.doctor_id = r2.doctor_id
                AND r1.fecha_reserva = r2.fecha_reserva
                AND r1.reserva_id < r2.reserva_id
                AND r1.reserva_estado NOT IN ('CANCELADA')
                AND r2.reserva_estado NOT IN ('CANCELADA')
                AND (
                    (r1.hora_inicio <= r2.hora_inicio AND r1.hora_fin > r2.hora_inicio)
                    OR
                    (r1.hora_inicio < r2.hora_fin AND r1.hora_fin >= r2.hora_fin)
                    OR
                    (r1.hora_inicio >= r2.hora_inicio AND r1.hora_fin <= r2.hora_fin)
                )"
        );
        
        $reservasSolapadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($reservasSolapadas) > 0) {
            log_mensaje("Se encontraron " . count($reservasSolapadas) . " solapamientos de horarios:", 'WARNING');
            
            foreach ($reservasSolapadas as $solape) {
                log_mensaje("Conflicto entre reservas {$solape['reserva1']} y {$solape['reserva2']} en fecha {$solape['fecha_reserva']}: " .
                          "{$solape['hora_inicio1']}-{$solape['hora_fin1']} vs {$solape['hora_inicio2']}-{$solape['hora_fin2']}", 'WARNING');
            }
        } else {
            log_mensaje("No se encontraron solapamientos de horarios.", 'SUCCESS');
        }
    } catch (PDOException $e) {
        log_mensaje("Error al verificar integridad: " . $e->getMessage(), 'ERROR');
    }
}

log_mensaje("Proceso de mantenimiento completado", 'SUCCESS');

// Salir con código 0 (éxito)
exit(0);

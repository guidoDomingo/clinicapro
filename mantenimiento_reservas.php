<?php
/**
 * Script de mantenimiento del módulo de reservas
 * 
 * Este script ejecuta las tareas de mantenimiento programadas para el módulo
 * de servicios y reservas, incluyendo limpieza, respaldo y verificación de
 * integridad de datos.
 * 
 * Uso desde línea de comandos:
 * php mantenimiento_reservas.php [--force] [--task=nombre_tarea]
 * 
 * @author Sistema Clínico
 * @version 1.0
 */

// Definir constante para indicar que se está ejecutando el mantenimiento
define('MAINTENANCE_INCLUDED', true);

// Cargar configuración
$configFile = __DIR__ . '/config/mantenimiento_reservas.php';
if (!file_exists($configFile)) {
    die("Error: Archivo de configuración no encontrado.\n");
}

$config = require $configFile;

// Si el mantenimiento está deshabilitado y no se fuerza la ejecución, salir
if (!$config['enabled'] && !in_array('--force', $argv)) {
    die("Mantenimiento programado deshabilitado. Use --force para ejecutar de todas formas.\n");
}

// Crear directorio de logs si no existe
if (!is_dir($config['log_path'])) {
    mkdir($config['log_path'], 0755, true);
}

// Archivo de log para este proceso
$logFile = $config['log_path'] . 'mantenimiento_' . date('Y-m-d') . '.log';

/**
 * Función para registrar mensajes en el log
 * 
 * @param string $mensaje Mensaje a registrar
 * @param string $tipo Tipo de mensaje (INFO, ERROR, SUCCESS)
 */
function logMensaje($mensaje, $tipo = 'INFO') {
    global $logFile;
    
    $fecha = date('Y-m-d H:i:s');
    $mensaje = "[$fecha][$tipo] $mensaje" . PHP_EOL;
    
    // Mostrar en consola si se ejecuta desde línea de comandos
    if (php_sapi_name() === 'cli') {
        echo $mensaje;
    }
    
    // Guardar en archivo de log
    file_put_contents($logFile, $mensaje, FILE_APPEND);
}

/**
 * Función para enviar notificación por email al administrador
 * 
 * @param string $asunto Asunto del email
 * @param string $mensaje Contenido del mensaje
 * @return bool Resultado del envío
 */
function notificarAdmin($asunto, $mensaje) {
    global $config;
    
    $cabeceras = "MIME-Version: 1.0" . "\r\n";
    $cabeceras .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $cabeceras .= "From: Sistema de Mantenimiento <noreply@clinica.com>" . "\r\n";
    
    return mail($config['admin_email'], $asunto, $mensaje, $cabeceras);
}

// Comenzar el proceso de mantenimiento
logMensaje("Iniciando proceso de mantenimiento del módulo de reservas");
$tiempoInicio = microtime(true);

/**
 * Clase para mantenimiento de reservas
 * 
 * Esta clase encapsula todas las operaciones de mantenimiento
 * para el sistema de reservas
 */
class MantenimientoReservas {
    /**
     * Conexión a la base de datos
     * @var PDO
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        try {
            require_once __DIR__ . "/model/conexion.php";
            $this->db = Conexion::conectar();
            
            // Verificar si la conexión se estableció correctamente
            if ($this->db === null) {
                throw new Exception("La conexión a la base de datos devolvió NULL. Verifica el archivo database.log para más detalles.");
            }
            
            logMensaje("Conexión a base de datos establecida correctamente", "SUCCESS");
        } catch (Exception $e) {
            logMensaje("Error al conectar con la base de datos: " . $e->getMessage(), "ERROR");
            notificarAdmin(
                "ERROR: Mantenimiento de reservas - Fallo en conexión a base de datos", 
                "El proceso de mantenimiento no pudo conectar a la base de datos: " . $e->getMessage()
            );
            die();
        }
    }
    
    /**
     * Obtener la conexión a la base de datos
     * 
     * @return PDO Conexión a la base de datos
     */
    public function getConexion() {
        return $this->db;
    }
    
    /**
     * Verifica que la conexión a la base de datos esté activa
     * 
     * @return bool True si la conexión está activa
     */
    public function verificarConexion() {
        if (!$this->db) {
            logMensaje("La conexión a la base de datos no está disponible", "ERROR");
            return false;
        }
        
        try {
            $this->db->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            logMensaje("Error al verificar la conexión: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
    
    /**
     * Ejecuta una consulta de limpieza en la base de datos
     * 
     * @param string $sql Consulta SQL a ejecutar
     * @param array $params Parámetros para la consulta
     * @return array Resultado de la operación
     */
    public function ejecutarLimpieza($sql, $params = []) {
        $resultado = [
            'ejecutado' => false,
            'registros_afectados' => 0,
            'error' => null,
            'resultado' => null
        ];
        
        try {
            if (!$this->db) {
                throw new Exception("No hay conexión a base de datos disponible");
            }
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $param => $value) {
                $tipo = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($param, $value, $tipo);
            }
            
            $stmt->execute();
            $resultado['ejecutado'] = true;
            $resultado['registros_afectados'] = $stmt->rowCount();
            
            // Intentar obtener resultados si es un SELECT
            try {
                $resultado['resultado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                // No es un SELECT, no hay problema
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            $resultado['error'] = $e->getMessage();
            logMensaje("Error al ejecutar consulta SQL: " . $e->getMessage(), "ERROR");
            return $resultado;
        }
    }
}

// Inicializar la clase de mantenimiento
$mantenimiento = new MantenimientoReservas();

// Verificar que la conexión esté activa antes de continuar
if (!$mantenimiento->verificarConexion()) {
    logMensaje("No se puede continuar el mantenimiento debido a problemas con la conexión a la base de datos", "ERROR");
    die();
}

// Verificar si se especificó una tarea específica
$tareaEspecifica = null;
foreach ($argv as $arg) {
    if (strpos($arg, '--task=') === 0) {
        $tareaEspecifica = substr($arg, 7);
        logMensaje("Se ejecutará solo la tarea: $tareaEspecifica");
        break;
    }
}

// 1. Actualización de estados de reservas antiguas
if ($config['scheduled_tasks']['update_old_reservations']['enabled'] && 
    (!$tareaEspecifica || $tareaEspecifica === 'update_old_reservations')) {
    
    logMensaje("Ejecutando: Actualización de estados de reservas antiguas");
    
    try {
        $db = $mantenimiento->getConexion();
        
        // Marcar como NO ASISTIO las reservas pasadas que siguen en CONFIRMADA
        $horasNoAsistio = $config['scheduled_tasks']['update_old_reservations']['mark_as_no_show_after_hours'];
        $fechaLimite = date('Y-m-d H:i:s', strtotime("-{$horasNoAsistio} hours"));
        
        $stmt = $db->prepare(
            "UPDATE servicios_reservas 
             SET reserva_estado = 'NO ASISTIO', 
                 updated_at = NOW(),
                 observaciones = COALESCE(observaciones, '') || E'\n' || 'Actualización automática a NO ASISTIO por sistema de mantenimiento el ' || NOW()
             WHERE 
                CONCAT(fecha_reserva, ' ', hora_fin) < :fecha_limite
                AND reserva_estado = 'CONFIRMADA'
             RETURNING reserva_id"
        );
        
        $stmt->bindParam(':fecha_limite', $fechaLimite);
        $stmt->execute();
        
        $reservasActualizadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $totalActualizadas = count($reservasActualizadas);
        
        logMensaje("Reservas actualizadas a NO ASISTIO: $totalActualizadas", "SUCCESS");
        
        if ($totalActualizadas > 0) {
            logMensaje("IDs de reservas actualizadas: " . implode(', ', $reservasActualizadas));
        }
    } catch (PDOException $e) {
        logMensaje("Error al actualizar estados de reservas: " . $e->getMessage(), "ERROR");
    }
}

// 2. Respaldo de datos de reservas
if ($config['backup']['enabled'] && 
    (!$tareaEspecifica || $tareaEspecifica === 'backup')) {
    
    logMensaje("Ejecutando: Respaldo de datos de reservas");
    
    // Crear directorio de respaldos si no existe
    if (!is_dir($config['backup']['path'])) {
        mkdir($config['backup']['path'], 0755, true);
    }
    
    $fechaRespaldo = date('Y-m-d_H-i-s');
    $archivoRespaldo = $config['backup']['path'] . "reservas_$fechaRespaldo.sql";
    
    try {
        $db = $mantenimiento->getConexion();
        
        // Obtener datos de reservas para respaldo
        $stmt = $db->query(
            "SELECT 
                sr.*,
                to_json(rp.*) as paciente_datos,
                to_json(dr.*) as doctor_datos,
                to_json(s.*) as servicio_datos
             FROM 
                servicios_reservas sr
             LEFT JOIN 
                rh_person rp ON sr.paciente_id = rp.person_id
             LEFT JOIN 
                (SELECT rd.doctor_id, rp.* FROM rh_doctors rd JOIN rh_person rp ON rd.person_id = rp.person_id) dr 
                ON sr.doctor_id = dr.doctor_id
             LEFT JOIN 
                rs_servicios s ON sr.servicio_id = s.serv_id"
        );
        
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Guardar en archivo
        file_put_contents($archivoRespaldo, json_encode($reservas, JSON_PRETTY_PRINT));
        
        logMensaje("Respaldo creado correctamente: $archivoRespaldo", "SUCCESS");
        logMensaje("Total de registros respaldados: " . count($reservas));
        
        // Eliminar respaldos antiguos
        $diasRetencion = $config['backup']['retention_days'];
        $fechaLimite = strtotime("-{$diasRetencion} days");
        
        foreach (glob($config['backup']['path'] . "reservas_*.sql") as $archivo) {
            if (filemtime($archivo) < $fechaLimite) {
                unlink($archivo);
                logMensaje("Respaldo antiguo eliminado: " . basename($archivo));
            }
        }
    } catch (Exception $e) {
        logMensaje("Error al crear respaldo: " . $e->getMessage(), "ERROR");
    }
}

// 3. Limpieza de registros antiguos
if ($config['cleanup']['enabled'] && 
    (!$tareaEspecifica || $tareaEspecifica === 'cleanup')) {
    
    logMensaje("Ejecutando: Limpieza de registros antiguos");
    
    try {
        $db = $mantenimiento->getConexion();
        
        // Archivado de reservas muy antiguas
        $diasCompletadas = $config['cleanup']['completed_reservations_retention_days'];
        $fechaLimiteCompletadas = date('Y-m-d', strtotime("-{$diasCompletadas} days"));
        
        $stmt = $db->prepare(
            "UPDATE servicios_reservas 
             SET archivada = TRUE,
                 updated_at = NOW()
             WHERE 
                fecha_reserva < :fecha_limite
                AND reserva_estado IN ('COMPLETADA', 'ATENDIDA')
                AND (archivada IS NULL OR archivada = FALSE)
             RETURNING reserva_id"
        );
        
        $stmt->bindParam(':fecha_limite', $fechaLimiteCompletadas);
        $stmt->execute();
        
        $reservasArchivadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $totalArchivadas = count($reservasArchivadas);
        
        logMensaje("Reservas completadas archivadas: $totalArchivadas", "SUCCESS");
        
        // Archivado de reservas canceladas antiguas
        $diasCanceladas = $config['cleanup']['cancelled_reservations_retention_days'];
        $fechaLimiteCanceladas = date('Y-m-d', strtotime("-{$diasCanceladas} days"));
        
        $stmt = $db->prepare(
            "UPDATE servicios_reservas 
             SET archivada = TRUE,
                 updated_at = NOW()
             WHERE 
                fecha_reserva < :fecha_limite
                AND reserva_estado IN ('CANCELADA', 'NO ASISTIO')
                AND (archivada IS NULL OR archivada = FALSE)
             RETURNING reserva_id"
        );
        
        $stmt->bindParam(':fecha_limite', $fechaLimiteCanceladas);
        $stmt->execute();
        
        $reservasCancelArchivadas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $totalCancelArchivadas = count($reservasCancelArchivadas);
        
        logMensaje("Reservas canceladas archivadas: $totalCancelArchivadas", "SUCCESS");
        
        // Limpieza de logs antiguos
        $diasLogs = $config['cleanup']['logs_retention_days'];
        $fechaLimiteLogs = strtotime("-{$diasLogs} days");
        $contadorLogs = 0;
        
        foreach (glob($config['log_path'] . "*.log") as $archivo) {
            if (filemtime($archivo) < $fechaLimiteLogs) {
                unlink($archivo);
                $contadorLogs++;
            }
        }
        
        logMensaje("Archivos de log eliminados: $contadorLogs", "SUCCESS");
        
    } catch (PDOException $e) {
        logMensaje("Error en proceso de limpieza: " . $e->getMessage(), "ERROR");
    }
}

// 4. Verificación de integridad
if ($config['scheduled_tasks']['integrity_check']['enabled'] && 
    (!$tareaEspecifica || $tareaEspecifica === 'integrity_check')) {
    
    logMensaje("Ejecutando: Verificación de integridad de datos");
    
    $erroresIntegridad = [];
    
    try {
        $db = $mantenimiento->getConexion();
        
        // Verificar reservas sin paciente válido
        $stmt = $db->query(
            "SELECT sr.reserva_id
             FROM servicios_reservas sr
             LEFT JOIN rh_person rp ON sr.paciente_id = rp.person_id
             WHERE sr.paciente_id IS NOT NULL 
             AND rp.person_id IS NULL"
        );
        
        $reservasSinPaciente = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($reservasSinPaciente) > 0) {
            $msg = "Reservas con paciente inexistente: " . count($reservasSinPaciente);
            logMensaje($msg, "ERROR");
            $erroresIntegridad[] = $msg;
        }
        
        // Verificar reservas sin médico válido
        $stmt = $db->query(
            "SELECT sr.reserva_id
             FROM servicios_reservas sr
             LEFT JOIN rh_doctors rd ON sr.doctor_id = rd.doctor_id
             WHERE sr.doctor_id IS NOT NULL 
             AND rd.doctor_id IS NULL"
        );
        
        $reservasSinMedico = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($reservasSinMedico) > 0) {
            $msg = "Reservas con médico inexistente: " . count($reservasSinMedico);
            logMensaje($msg, "ERROR");
            $erroresIntegridad[] = $msg;
        }
        
        // Verificar reservas sin servicio válido
        $stmt = $db->query(
            "SELECT sr.reserva_id
             FROM servicios_reservas sr
             LEFT JOIN rs_servicios rs ON sr.servicio_id = rs.serv_id
             WHERE sr.servicio_id IS NOT NULL 
             AND rs.serv_id IS NULL"
        );
        
        $reservasSinServicio = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($reservasSinServicio) > 0) {
            $msg = "Reservas con servicio inexistente: " . count($reservasSinServicio);
            logMensaje($msg, "ERROR");
            $erroresIntegridad[] = $msg;
        }
        
        // Verificar solapamientos de horarios
        $stmt = $db->query(
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
            $msg = "Reservas con solapamiento de horarios: " . count($reservasSolapadas);
            logMensaje($msg, "ERROR");
            $erroresIntegridad[] = $msg;
            
            foreach ($reservasSolapadas as $solape) {
                logMensaje("Conflicto entre reservas {$solape['reserva1']} y {$solape['reserva2']} en fecha {$solape['fecha_reserva']}: ".
                          "{$solape['hora_inicio1']}-{$solape['hora_fin1']} vs {$solape['hora_inicio2']}-{$solape['hora_fin2']}", "ERROR");
            }
        }
        
        // Si hay errores de integridad, notificar al administrador
        if (!empty($erroresIntegridad)) {
            $mensaje = "<h2>Errores de Integridad Detectados</h2><ul>";
            foreach ($erroresIntegridad as $error) {
                $mensaje .= "<li>$error</li>";
            }
            $mensaje .= "</ul><p>Por favor revise el log completo para más detalles.</p>";
            
            notificarAdmin(
                "ALERTA: Problemas de integridad en módulo de reservas", 
                $mensaje
            );
        } else {
            logMensaje("Verificación de integridad completada sin errores", "SUCCESS");
        }
        
    } catch (PDOException $e) {
        logMensaje("Error en verificación de integridad: " . $e->getMessage(), "ERROR");
    }
}

// Finalizar el proceso
$tiempoFin = microtime(true);
$tiempoEjecucion = round($tiempoFin - $tiempoInicio, 2);
logMensaje("Proceso de mantenimiento completado en $tiempoEjecucion segundos", "SUCCESS");

// Si se ejecuta desde línea de comandos, mostrar resumen
if (php_sapi_name() === 'cli') {
    echo "\n=== Resumen de Mantenimiento ===\n";
    echo "Duración: $tiempoEjecucion segundos\n";
    echo "Log guardado en: $logFile\n\n";
}
?>

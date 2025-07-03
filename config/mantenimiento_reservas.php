<?php
/**
 * Configuración de mantenimiento programado para el módulo de reservas
 * 
 * Este archivo define las tareas de mantenimiento automático para el módulo
 * de servicios y reservas del sistema clínico.
 * 
 * @author Sistema Clínico
 * @version 1.0
 */

// Configuración general
$config = [
    // Habilitar o deshabilitar el mantenimiento programado
    'enabled' => true,
    
    // Ruta base para archivos de log
    'log_path' => __DIR__ . '/logs/',
    
    // Correo electrónico para notificaciones
    'admin_email' => 'admin@clinica.com',
    
    // Configuración de respaldo
    'backup' => [
        'enabled' => true,
        'path' => __DIR__ . '/backups/reservas/',
        'retention_days' => 30, // Días que se conservan los respaldos
    ],
    
    // Limpieza de registros antiguos
    'cleanup' => [
        'enabled' => true,
        'logs_retention_days' => 90, // Retener logs por 90 días
        'completed_reservations_retention_days' => 365, // Mantener reservas completadas por 1 año
        'cancelled_reservations_retention_days' => 180, // Mantener reservas canceladas por 6 meses
    ],
    
    // Notificaciones automáticas
    'notifications' => [
        'reminder_enabled' => true,
        'reminder_hours_before' => 24, // Enviar recordatorio 24 horas antes
        'whatsapp_enabled' => true,
        'email_enabled' => true,
    ],
    
    // Tareas de mantenimiento programadas
    'scheduled_tasks' => [
        // Actualización de estados de reservas antiguas
        'update_old_reservations' => [
            'enabled' => true,
            'mark_as_no_show_after_hours' => 12, // Marcar como no asistió después de 12 horas
            'auto_cancel_before_hours' => 2, // Permitir auto-cancelación hasta 2 horas antes
        ],
        
        // Limpieza de archivos temporales
        'clean_temp_files' => [
            'enabled' => true,
            'temp_retention_hours' => 24, // Borrar archivos temporales después de 24 horas
        ],
        
        // Compactación de logs
        'compact_logs' => [
            'enabled' => true,
            'frequency_days' => 7, // Compactar logs cada 7 días
        ],
        
        // Verificación de integridad
        'integrity_check' => [
            'enabled' => true,
            'frequency_days' => 1, // Verificar integridad diariamente
        ],
    ],
    
    // Configuración de horarios de ejecución
    'execution_schedule' => [
        'cleanup_hour' => 1, // Hora del día (1:00 AM)
        'backup_hour' => 2, // Hora del día (2:00 AM)
        'notifications_hour' => 10, // Hora del día (10:00 AM)
        'integrity_check_hour' => 0, // Hora del día (12:00 AM)
    ],
];

/**
 * No modificar nada debajo de esta línea
 */

// Función para verificar si el script se está ejecutando desde línea de comandos
function isCommandLine() {
    return (php_sapi_name() === 'cli');
}

// Si el archivo se accede directamente, mostrar información de configuración
if (!isCommandLine() && !defined('MAINTENANCE_INCLUDED')) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Configuración de Mantenimiento - Módulo de Reservas</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
            h1 { color: #2c3e50; }
            .config { background: #f8f9fa; padding: 15px; border-radius: 5px; }
            .enabled { color: #27ae60; }
            .disabled { color: #e74c3c; }
            .warning { color: #f39c12; background: #fef9e7; padding: 10px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <h1>Configuración de Mantenimiento - Módulo de Reservas</h1>
        <div class="warning">
            <strong>Advertencia:</strong> Este archivo contiene la configuración del sistema de mantenimiento.
            No debe ser accesible públicamente en un entorno de producción.
        </div>
        <h2>Estado General: <span class="' . ($config['enabled'] ? 'enabled">ACTIVADO' : 'disabled">DESACTIVADO') . '</span></h2>
        <div class="config">
            <h3>Respaldo de datos: <span class="' . ($config['backup']['enabled'] ? 'enabled">ACTIVADO' : 'disabled">DESACTIVADO') . '</span></h3>
            <p>Ubicación: ' . $config['backup']['path'] . '</p>
            <p>Retención: ' . $config['backup']['retention_days'] . ' días</p>
            
            <h3>Limpieza de registros: <span class="' . ($config['cleanup']['enabled'] ? 'enabled">ACTIVADO' : 'disabled">DESACTIVADO') . '</span></h3>
            <p>Retención de logs: ' . $config['cleanup']['logs_retention_days'] . ' días</p>
            <p>Retención de reservas completadas: ' . $config['cleanup']['completed_reservations_retention_days'] . ' días</p>
            <p>Retención de reservas canceladas: ' . $config['cleanup']['cancelled_reservations_retention_days'] . ' días</p>
            
            <h3>Notificaciones automáticas: <span class="' . ($config['notifications']['reminder_enabled'] ? 'enabled">ACTIVADO' : 'disabled">DESACTIVADO') . '</span></h3>
            <p>Recordatorios: ' . $config['notifications']['reminder_hours_before'] . ' horas antes</p>
            <p>WhatsApp: <span class="' . ($config['notifications']['whatsapp_enabled'] ? 'enabled">ACTIVADO' : 'disabled">DESACTIVADO') . '</span></p>
            <p>Email: <span class="' . ($config['notifications']['email_enabled'] ? 'enabled">ACTIVADO' : 'disabled">DESACTIVADO') . '</span></p>
        </div>
        
        <h2>Para ejecutar el mantenimiento manualmente:</h2>
        <code>php ' . __DIR__ . '/mantenimiento_reservas.php</code>
    </body>
    </html>';
    exit;
}

// Si se incluye desde otro archivo, simplemente retornar la configuración
return $config;
?>

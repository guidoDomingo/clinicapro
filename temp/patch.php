<?php
/**
 * Parche para agregar el campo sala_id en la inserción de reservas
 */

// Ruta al archivo original
$filePath = "c:/laragon/www/clinica/model/servicios.model.php";

// Leer el archivo completo
$content = file_get_contents($filePath);

// Hacer las modificaciones necesarias
// 1. Agregar sala_id en la consulta SQL
$oldSql = "INSERT INTO servicios_reservas (
                    servicio_id, doctor_id, paciente_id, fecha_reserva, 
                    hora_inicio, hora_fin, observaciones, reserva_estado, 
                    business_id, created_by, agenda_id, tarifa_id, seguro_id
                ) VALUES (
                    :servicio_id, :doctor_id, :paciente_id, :fecha_reserva, 
                    :hora_inicio, :hora_fin, :observaciones, :reserva_estado, 
                    :business_id, :created_by, :agenda_id, :tarifa_id, :seguro_id";

$newSql = "INSERT INTO servicios_reservas (
                    servicio_id, doctor_id, paciente_id, fecha_reserva, 
                    hora_inicio, hora_fin, observaciones, reserva_estado, 
                    business_id, created_by, agenda_id, tarifa_id, seguro_id, sala_id
                ) VALUES (
                    :servicio_id, :doctor_id, :paciente_id, :fecha_reserva, 
                    :hora_inicio, :hora_fin, :observaciones, :reserva_estado, 
                    :business_id, :created_by, :agenda_id, :tarifa_id, :seguro_id, :sala_id";

$content = str_replace($oldSql, $newSql, $content);

// 2. Agregar bindeo para sala_id antes de la ejecución de la consulta
$oldBindings = '$seguroId = isset($datos[\'seguro_id\']) ? $datos[\'seguro_id\'] : null;
            $stmt->bindParam(":seguro_id", $seguroId, $seguroId ? PDO::PARAM_INT : PDO::PARAM_NULL);';

$newBindings = '$seguroId = isset($datos[\'seguro_id\']) ? $datos[\'seguro_id\'] : null;
            $stmt->bindParam(":seguro_id", $seguroId, $seguroId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            
            // Bindear sala_id si está disponible
            $salaId = isset($datos[\'sala_id\']) ? $datos[\'sala_id\'] : null;
            $stmt->bindParam(":sala_id", $salaId, $salaId ? PDO::PARAM_INT : PDO::PARAM_NULL);
            error_log("mdlGuardarReserva: Usando sala_id=" . (isset($datos[\'sala_id\']) ? $datos[\'sala_id\'] : "null"), 
                      3, \'c:/laragon/www/clinica/logs/reservas.log\');';

$content = str_replace($oldBindings, $newBindings, $content);

// 3. Agregar sala_id en los parámetros del log
$oldLogParams = 'error_log("Parámetros: " . json_encode([
                \'servicio_id\' => $datos[\'servicio_id\'],
                \'doctor_id\' => $datos[\'doctor_id\'],
                \'paciente_id\' => $datos[\'paciente_id\'],
                \'fecha_reserva\' => $datos[\'fecha_reserva\'],
                \'hora_inicio\' => $datos[\'hora_inicio\'],
                \'hora_fin\' => $datos[\'hora_fin\'],
                \'agenda_id\' => $agendaId,
                \'tarifa_id\' => $tarifaId,
                \'seguro_id\' => $seguroId';

$newLogParams = 'error_log("Parámetros: " . json_encode([
                \'servicio_id\' => $datos[\'servicio_id\'],
                \'doctor_id\' => $datos[\'doctor_id\'],
                \'paciente_id\' => $datos[\'paciente_id\'],
                \'fecha_reserva\' => $datos[\'fecha_reserva\'],
                \'hora_inicio\' => $datos[\'hora_inicio\'],
                \'hora_fin\' => $datos[\'hora_fin\'],
                \'agenda_id\' => $agendaId,
                \'tarifa_id\' => $tarifaId,
                \'seguro_id\' => $seguroId,
                \'sala_id\' => $salaId';

$content = str_replace($oldLogParams, $newLogParams, $content);

// Guardar los cambios en el archivo original
file_put_contents($filePath, $content);

echo "Parche aplicado correctamente.";
?>

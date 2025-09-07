<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "controller/agendas.controller.php";
require_once "model/agendas.model.php";
require_once "model/conexion.php";

echo "<h3>Prueba de Actualización sin Duplicados</h3>";

try {
    // Verificar estado inicial
    echo "<h4>Estado inicial:</h4>";
    $stmt = Conexion::conectar()->prepare(
        "SELECT COUNT(*) as count FROM rs_servicios_doctors WHERE agenda_detalle_id = 40"
    );
    $stmt->execute();
    $inicial = $stmt->fetchColumn();
    echo "Registros iniciales para detalle_id 40: {$inicial}<br>";
    
    // Datos para actualizar el horario
    $datosActualizar = [
        "detalle_id" => 40, // Un detalle existente
        "agenda_id" => 20,   // Agenda del doctor 18
        "turno_id" => 1,     // Turno mañana
        "sala_id" => 1,      // Sala 1
        "servicio_id" => 2,  // Cambiar a servicio 2 (diferente al actual)
        "dia_semana" => "LUNES",
        "hora_inicio" => "08:00:00",
        "hora_fin" => "12:00:00",
        "intervalo_minutos" => 45,
        "cupo_maximo" => 1,
        "detalle_estado" => true
    ];
    
    echo "<h4>Simulando actualización de horario:</h4>";
    echo "Cambiando servicio_id a: {$datosActualizar['servicio_id']}<br>";
    
    // Actualizar usando nuestro controlador
    $resultado = ControllerAgendas::ctrGuardarDetalleAgenda($datosActualizar);
    echo "Resultado actualización: " . json_encode($resultado) . "<br>";
    
    if (!$resultado["error"]) {
        // Verificar estado después de la actualización
        echo "<h4>Estado después de actualización:</h4>";
        $stmt = Conexion::conectar()->prepare(
            "SELECT COUNT(*) as count FROM rs_servicios_doctors WHERE agenda_detalle_id = 40"
        );
        $stmt->execute();
        $final = $stmt->fetchColumn();
        echo "Registros finales para detalle_id 40: {$final}<br>";
        
        if ($final == 1) {
            echo "✅ Correcto: Solo hay 1 registro (sin duplicados)<br>";
        } else {
            echo "❌ Error: Hay {$final} registros (duplicados encontrados)<br>";
        }
        
        // Mostrar los registros específicos
        $stmt2 = Conexion::conectar()->prepare(
            "SELECT rsd.*, s.serv_descripcion 
             FROM rs_servicios_doctors rsd 
             INNER JOIN rs_servicios s ON rsd.servicio_id = s.serv_id
             WHERE rsd.agenda_detalle_id = 40"
        );
        $stmt2->execute();
        $registros = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h5>Registros encontrados:</h5>";
        foreach ($registros as $registro) {
            echo "- ID: {$registro['id']}, Servicio: {$registro['serv_descripcion']} (ID: {$registro['servicio_id']}), Doctor: {$registro['doctor_id']}<br>";
        }
        
        // Hacer otra actualización para verificar que no se duplica
        echo "<h4>Segunda actualización (cambiar de vuelta):</h4>";
        $datosActualizar2 = $datosActualizar;
        $datosActualizar2["servicio_id"] = 3; // Cambiar a servicio 3
        
        $resultado2 = ControllerAgendas::ctrGuardarDetalleAgenda($datosActualizar2);
        echo "Resultado segunda actualización: " . json_encode($resultado2) . "<br>";
        
        $stmt3 = Conexion::conectar()->prepare(
            "SELECT COUNT(*) as count FROM rs_servicios_doctors WHERE agenda_detalle_id = 40"
        );
        $stmt3->execute();
        $final2 = $stmt3->fetchColumn();
        echo "Registros después de segunda actualización: {$final2}<br>";
        
        if ($final2 == 1) {
            echo "✅ Perfecto: Aún solo hay 1 registro después de múltiples actualizaciones<br>";
        } else {
            echo "❌ Error: Hay {$final2} registros después de segunda actualización<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}
?>
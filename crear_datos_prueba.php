<?php
/**
 * Crear datos de prueba para testear la edición
 */

require_once "controller/agendas.controller.php";
require_once "model/agendas.model.php";

echo "<h2>Creando datos de prueba</h2>";

$modelo = new ModelAgendas();

// Primero verificar si hay médicos
$medicos = $modelo->mdlObtenerMedicos();
echo "<h3>Médicos disponibles:</h3>";
echo "<pre>";
print_r($medicos);
echo "</pre>";

if (empty($medicos)) {
    echo "<p>No hay médicos disponibles. No se pueden crear agendas.</p>";
    exit;
}

// Crear una agenda de prueba
$medicoId = $medicos[0]['doctor_id'];
echo "<h3>Creando agenda para médico ID: $medicoId</h3>";

// Crear agenda cabecera
$datosAgenda = [
    'agenda_id' => 0,
    'medico_id' => $medicoId,
    'fecha_inicio' => date('Y-m-d'),
    'fecha_fin' => date('Y-m-d', strtotime('+30 days')),
    'estado' => 1,
    'usuario' => 1 // Usuario admin
];

$agendaId = $modelo->mdlCrearAgenda($datosAgenda);
echo "<p>Agenda creada con ID: $agendaId</p>";

if ($agendaId) {
    // Crear algunos detalles de horarios
    $servicios = $modelo->mdlObtenerServicios();
    $servicio1 = $servicios[0]['serv_id'] ?? 2;
    $servicio2 = $servicios[1]['serv_id'] ?? 3;
    
    // Obtener turnos y salas
    $turnos = $modelo->mdlObtenerTurnos();
    $salas = $modelo->mdlObtenerSalas();
    
    $turnoId = $turnos[0]['turno_id'] ?? 1;
    $salaId = $salas[0]['sala_id'] ?? 1;
    
    echo "<h3>Creando detalles de horarios</h3>";
    echo "<p>Turno ID: $turnoId, Sala ID: $salaId</p>";
    echo "<p>Servicio 1: $servicio1, Servicio 2: $servicio2</p>";
    
    // Detalle 1: Lunes
    $detalle1 = [
        'detalle_id' => 0,
        'agenda_id' => $agendaId,
        'dia_semana' => 'LUNES',
        'turno_id' => $turnoId,
        'sala_id' => $salaId,
        'servicio_id' => $servicio1,
        'hora_inicio' => '08:00',
        'hora_fin' => '12:00',
        'intervalo_minutos' => 30,
        'cupo_maximo' => 2,
        'detalle_estado' => 1,
        'usuario' => 1
    ];
    
    $detalleId1 = $modelo->mdlCrearDetalleAgenda($detalle1);
    echo "<p>Detalle 1 creado (Lunes) con ID: $detalleId1</p>";
    
    // Detalle 2: Miércoles  
    $detalle2 = [
        'detalle_id' => 0,
        'agenda_id' => $agendaId,
        'dia_semana' => 'MIERCOLES',
        'turno_id' => $turnoId,
        'sala_id' => $salaId,
        'servicio_id' => $servicio2,
        'hora_inicio' => '14:00',
        'hora_fin' => '18:00',
        'intervalo_minutos' => 45,
        'cupo_maximo' => 1,
        'detalle_estado' => 1,
        'usuario' => 1
    ];
    
    $detalleId2 = $modelo->mdlCrearDetalleAgenda($detalle2);
    echo "<p>Detalle 2 creado (Miércoles) con ID: $detalleId2</p>";
}

echo "<h3>Probando carga de detalles:</h3>";
$detallesResultado = $modelo->mdlObtenerDetallesAgenda($agendaId);
echo "<pre>";
print_r($detallesResultado);
echo "</pre>";

echo "<p><strong>¡Datos de prueba creados exitosamente!</strong></p>";
echo "<p><a href='servicios' target='_blank'>Ir a probar la interfaz</a></p>";
?>
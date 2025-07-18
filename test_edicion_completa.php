<?php
/**
 * Test completo de la funcionalidad de edición de reservas
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

echo "<h2>Test Completo de Edición de Reservas</h2>";

$reservaId = 68;

echo "<h3>1. Obtener datos de la reserva para edición</h3>";

try {
    $reserva = ControladorServicios::ctrObtenerReservaPorId($reservaId);
    
    if ($reserva) {
        echo "<p style='color: green;'>✓ Reserva encontrada para edición</p>";
        echo "<div style='background: #f8f9fa; padding: 10px; border: 1px solid #ddd; margin: 10px 0;'>";
        echo "<strong>Datos actuales:</strong><br>";
        echo "ID: {$reserva['reserva_id']}<br>";
        echo "Paciente: {$reserva['paciente_nombre']}<br>";
        echo "Doctor: {$reserva['doctor_nombre']}<br>";
        echo "Servicio: {$reserva['servicio_nombre']}<br>";
        echo "Fecha: {$reserva['fecha_reserva']}<br>";
        echo "Hora: {$reserva['hora_inicio']} - {$reserva['hora_fin']}<br>";
        echo "Estado: {$reserva['reserva_estado']}<br>";
        echo "Sala: {$reserva['sala_nombre']}<br>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>✗ No se pudo obtener la reserva</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    exit;
}

echo "<h3>2. Test de datos necesarios para el modal</h3>";

// Test servicios
try {
    $servicios = ControladorServicios::ctrObtenerServicios();
    echo "<p style='color: green;'>✓ Servicios disponibles: " . count($servicios) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al cargar servicios: " . $e->getMessage() . "</p>";
}

// Test doctores (usaremos la función de médicos por fecha)
try {
    $doctores = ControladorServicios::ctrObtenerMedicosDisponiblesPorFecha($reserva['fecha_reserva']);
    echo "<p style='color: green;'>✓ Doctores disponibles: " . count($doctores) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error al cargar doctores: " . $e->getMessage() . "</p>";
}

echo "<h3>3. Simulación de edición (cambio de hora)</h3>";

// Simular cambio de hora de 11:00-11:45 a 14:00-14:45
$datosActualizacion = [
    'reserva_id' => $reservaId,
    'servicio_id' => $reserva['servicio_id'],
    'agenda_id' => $reserva['agenda_id'],
    'doctor_id' => $reserva['doctor_id'],
    'paciente_id' => $reserva['paciente_id'],
    'fecha_reserva' => $reserva['fecha_reserva'],
    'hora_inicio' => '14:00:00',  // Cambio de hora
    'hora_fin' => '14:45:00',     // Cambio de hora
    'sala_id' => $reserva['sala_id'],
    'reserva_estado' => $reserva['reserva_estado'],
    'observaciones' => $reserva['observaciones'] . ' - Editado en test'
];

echo "<div style='background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; margin: 10px 0;'>";
echo "<strong>Datos de prueba para actualización:</strong><br>";
echo "Hora actual: {$reserva['hora_inicio']} - {$reserva['hora_fin']}<br>";
echo "Nueva hora: {$datosActualizacion['hora_inicio']} - {$datosActualizacion['hora_fin']}<br>";
echo "</div>";

try {
    $resultado = ControladorServicios::ctrActualizarReserva($datosActualizacion);
    
    if ($resultado['status'] === 'success') {
        echo "<p style='color: green;'>✓ Actualización exitosa: {$resultado['message']}</p>";
        
        // Verificar el cambio
        $reservaActualizada = ControladorServicios::ctrObtenerReservaPorId($reservaId);
        if ($reservaActualizada) {
            echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; margin: 10px 0;'>";
            echo "<strong>Datos después de la actualización:</strong><br>";
            echo "Hora actualizada: {$reservaActualizada['hora_inicio']} - {$reservaActualizada['hora_fin']}<br>";
            echo "Observaciones: {$reservaActualizada['observaciones']}<br>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>✗ Error en actualización: {$resultado['message']}</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error en actualización: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Test de la interfaz AJAX</h3>";

echo "<div style='background: #e3f2fd; padding: 10px; border: 1px solid #bbdefb; margin: 10px 0;'>";
echo "<strong>Prueba desde JavaScript:</strong><br>";
echo "1. Ir a la página de servicios<br>";
echo "2. En la pestaña 'Reservas', buscar reservas del día<br>";
echo "3. Hacer clic en el botón 'Editar' (ícono de lápiz) de la reserva ID $reservaId<br>";
echo "4. Debe abrir el modal con los datos pre-cargados<br>";
echo "5. Modificar algún campo y guardar<br>";
echo "</div>";

echo "<hr>";
echo "<p>";
echo "<a href='servicios' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 10px;'>→ Probar en Servicios</a>";
echo "<a href='javascript:history.back()' style='display: inline-block; padding: 10px 20px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 5px;'>← Volver</a>";
echo "</p>";

// Script JavaScript para test automático
?>
<script>
console.log('=== TEST DE EDICIÓN DE RESERVAS ===');
console.log('Datos de la reserva para test:', <?php echo json_encode($reserva); ?>);
console.log('Datos de actualización:', <?php echo json_encode($datosActualizacion); ?>);

// Función para test AJAX
function testEditarReservaAJAX() {
    console.log('Iniciando test AJAX...');
    
    // Simular llamada para obtener reserva
    fetch('ajax/servicios.ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=obtenerReservaPorId&reserva_id=<?php echo $reservaId; ?>'
    })
    .then(response => response.json())
    .then(data => {
        console.log('Respuesta obtener reserva:', data);
        
        if (data.status === 'success') {
            console.log('✓ AJAX obtenerReservaPorId funciona correctamente');
        } else {
            console.error('✗ Error en AJAX obtenerReservaPorId:', data.message);
        }
    })
    .catch(error => {
        console.error('✗ Error de red en test AJAX:', error);
    });
}

// Ejecutar test automáticamente
setTimeout(testEditarReservaAJAX, 1000);
</script>
<?php echo "<p style='color: #007bff;'><strong>Nota:</strong> Abre las herramientas de desarrollador (F12) para ver los resultados del test AJAX automático.</p>"; ?>

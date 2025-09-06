<?php
/**
 * Script de prueba para los nuevos endpoints de servicios
 */

echo "<h2>🧪 Prueba de Nuevos Endpoints - Flujo Servicio Primero</h2>";

// Simular una petición POST para obtener todos los servicios
echo "<h3>1. Probando obtenerTodosLosServicios</h3>";
$_POST['action'] = 'obtenerTodosLosServicios';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Capturar la salida del archivo ajax
ob_start();
include 'ajax/servicios.ajax.php';
$responseServicios = ob_get_clean();

echo "<pre>";
echo "Respuesta JSON:\n";
echo $responseServicios;
echo "</pre>";

// Decodificar para obtener un servicio de prueba
$serviciosData = json_decode($responseServicios, true);
if ($serviciosData && $serviciosData['success'] && !empty($serviciosData['data'])) {
    $primerServicio = $serviciosData['data'][0];
    $servicioTestId = $primerServicio['serv_id'];
    
    echo "<h3>2. Probando obtenerMedicosPorServicio (Servicio ID: $servicioTestId)</h3>";
    
    // Limpiar variables POST
    unset($_POST);
    $_POST['action'] = 'obtenerMedicosPorServicio';
    $_POST['servicio_id'] = $servicioTestId;
    
    // Capturar la salida
    ob_start();
    include 'ajax/servicios.ajax.php';
    $responseMedicos = ob_get_clean();
    
    echo "<pre>";
    echo "Respuesta JSON:\n";
    echo $responseMedicos;
    echo "</pre>";
    
    // Mostrar interpretación de los datos
    $medicosData = json_decode($responseMedicos, true);
    if ($medicosData && $medicosData['success']) {
        echo "<h4>✅ Resultados:</h4>";
        echo "<ul>";
        echo "<li><strong>Servicios encontrados:</strong> " . count($serviciosData['data']) . "</li>";
        echo "<li><strong>Médicos para servicio '{$primerServicio['serv_descripcion']}':</strong> " . count($medicosData['data']) . "</li>";
        echo "</ul>";
        
        if (!empty($medicosData['data'])) {
            echo "<h4>👨‍⚕️ Médicos disponibles:</h4>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Estado</th><th>Disponibilidad</th></tr>";
            foreach ($medicosData['data'] as $medico) {
                echo "<tr>";
                echo "<td>{$medico['doctor_id']}</td>";
                echo "<td>{$medico['doctor_nombre']}</td>";
                echo "<td>{$medico['doctor_estado']}</td>";
                echo "<td>{$medico['disponibilidad_texto']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<h4>⚠️ No se encontraron médicos para este servicio</h4>";
    }
} else {
    echo "<h4>❌ Error: No se pudieron obtener servicios para la prueba</h4>";
}

echo "<hr>";
echo "<h3>📊 Resumen de la Implementación</h3>";
echo "<p><strong>✅ Tabla de relación creada:</strong> rs_servicios_doctors</p>";
echo "<p><strong>✅ Endpoints agregados:</strong> obtenerTodosLosServicios, obtenerMedicosPorServicio</p>";
echo "<p><strong>✅ Flujo implementado:</strong> Servicio → Médicos filtrados → Paciente → Reserva</p>";

// Mostrar algunos servicios de ejemplo
if (isset($serviciosData) && $serviciosData['success']) {
    echo "<h4>🔧 Servicios disponibles (primeros 5):</h4>";
    echo "<ol>";
    $count = 0;
    foreach ($serviciosData['data'] as $servicio) {
        if ($count >= 5) break;
        echo "<li>{$servicio['serv_descripcion']} (ID: {$servicio['serv_id']})</li>";
        $count++;
    }
    echo "</ol>";
}
?>
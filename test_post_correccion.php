<?php
/**
 * Test directo del método del modelo después de las correcciones
 */

require_once "model/conexion.php";
require_once "model/servicios.model.php";
require_once "controller/servicios.controller.php";

header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST DIRECTO POST-CORRECCIÓN ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Objetivo: Probar mdlObtenerDoctoresPorFecha('2025-07-17')\n\n";

try {
    // 1. Test del modelo
    echo "1. PROBANDO MODELO...\n";
    echo "------------------------\n";
    $doctoresModelo = ModelServicios::mdlObtenerDoctoresPorFecha('2025-07-17');
    
    echo "Resultado del modelo: " . count($doctoresModelo) . " doctores\n";
    
    if (!empty($doctoresModelo)) {
        echo "\n🎉 ¡ÉXITO! Doctores encontrados:\n";
        foreach ($doctoresModelo as $i => $doctor) {
            echo "\n--- Doctor #" . ($i + 1) . " ---\n";
            echo "ID: {$doctor['doctor_id']}\n";
            echo "Nombre: {$doctor['nombre_doctor']}\n";
            echo "Person ID: {$doctor['person_id']}\n";
            echo "Especialidad: " . ($doctor['especialidad'] ?? 'No especificada') . "\n";
            echo "Horario: {$doctor['hora_inicio']} - {$doctor['hora_fin']}\n";
            echo "Intervalo: {$doctor['intervalo_minutos']} minutos\n";
            echo "Detalle ID: {$doctor['detalle_id']}\n";
            echo "Agenda ID: {$doctor['agenda_id']}\n";
        }
    } else {
        echo "\n❌ El modelo no devolvió ningún doctor\n";
    }
    
    echo "\n\n2. PROBANDO CONTROLADOR...\n";
    echo "-----------------------------\n";
    $doctoresControlador = ControladorServicios::ctrObtenerDoctoresPorFecha('2025-07-17');
    
    echo "Resultado del controlador: " . count($doctoresControlador) . " doctores\n";
    
    if (!empty($doctoresControlador)) {
        echo "\n✅ Controlador también funciona correctamente\n";
        foreach ($doctoresControlador as $i => $doctor) {
            echo "  Dr. {$doctor['nombre_doctor']} - {$doctor['hora_inicio']} a {$doctor['hora_fin']}\n";
        }
    } else {
        echo "\n❌ El controlador no devolvió ningún doctor\n";
    }
    
    echo "\n\n3. SIMULANDO AJAX...\n";
    echo "---------------------\n";
    
    // Simular variables POST
    $_POST = [
        'action' => 'obtenerDoctoresPorFecha',
        'fecha' => '2025-07-17'
    ];
    
    // Capturar salida del AJAX
    ob_start();
    include 'ajax/servicios.ajax.php';
    $ajaxOutput = ob_get_clean();
    
    echo "Respuesta AJAX cruda:\n";
    echo $ajaxOutput . "\n";
    
    // Intentar decodificar JSON
    $ajaxData = json_decode($ajaxOutput, true);
    if ($ajaxData) {
        echo "\nRespuesta AJAX decodificada:\n";
        echo "Status: " . ($ajaxData['status'] ?? 'No definido') . "\n";
        echo "Total: " . ($ajaxData['total'] ?? 'No definido') . "\n";
        
        if (isset($ajaxData['data']) && is_array($ajaxData['data'])) {
            echo "Doctores: " . count($ajaxData['data']) . "\n";
            foreach ($ajaxData['data'] as $doctor) {
                echo "  - {$doctor['nombre_doctor']} (ID: {$doctor['doctor_id']})\n";
            }
        }
    } else {
        echo "\n❌ No se pudo decodificar la respuesta AJAX como JSON\n";
    }
    
    echo "\n\n=== RESUMEN FINAL ===\n";
    $modeloTotal = count($doctoresModelo);
    $controladorTotal = count($doctoresControlador);
    
    echo "Modelo: $modeloTotal doctores\n";
    echo "Controlador: $controladorTotal doctores\n";
    echo "AJAX: " . (isset($ajaxData['total']) ? $ajaxData['total'] : 'Error') . " doctores\n\n";
    
    if ($modeloTotal > 0 && $controladorTotal > 0) {
        echo "🎉 ¡ÉXITO COMPLETO! La funcionalidad está funcionando.\n";
        echo "Ahora el modal de edición debería cargar los doctores correctamente.\n";
    } else {
        echo "❌ Aún hay problemas. Revisar logs para más detalles.\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>

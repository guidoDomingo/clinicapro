<?php
/**
 * Test final: Verificación paso a paso con datos reales
 */

// Headers
header('Content-Type: text/plain; charset=utf-8');

echo "=== TEST FINAL: DOCTORES POR FECHA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "Objetivo: Verificar que se obtengan los doctores para JUEVES (2025-07-17)\n\n";

try {
    // Incluir archivos
    require_once "model/conexion.php";
    require_once "model/servicios.model.php";
    require_once "controller/servicios.controller.php";
    
    // 1. Verificar conexión
    echo "1. VERIFICANDO CONEXIÓN...\n";
    $conexion = Conexion::conectar();
    if ($conexion) {
        echo "✅ Conexión exitosa\n\n";
    } else {
        throw new Exception("❌ Error de conexión");
    }
    
    // 2. Consulta directa con tus datos exactos
    echo "2. CONSULTA DIRECTA (TUS DATOS)...\n";
    $stmt = $conexion->prepare("
        SELECT 
            rp.person_id,
            rp.first_name,
            rp.last_name,
            rd.doctor_id,
            ad.detalle_id,
            ad.agenda_id,
            ad.dia_semana,
            ad.hora_inicio,
            ad.hora_fin,
            ad.intervalo_minutos
        FROM agendas_detalle ad 
        INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
        INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
        INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
        WHERE ad.dia_semana = 'JUEVES'
    ");
    
    $stmt->execute();
    $datosReales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registros encontrados: " . count($datosReales) . "\n";
    foreach ($datosReales as $i => $datos) {
        echo "  [" . ($i + 1) . "] Dr. {$datos['first_name']} {$datos['last_name']} (doctor_id: {$datos['doctor_id']})\n";
        echo "      Horario: {$datos['hora_inicio']} - {$datos['hora_fin']}\n";
        echo "      Intervalo: {$datos['intervalo_minutos']} min\n";
    }
    echo "\n";
    
    // 3. Probar el método del modelo
    echo "3. PROBANDO MODELO...\n";
    $doctoresModelo = ModelServicios::mdlObtenerDoctoresPorFecha('2025-07-17');
    
    echo "Doctores del modelo: " . count($doctoresModelo) . "\n";
    foreach ($doctoresModelo as $i => $doctor) {
        echo "  [" . ($i + 1) . "] {$doctor['nombre_doctor']} (doctor_id: {$doctor['doctor_id']})\n";
        echo "      Horario: {$doctor['hora_inicio']} - {$doctor['hora_fin']}\n";
        echo "      Person ID: {$doctor['person_id']}\n";
    }
    echo "\n";
    
    // 4. Probar el controlador
    echo "4. PROBANDO CONTROLADOR...\n";
    $doctoresControlador = ControladorServicios::ctrObtenerDoctoresPorFecha('2025-07-17');
    
    echo "Doctores del controlador: " . count($doctoresControlador) . "\n";
    foreach ($doctoresControlador as $i => $doctor) {
        echo "  [" . ($i + 1) . "] {$doctor['nombre_doctor']} (doctor_id: {$doctor['doctor_id']})\n";
        echo "      Horario: {$doctor['hora_inicio']} - {$doctor['hora_fin']}\n";
    }
    echo "\n";
    
    // 5. Simular llamada AJAX
    echo "5. SIMULANDO LLAMADA AJAX...\n";
    $_POST['action'] = 'obtenerDoctoresPorFecha';
    $_POST['fecha'] = '2025-07-17';
    
    // Capturar la salida del script AJAX
    ob_start();
    include 'ajax/servicios.ajax.php';
    $ajaxOutput = ob_get_clean();
    
    echo "Respuesta AJAX:\n";
    echo $ajaxOutput . "\n\n";
    
    // 6. Decodificar respuesta AJAX
    $respuestaAjax = json_decode($ajaxOutput, true);
    if ($respuestaAjax) {
        echo "6. ANÁLISIS DE RESPUESTA AJAX...\n";
        echo "Status: " . ($respuestaAjax['status'] ?? 'No definido') . "\n";
        echo "Total: " . ($respuestaAjax['total'] ?? 'No definido') . "\n";
        
        if (isset($respuestaAjax['data']) && is_array($respuestaAjax['data'])) {
            echo "Doctores en respuesta: " . count($respuestaAjax['data']) . "\n";
            foreach ($respuestaAjax['data'] as $i => $doctor) {
                echo "  [" . ($i + 1) . "] {$doctor['nombre_doctor']} (ID: {$doctor['doctor_id']})\n";
            }
        }
    } else {
        echo "6. ERROR: No se pudo decodificar la respuesta AJAX\n";
    }
    
    echo "\n=== RESULTADO FINAL ===\n";
    if (count($doctoresModelo) > 0 && count($doctoresControlador) > 0) {
        echo "✅ ÉXITO: La funcionalidad está funcionando correctamente\n";
        echo "   - Modelo devuelve: " . count($doctoresModelo) . " doctores\n";
        echo "   - Controlador devuelve: " . count($doctoresControlador) . " doctores\n";
        echo "   - Datos esperados: Dr. ronal con 2 horarios para JUEVES\n";
    } else {
        echo "❌ PROBLEMA: La funcionalidad no está devolviendo resultados\n";
        echo "   - Modelo: " . count($doctoresModelo) . " doctores\n";
        echo "   - Controlador: " . count($doctoresControlador) . " doctores\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR CRÍTICO: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}

echo "\n=== FIN DEL TEST ===\n";
?>

<?php
/**
 * Test directo de la API AJAX para verificar qué está pasando
 */

// Simular exactamente la llamada AJAX del modal
$_POST['action'] = 'obtenerDoctoresPorFecha';
$_POST['fecha'] = '2025-07-17';

// Configurar headers
header('Content-Type: application/json');

echo "=== SIMULACIÓN EXACTA DE LA LLAMADA AJAX ===\n";
echo "POST data:\n";
print_r($_POST);
echo "\n";

// Incluir el archivo AJAX
try {
    ob_start();
    include 'ajax/servicios.ajax.php';
    $response = ob_get_clean();
    
    echo "Respuesta del archivo AJAX:\n";
    echo $response . "\n\n";
    
    // Decodificar y analizar
    $data = json_decode($response, true);
    if ($data) {
        echo "Datos decodificados:\n";
        print_r($data);
        
        if ($data['status'] === 'success' && empty($data['data'])) {
            echo "\n❌ PROBLEMA: API devuelve success pero data está vacío\n";
            echo "Vamos a probar directamente el modelo...\n\n";
            
            require_once "model/conexion.php";
            require_once "model/servicios.model.php";
            
            // Test directo del modelo
            $doctores = ModelServicios::mdlObtenerDoctoresPorFecha('2025-07-17');
            echo "Resultado directo del modelo:\n";
            print_r($doctores);
            echo "Total doctores del modelo: " . count($doctores) . "\n";
            
            if (!empty($doctores)) {
                echo "\n✅ El modelo SÍ devuelve datos!\n";
                echo "El problema está en el controlador o en el AJAX.\n";
                
                // Test del controlador
                require_once "controller/servicios.controller.php";
                $doctoresControlador = ControladorServicios::ctrObtenerDoctoresPorFecha('2025-07-17');
                echo "\nResultado del controlador:\n";
                print_r($doctoresControlador);
                echo "Total doctores del controlador: " . count($doctoresControlador) . "\n";
                
            } else {
                echo "\n❌ El modelo tampoco devuelve datos.\n";
                echo "Vamos a probar la consulta directa...\n";
                
                $conexion = Conexion::conectar();
                $stmt = $conexion->prepare("
                    SELECT 
                        rp.person_id,
                        rp.first_name,
                        rd.doctor_id,
                        ad.*
                    FROM agendas_detalle ad 
                    INNER JOIN agendas_cabecera ac ON ad.agenda_id = ac.agenda_id 
                    INNER JOIN rh_doctors rd ON rd.doctor_id = ac.medico_id 
                    INNER JOIN rh_person rp ON rd.person_id = rp.person_id 
                    WHERE ad.dia_semana = 'JUEVES'
                ");
                $stmt->execute();
                $consultaDirecta = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "\nConsulta directa:\n";
                print_r($consultaDirecta);
                echo "Total de consulta directa: " . count($consultaDirecta) . "\n";
            }
        }
    } else {
        echo "\n❌ No se pudo decodificar la respuesta JSON\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
?>
